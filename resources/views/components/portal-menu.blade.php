@php
    use App\Http\Middleware\CareEarthAuth;

    $variant = $variant ?? 'app';
    $menuId = 'portal-menu-' . $variant;
    $canManageUsers = $canManageUsers ?? CareEarthAuth::canManageUsers(request());
@endphp

<div class="portal-menu portal-menu--{{ $variant }}" data-portal-menu>
    <button
        type="button"
        class="portal-menu-toggle"
        aria-expanded="false"
        aria-haspopup="true"
        aria-controls="{{ $menuId }}-panel"
        aria-label="メニューを開く"
        data-portal-menu-toggle
    >
        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <rect x="3" y="4.5" width="14" height="2" rx="1"/>
            <rect x="3" y="9" width="14" height="2" rx="1"/>
            <rect x="3" y="13.5" width="14" height="2" rx="1"/>
        </svg>
    </button>

    <div
        id="{{ $menuId }}-panel"
        class="portal-menu-dropdown"
        role="menu"
        hidden
        data-portal-menu-panel
    >
        <div class="portal-menu-group" role="presentation">
            <p class="portal-menu-group-label">メニュー</p>
            <a
                href="{{ route('home') }}"
                role="menuitem"
                @class(['portal-menu-item', 'active' => request()->routeIs('home')])
            >ホーム</a>
            <a
                href="{{ route('admin.applications.index') }}"
                role="menuitem"
                @class(['portal-menu-item', 'active' => request()->routeIs('admin.applications.*', 'admin.flow-managements.*', 'admin.settlement-managements.*')])
            >賃貸管理一覧</a>
            <a
                href="{{ route('admin.rental-property-archives.index') }}"
                role="menuitem"
                @class(['portal-menu-item', 'active' => request()->routeIs('admin.rental-property-archives.*')])
            >賃貸物件保管</a>
        </div>

        <div class="portal-menu-group" role="presentation">
            <p class="portal-menu-group-label">アカウント</p>
            @if (session('email'))
                <p class="portal-menu-item" style="cursor: default; opacity: 0.75;">{{ session('name') ?: session('email') }}</p>
            @endif
            @if ($canManageUsers)
            <a
                href="{{ route('users.index') }}"
                role="menuitem"
                @class(['portal-menu-item', 'active' => request()->routeIs('users.*')])
            >ユーザー管理</a>
            @endif
            <form method="post" action="{{ route('logout') }}" role="none">
                @csrf
                <button type="submit" class="portal-menu-item" role="menuitem" style="width: 100%; text-align: left; border: 0; background: transparent; cursor: pointer;">
                    ログアウト
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.__portalMenuInit) {
            return;
        }
        window.__portalMenuInit = true;

        document.addEventListener('click', (event) => {
            document.querySelectorAll('[data-portal-menu]').forEach((menu) => {
                const toggle = menu.querySelector('[data-portal-menu-toggle]');
                const panel = menu.querySelector('[data-portal-menu-panel]');
                if (!toggle || !panel) {
                    return;
                }

                const isToggle = toggle.contains(event.target);
                const isInside = menu.contains(event.target);

                if (isToggle) {
                    const open = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
                    panel.hidden = open;
                    return;
                }

                if (!isInside) {
                    toggle.setAttribute('aria-expanded', 'false');
                    panel.hidden = true;
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('[data-portal-menu-toggle]').forEach((toggle) => {
                toggle.setAttribute('aria-expanded', 'false');
                const panelId = toggle.getAttribute('aria-controls');
                const panel = panelId ? document.getElementById(panelId) : null;
                if (panel) {
                    panel.hidden = true;
                }
            });
        });
    })();
</script>
