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
        
        return request()->is($urlPath) || request()->is($urlPath . '/*');
    };

    $clonedMenu = [];
    foreach ($parent_menu as $origParent) {
        $newPItem = clone $origParent;
        
        if ($newPItem->title === 'Basis Data') {
            $clusters = ['Nasabah', 'Kelompok'];
            
            $isAlreadyGrouped = false;
            foreach($newPItem->child as $childCheck) {
                if ($childCheck->link === '#' && in_array($childCheck->title, $clusters)) {
                    $isAlreadyGrouped = true; 
                    break;
                }
            }

            if (!$isAlreadyGrouped) {
                $clusteredMap = [];
                $unmatchedItems = [];
                foreach ($clusters as $w) { $clusteredMap[$w] = []; }

                foreach ($newPItem->child as $cItem) {
                    $found = false;
                    if ($cItem->link !== '#' && $cItem->link !== '#database#') {
                        foreach ($clusters as $w) {
                            if (stripos($cItem->title, $w) !== false) {
                                $clusteredMap[$w][] = clone $cItem;
                                $found = true; break;
                            }
                        }
                    }
                    if (!$found) { $unmatchedItems[] = clone $cItem; }
                }

                $syntheticList = [];
                $synthBaseId = 99880;
                foreach ($clusters as $idx => $w) {
                    if (!empty($clusteredMap[$w])) {
                        $sNode = new \stdClass;
                        $sNode->id = $synthBaseId + $idx;
                        $sNode->title = $w;
                        $sNode->link = '#';
                        $sNode->icon = 'ni ni-bullet-list-67';
                        $sNode->child = collect($clusteredMap[$w]);
                        $syntheticList[] = $sNode;
                    }
                }
                $newPItem->child = collect(array_merge($syntheticList, $unmatchedItems));
            }
        }
        $clonedMenu[] = $newPItem;
    }
    $parent_menu = collect($clonedMenu);
@endphp

<ul class="navbar-nav">
    @foreach($parent_menu as $item)
        @php
            $isActive = $checkActive($item->link);
            $hasActiveChild = false;
            foreach ($item->child as $child) {
                if ($checkActive($child->link)) { $hasActiveChild = true; break; }
                foreach ($child->child as $subchild) {
                    if ($checkActive($subchild->link)) { $hasActiveChild = true; break 2; }
                }
            }

            $title = $item->title;
            if ($title === 'Pelayanan Kredit') {
                $title = 'Pelayanan Kredit Individu';
            }

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
                                $childActive = $checkActive($child->link);
                                $hasActiveSubChild = false;
                                foreach ($child->child as $subchild) {
                                    if ($checkActive($subchild->link)) { $hasActiveSubChild = true; break; }
                                }
                            @endphp

                            @if($child->child->isEmpty())
                                <li class="nav-item">
                                    <a class="nav-link {{ $childActive ? 'active' : '' }}"
                                       href="{{ $child->link !== '#' && !str_contains($child->link, '#') ? url($child->link) : 'javascript:;' }}">
                                        <i class="far fa-circle text-secondary opacity-5 me-2" style="font-size: 6px;"></i>
                                        <span class="sidenav-normal">{{ $child->title }}</span>
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link {{ $hasActiveSubChild ? 'active' : '' }} menu-toggle"
                                       href="javascript:;"
                                       data-target="submenu-{{ $child->id }}">
                                        <i class="far fa-circle text-secondary opacity-5 me-2" style="font-size: 6px;"></i>
                                        <span class="sidenav-normal">{{ $child->title }}</span>
                                        <i class="fas fa-chevron-down menu-arrow"></i>
                                    </a>

                                    <div class="sidenav-submenu {{ $hasActiveSubChild ? 'open' : '' }}" id="submenu-{{ $child->id }}">
                                        <ul class="nav ms-4 ps-3">
                                            @foreach($child->child as $subchild)
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $checkActive($subchild->link) ? 'active' : '' }}"
                                                       href="{{ $subchild->link !== '#' && !str_contains($subchild->link, '#') ? url($subchild->link) : 'javascript:;' }}">
                                                        <i class="fas fa-circle text-secondary opacity-5 me-2" style="font-size: 6px;"></i>
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
