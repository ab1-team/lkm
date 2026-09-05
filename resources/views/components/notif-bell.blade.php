<li class="nav-item px-2 d-flex align-items-center">
    <a href="javascript:;" id="notif-bell-btn"
       class="nav-link text-white p-0 position-relative"
       data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell cursor-pointer"></i>
        <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle d-none"
              id="notif-badge">0</span>
    </a>

    <div class="dropdown-menu dropdown-menu-end p-0 me-sm-n4 notif-dropdown"
         aria-labelledby="notif-bell-btn">
        <div class="notif-header d-flex justify-content-between align-items-center px-3 py-2">
            <h6 class="m-0 fw-bold text-white">Notifikasi</h6>
            <a href="javascript:;" id="btn-tandai-dibaca" class="text-white small text-decoration-none">
                <i class="fa fa-check-double me-1"></i>Tandai sudah dibaca
            </a>
        </div>
        <div id="notif-list" class="notif-list text-muted small text-center">Memuat...</div>
        <div class="notif-footer px-3 py-2">
            <a href="/notifikasi/timeline" class="btn btn-primary btn-sm w-100">
                Periksa semua update fitur
            </a>
        </div>
    </div>
</li>
