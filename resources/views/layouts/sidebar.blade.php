<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
    <div class="sidenav-header" style="text-align:center; padding:2px 0; min-height:unset;">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-xl-none"
         aria-hidden="true" id="iconSidenav"
         style="cursor:pointer; font-size:1.1rem; opacity:0.7 !important;"></i>
      <a class="navbar-brand m-0" href="/dashboard">
        <img src="{{ $logo }}" 
             class="navbar-brand-img" 
             style="width:80px; height:80px; object-fit:contain; max-height:none !important;"
             alt="main_logo"
             onerror="this.onerror=null; this.src='/assets/img/logo.jpeg';">
      </a>
    </div>
    <hr class="horizontal dark mt-0">

    <div class="sidenav-scroll-wrapper">
        @include('layouts.menu', ['parent_menu' => Session::get('menu')])
    </div>

</aside>
