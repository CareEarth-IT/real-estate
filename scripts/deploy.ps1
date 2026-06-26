# Cloud Run + Cloud SQL deploy script for real-estate (PowerShell)
# Prerequisites: Google Cloud SDK (gcloud), logged in, billing enabled
#
# Usage:
#   .\scripts\deploy.ps1 -ProjectId "your-gcp-project" -DbPassword "strong-password"
#
param(
    [Parameter(Mandatory = $true)]
    [string]$ProjectId,

    [string]$Region = "asia-northeast1",
    [string]$ServiceName = "real-estate",
    [string]$Repository = "real-estate",
    [string]$SqlInstance = "real-estate-db",
    [string]$DbName = "laravel",
    [string]$DbUser = "laravel",

    [Parameter(Mandatory = $true)]
    [string]$DbPassword,

    [string]$AppKey = "",
    [string]$GoogleClientId = "",
    [string]$GoogleClientSecret = "",
    [switch]$SkipSqlCreate,
    [switch]$SkipBuild,
    [switch]$UseLocalDocker
)

$ErrorActionPreference = "Continue"

function Invoke-Gcloud {
    param(
        [Parameter(Mandatory = $true)][string[]]$Args,
        [switch]$AllowFailure
    )

    & gcloud @Args
    $code = $LASTEXITCODE

    if (-not $AllowFailure -and $code -ne 0) {
        throw "gcloud failed ($code): gcloud $($Args -join ' ')"
    }

    return $code
}

function Assert-Gcloud {
    if (Get-Command gcloud -ErrorAction SilentlyContinue) {
        return
    }

    $candidates = @(
        "$env:ProgramFiles\Google\Cloud SDK\google-cloud-sdk\bin\gcloud.cmd",
        "${env:ProgramFiles(x86)}\Google\Cloud SDK\google-cloud-sdk\bin\gcloud.cmd",
        "$env:LOCALAPPDATA\Google\Cloud SDK\google-cloud-sdk\bin\gcloud.cmd"
    )

    foreach ($candidate in $candidates) {
        if (Test-Path $candidate) {
            $bin = Split-Path $candidate -Parent
            $env:Path = "$bin;$env:Path"
            return
        }
    }

    Write-Error @"
gcloud CLI not found.
Install Google Cloud SDK: https://cloud.google.com/sdk/docs/install
Then run: gcloud auth login && gcloud config set project $ProjectId
"@
}

function Add-SecretVersion {
    param([string]$Name, [string]$Value)

    $tempFile = New-TemporaryFile
    try {
        [System.IO.File]::WriteAllText($tempFile, $Value)
        Invoke-Gcloud -Args @("secrets", "versions", "add", $Name, "--project", $ProjectId, "--data-file=$tempFile") | Out-Null
    } finally {
        Remove-Item $tempFile -Force -ErrorAction SilentlyContinue
    }
}

function Ensure-Secret {
    param([string]$Name, [string]$Value)

    if ([string]::IsNullOrWhiteSpace($Value)) {
        Write-Warning "Secret '$Name' is empty; skipped."
        return
    }

    Invoke-Gcloud -Args @("secrets", "create", $Name, "--project", $ProjectId, "--replication-policy=automatic") -AllowFailure | Out-Null
    Write-Host "Updating secret: $Name"
    Add-SecretVersion -Name $Name -Value $Value
}

function Grant-SecretAccess {
    param([string]$SecretName, [string]$ServiceAccount)

    gcloud secrets add-iam-policy-binding $SecretName `
        --project $ProjectId `
        --member "serviceAccount:$ServiceAccount" `
        --role roles/secretmanager.secretAccessor `
        --quiet | Out-Null
}

Assert-Gcloud

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

Write-Host "==> Project: $ProjectId / Region: $Region"

gcloud config set project $ProjectId | Out-Null

Write-Host "==> Enabling APIs..."
gcloud services enable `
    run.googleapis.com `
    cloudbuild.googleapis.com `
    artifactregistry.googleapis.com `
    sqladmin.googleapis.com `
    secretmanager.googleapis.com `
    --project $ProjectId

if (-not $SkipSqlCreate) {
    if ((Invoke-Gcloud -Args @("sql", "instances", "describe", $SqlInstance, "--project", $ProjectId) -AllowFailure) -ne 0) {
        Write-Host "==> Creating Cloud SQL instance '$SqlInstance' (about 5-10 minutes)..."
        Invoke-Gcloud -Args @(
            "sql", "instances", "create", $SqlInstance,
            "--project", $ProjectId,
            "--database-version=MYSQL_8_0",
            "--tier=db-f1-micro",
            "--region", $Region,
            "--storage-auto-increase",
            "--quiet"
        ) | Out-Null
    } else {
        Write-Host "==> Cloud SQL instance '$SqlInstance' already exists"
    }

    Invoke-Gcloud -Args @("sql", "databases", "create", $DbName, "--instance", $SqlInstance, "--project", $ProjectId) -AllowFailure | Out-Null
    Invoke-Gcloud -Args @("sql", "users", "create", $DbUser, "--instance", $SqlInstance, "--password", $DbPassword, "--project", $ProjectId) -AllowFailure | Out-Null
}

$SqlConnection = "${ProjectId}:${Region}:${SqlInstance}"
$Image = "${Region}-docker.pkg.dev/${ProjectId}/${Repository}/${ServiceName}:latest"

Write-Host "==> Artifact Registry..."
Invoke-Gcloud -Args @(
    "artifacts", "repositories", "create", $Repository,
    "--repository-format=docker",
    "--location", $Region,
    "--project", $ProjectId,
    "--quiet"
) -AllowFailure | Out-Null

if ([string]::IsNullOrWhiteSpace($AppKey)) {
    $bytes = New-Object byte[] 32
    [System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
    $AppKey = "base64:$([Convert]::ToBase64String($bytes))"
    Write-Host "==> Generated APP_KEY"
}

if ([string]::IsNullOrWhiteSpace($GoogleClientId)) {
    $GoogleClientId = "unset"
}
if ([string]::IsNullOrWhiteSpace($GoogleClientSecret)) {
    $GoogleClientSecret = "unset"
}

Ensure-Secret -Name "real-estate-app-key" -Value $AppKey
Ensure-Secret -Name "real-estate-db-password" -Value $DbPassword
Ensure-Secret -Name "real-estate-google-client-id" -Value $GoogleClientId
Ensure-Secret -Name "real-estate-google-client-secret" -Value $GoogleClientSecret

$ProjectNumber = gcloud projects describe $ProjectId --format="value(projectNumber)"

if (-not $SkipBuild) {
    $BuildSa = "${ProjectNumber}@cloudbuild.gserviceaccount.com"
    $ComputeSa = "${ProjectNumber}-compute@developer.gserviceaccount.com"

    if ($UseLocalDocker) {
        Write-Host "==> Building image locally with Docker..."
        gcloud auth configure-docker "${Region}-docker.pkg.dev" --quiet | Out-Null
        docker build -t $Image .
        if ($LASTEXITCODE -ne 0) { throw "docker build failed. Is Docker Desktop running?" }
        Write-Host "==> Pushing image to Artifact Registry..."
        docker push $Image
        if ($LASTEXITCODE -ne 0) { throw "docker push failed" }
    } else {
        Write-Host "==> Granting Cloud Build permissions (requires Project IAM Admin)..."
        foreach ($binding in @(
            @{ Member = "serviceAccount:$BuildSa"; Role = "roles/storage.admin" },
            @{ Member = "serviceAccount:$BuildSa"; Role = "roles/artifactregistry.writer" },
            @{ Member = "serviceAccount:$ComputeSa"; Role = "roles/storage.admin" }
        )) {
            gcloud projects add-iam-policy-binding $ProjectId `
                --member $binding.Member `
                --role $binding.Role `
                --quiet 2>$null | Out-Null
        }

        Write-Host "==> Building and pushing image (Cloud Build)..."
        $buildResult = Invoke-Gcloud -Args @("builds", "submit", "--project", $ProjectId, "--tag", $Image, ".") -AllowFailure
        if ($buildResult -ne 0) {
            Write-Warning @"
Cloud Build failed (often IAM on default service accounts).
Ask a GCP Project IAM Admin to run:
  gcloud projects add-iam-policy-binding $ProjectId --member=serviceAccount:$ComputeSa --role=roles/storage.admin
  gcloud projects add-iam-policy-binding $ProjectId --member=serviceAccount:$BuildSa --role=roles/storage.admin

Or start Docker Desktop and rerun with -UseLocalDocker
"@
            throw "Cloud Build failed"
        }
    }
}

$RunSa = "${ProjectNumber}-compute@developer.gserviceaccount.com"

Write-Host "==> Granting Cloud SQL Client role to Cloud Run service account..."
gcloud projects add-iam-policy-binding $ProjectId `
    --member "serviceAccount:$RunSa" `
    --role roles/cloudsql.client `
    --quiet | Out-Null

foreach ($secret in @(
    "real-estate-app-key",
    "real-estate-db-password",
    "real-estate-google-client-id",
    "real-estate-google-client-secret"
)) {
    Grant-SecretAccess -SecretName $secret -ServiceAccount $RunSa
}

# First deploy may use placeholder APP_URL; update after deploy with real URL
$AppUrl = gcloud run services describe $ServiceName --region $Region --project $ProjectId --format="value(status.url)" 2>$null
if ([string]::IsNullOrWhiteSpace($AppUrl)) {
    $AppUrl = "https://placeholder.run.app"
}

$EnvVars = @(
    "APP_ENV=production",
    "APP_DEBUG=false",
    "APP_URL=$AppUrl",
    "DB_CONNECTION=mysql",
    "DB_SOCKET=/cloudsql/$SqlConnection",
    "DB_DATABASE=$DbName",
    "DB_USERNAME=$DbUser",
    "LOG_CHANNEL=stderr",
    "SESSION_DRIVER=database",
    "SESSION_SECURE_COOKIE=true",
    "CACHE_STORE=database",
    "QUEUE_CONNECTION=database",
    "ADMIN_GOOGLE_HOSTED_DOMAIN=careearth.info",
    "GOOGLE_REDIRECT_URI=$AppUrl/admin/auth/google/callback"
) -join ","

$Secrets = "APP_KEY=real-estate-app-key:latest,DB_PASSWORD=real-estate-db-password:latest,GOOGLE_CLIENT_ID=real-estate-google-client-id:latest,GOOGLE_CLIENT_SECRET=real-estate-google-client-secret:latest"

Write-Host "==> Deploying to Cloud Run..."
Invoke-Gcloud -Args @(
    "run", "deploy", $ServiceName,
    "--project", $ProjectId,
    "--image", $Image,
    "--region", $Region,
    "--platform", "managed",
    "--allow-unauthenticated",
    "--port", "8080",
    "--add-cloudsql-instances", $SqlConnection,
    "--set-env-vars", $EnvVars,
    "--set-secrets", $Secrets,
    "--quiet"
) | Out-Null

$ServiceUrl = gcloud run services describe $ServiceName --region $Region --project $ProjectId --format="value(status.url)"

if ($AppUrl -ne $ServiceUrl) {
    Write-Host "==> Updating APP_URL to $ServiceUrl"
    $EnvVars = $EnvVars.Replace($AppUrl, $ServiceUrl)
    Invoke-Gcloud -Args @(
        "run", "services", "update", $ServiceName,
        "--project", $ProjectId,
        "--region", $Region,
        "--set-env-vars", $EnvVars,
        "--quiet"
    ) | Out-Null
}

Write-Host ""
Write-Host "========================================"
Write-Host "Deploy complete!"
Write-Host "URL: $ServiceUrl"
Write-Host "Health: $ServiceUrl/up"
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Register Google OAuth redirect URI:"
Write-Host "   $ServiceUrl/admin/auth/google/callback"
Write-Host "2. Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in Secret Manager if needed"
Write-Host "3. Redeploy: .\scripts\deploy.ps1 -ProjectId $ProjectId -DbPassword '***' -SkipSqlCreate -SkipBuild"
Write-Host "========================================"
