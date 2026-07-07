@php
    use App\Http\Middleware\CareEarthAuth;

    $variant = $variant ?? 'app';
    $menuId = 'portal-menu-' . $variant;
    $isAdmin = CareEarthAuth::isAdmin(request());
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
        @if ($isAdmin)
        <div class="portal-menu-group" role="presentation">
            <p class="portal-menu-group-label">管理者メニュー</p>
            <a
                href="{{ route('properties.index') }}"
                role="menuitem"
                @class(['portal-menu-item', 'active' => request()->routeIs('properties.index', 'properties.show', 'properties.edit')])
            >物件マスターデータ一覧</a>
            <a
                href="{{ route('users.index') }}"
                role="menuitem"
                @class(['portal-menu-item', 'active' => request()->routeIs('users.*')])
            >ユーザー管理</a>
        </div>
        @endif
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
