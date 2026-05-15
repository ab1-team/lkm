@php

    $allMenuUrls = [];
    $collectUrls = function($items) use (&$allMenuUrls, &$collectUrls) {
        foreach ($items as $itm) {
            $u = ltrim($itm->link, '/');
            if (!empty($u) && $itm->link !== '#' && $itm->link !== 'javascript:;' && !str_contains($itm->link, '#')) {
                $allMenuUrls[] = $u;
            }
            if (isset($itm->child) && !empty($itm->child)) {
                $collectUrls($itm->child);
            }
        }
    };
    $collectUrls($parent_menu);

    $hasExactMatchInMenu = false;
    foreach ($allMenuUrls as $mUrl) {
        if (request()->is($mUrl)) {
            $hasExactMatchInMenu = true;
            break;
        }
    }

    $checkActive = function($link) use ($hasExactMatchInMenu) {
        $urlPath = ltrim($link, '/');
        if (empty($urlPath) || $link === '#' || $link === 'javascript:;' || str_contains($link, '#')) {
            return false;
        }
        

        if ($hasExactMatchInMenu) {
            return request()->is($urlPath);
        }
        

    @media (min-width: 1200px) {
        #sidenav-main {
            width: 270px !important;
            max-width: 270px !important;
        }
        
        .g-sidenav-show .main-content {
            margin-left: 290px !important;
        }
    }

    
    
    #sidenav-main.bg-white .nav-link.active {
        background-color: #cbd5e1 !important; 
        box-shadow: none !important;
    }

    
    #sidenav-main.bg-white .nav-link.active .nav-link-text,
    #sidenav-main.bg-white .nav-link.active i {
        color: #1e293b !important; 
        font-weight: 700 !important;
    }

    
    #sidenav-main:not(.bg-white) .nav-link.active,
    #sidenav-main.bg-default .nav-link.active,
    #sidenav-main.bg-dark .nav-link.active {
        background-color: rgba(255, 255, 255, 0.35) !important; 
        box-shadow: none !important;
    }

    
    #sidenav-main:not(.bg-white) .nav-link.active .nav-link-text,
    #sidenav-main:not(.bg-white) .nav-link.active i,
    #sidenav-main.bg-default .nav-link.active .nav-link-text,
    #sidenav-main.bg-default .nav-link.active i,
    #sidenav-main.bg-dark .nav-link.active .nav-link-text,
    #sidenav-main.bg-dark .nav-link.active i {
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    
    
    #sidenav-main .nav-link.active.menu-toggle {
        background-color: transparent !important;
        box-shadow: none !important;
    }

    
    .sidenav-submenu {
        display: none;
    }
    .sidenav-submenu.open {
        display: block;
    }

    
    #sidenav-main .nav-link.menu-toggle::after,
    #sidenav-main .nav-link[data-bs-toggle]::after {
        display: none !important;
    }

    
    #sidenav-main .nav-link.menu-toggle {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        padding-right: 1.5rem !important;
    }

    
    .menu-toggle .menu-arrow {
        margin-left: auto !important;
        font-size: 0.75rem !important;
        color: #7b809a;
        transition: transform 0.3s ease;
        display: inline-block !important;
    }
    .menu-toggle.open .menu-arrow {
        transform: rotate(180deg);
    }

    
    .sidenav-scroll-wrapper {
        height: calc(100vh - 100px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    .sidenav-scroll-wrapper::-webkit-scrollbar { width: 4px; }
    .sidenav-scroll-wrapper::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.15);
        border-radius: 4px;
    }
    .sidenav-scroll-wrapper::-webkit-scrollbar-track { background: transparent; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.menu-toggle').forEach(function (toggle) {
        var targetId = toggle.getAttribute('data-target');
        var submenu = document.getElementById(targetId);
        if (submenu && submenu.classList.contains('open')) {
            toggle.classList.add('open');
        }
    });

    document.querySelectorAll('.menu-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var targetId = this.getAttribute('data-target');
            var submenu = document.getElementById(targetId);
            if (!submenu) return;

            var isOpen = submenu.classList.contains('open');

            var parentUl = this.closest('ul');
            if (parentUl) {
                parentUl.querySelectorAll(':scope > li > .menu-toggle').forEach(function (sibling) {
                    var sibId = sibling.getAttribute('data-target');
                    var sibMenu = document.getElementById(sibId);
                    if (sibMenu && sibMenu !== submenu) {
                        sibMenu.classList.remove('open');
                        sibling.classList.remove('open');
                    }
                });
            }

            submenu.classList.toggle('open', !isOpen);
            this.classList.toggle('open', !isOpen);
        });
    });
});
</script>
