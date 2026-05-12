<ul class="navbar-nav">
    @foreach($parent_menu as $item)
        @php
            $isActive = request()->is(ltrim($item->link, '/'));
            $hasActiveChild = false;
            foreach ($item->child as $child) {
                if (request()->is(ltrim($child->link, '/'))) { $hasActiveChild = true; break; }
                foreach ($child->child as $subchild) {
                    if (request()->is(ltrim($subchild->link, '/'))) { $hasActiveChild = true; break 2; }
                }
            }

            // Override "Pelayanan Kredit" to "Pelayanan Kredit Individu"
            $title = $item->title;
            if ($title === 'Pelayanan Kredit') {
                $title = 'Pelayanan Kredit Individu';
            }

            // Map generic icons to beautiful, specific FontAwesome icons
            $icon = $item->icon;
            if (empty($icon) || $icon === 'ni ni-bullet-list-67') {
                if ($item->title === 'Dashboard') {
                    $icon = 'fas fa-chart-line';
                } elseif ($item->title === 'Pengaturan') {
                    $icon = 'fas fa-sliders-h';
                } elseif ($item->title === 'Basis Data') {
                    $icon = 'fas fa-database';
                } elseif ($item->title === 'Pelayanan Kredit') {
                    $icon = 'fas fa-user-tie';
                } elseif ($item->title === 'Pelayanan Kredit Kelompok') {
                    $icon = 'fas fa-users';
                } elseif ($item->title === 'Transaksi') {
                    $icon = 'fas fa-exchange-alt';
                } elseif ($item->title === 'Laporan') {
                    $icon = 'fas fa-file-invoice-dollar';
                } else {
                    $icon = 'ni ni-bullet-list-67';
                }
            }
        @endphp

        @if($item->child->isEmpty())
            {{-- Menu tanpa submenu --}}
            <li class="nav-item">
                <a class="nav-link {{ $isActive ? 'active' : '' }}"
                   href="{{ $item->link !== '#' && !str_contains($item->link, '#') ? url($item->link) : 'javascript:;' }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="{{ $icon }} text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">{{ $title }}</span>
                </a>
            </li>
        @else
            {{-- Menu dengan submenu --}}
            <li class="nav-item">
                <a class="nav-link {{ $hasActiveChild ? 'active' : '' }} menu-toggle"
                   href="javascript:;"
                   data-target="submenu-{{ $item->id }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="{{ $icon }} text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">{{ $title }}</span>
                    <i class="fas fa-chevron-down menu-arrow"></i>
                </a>

                <div class="sidenav-submenu {{ $hasActiveChild ? 'open' : '' }}" id="submenu-{{ $item->id }}">
                    <ul class="nav ms-4 ps-3">
                        @foreach($item->child as $child)
                            @php
                                $childActive = request()->is(ltrim($child->link, '/'));
                                $hasActiveSubChild = false;
                                foreach ($child->child as $subchild) {
                                    if (request()->is(ltrim($subchild->link, '/'))) { $hasActiveSubChild = true; break; }
                                }
                            @endphp

                            @if($child->child->isEmpty())
                                <li class="nav-item">
                                    <a class="nav-link {{ $childActive ? 'active' : '' }}"
                                       href="{{ $child->link !== '#' && !str_contains($child->link, '#') ? url($child->link) : 'javascript:;' }}">
                                        <span class="sidenav-mini-icon">•</span>
                                        <span class="sidenav-normal">{{ $child->title }}</span>
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link {{ $hasActiveSubChild ? 'active' : '' }} menu-toggle"
                                       href="javascript:;"
                                       data-target="submenu-{{ $child->id }}">
                                        <span class="sidenav-mini-icon">•</span>
                                        <span class="sidenav-normal">{{ $child->title }}</span>
                                        <i class="fas fa-chevron-down menu-arrow"></i>
                                    </a>

                                    <div class="sidenav-submenu {{ $hasActiveSubChild ? 'open' : '' }}" id="submenu-{{ $child->id }}">
                                        <ul class="nav ms-4 ps-3">
                                            @foreach($child->child as $subchild)
                                                <li class="nav-item">
                                                    <a class="nav-link {{ request()->is(ltrim($subchild->link, '/')) ? 'active' : '' }}"
                                                       href="{{ $subchild->link !== '#' && !str_contains($subchild->link, '#') ? url($subchild->link) : 'javascript:;' }}">
                                                        <span class="sidenav-mini-icon">-</span>
                                                        <span class="sidenav-normal">{{ $subchild->title }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </li>
        @endif
    @endforeach
</ul>

<style>
    /* Lebarkan sidebar sedikit pada Desktop agar teks panjang muat sempurna */
    @media (min-width: 1200px) {
        #sidenav-main {
            width: 270px !important;
            max-width: 270px !important;
        }
        /* Geser main-content agar tidak menabrak sidebar */
        .g-sidenav-show .main-content {
            margin-left: 290px !important;
        }
    }

    /* Submenu tersembunyi default, tampil saat .open */
    .sidenav-submenu {
        display: none;
    }
    .sidenav-submenu.open {
        display: block;
    }

    /* Hapus panah bawaan Argon yang bikin dobel */
    #sidenav-main .nav-link.menu-toggle::after,
    #sidenav-main .nav-link[data-bs-toggle]::after {
        display: none !important;
    }

    /* Jadikan nav-link menu-toggle sebagai flexbox agar posisi icon buka-tutup sejajar di pojok kanan */
    #sidenav-main .nav-link.menu-toggle {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        padding-right: 1.5rem !important;
    }

    /* Panah custom - Sejajar sempurna di Pojok Kanan */
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

    /* Scrollbar tipis sidebar */
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
    // Set initial open state
    document.querySelectorAll('.menu-toggle').forEach(function (toggle) {
        var targetId = toggle.getAttribute('data-target');
        var submenu = document.getElementById(targetId);
        if (submenu && submenu.classList.contains('open')) {
            toggle.classList.add('open');
        }
    });

    // Toggle click handler
    document.querySelectorAll('.menu-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var targetId = this.getAttribute('data-target');
            var submenu = document.getElementById(targetId);
            if (!submenu) return;

            var isOpen = submenu.classList.contains('open');

            // Tutup semua sibling di level yang sama
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

            // Toggle yang diklik
            submenu.classList.toggle('open', !isOpen);
            this.classList.toggle('open', !isOpen);
        });
    });
});
</script>
