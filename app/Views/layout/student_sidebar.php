
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="/mahasiswa/dashboard"><h3>Mahasiswa</h3></a>
                </div>
                <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input me-0" type="checkbox" id="toggle-dark">
                        <label class="form-check-label"></label>
                    </div>
                </div>
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>
                
                <li class="sidebar-item">
                    <a href="/mahasiswa/dashboard" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Beranda</span>
                    </a>
                </li>

                <li class="sidebar-item <?= (strpos(uri_string(), 'mahasiswa/kelas') !== false) ? 'active' : '' ?>">
                    <a href="/mahasiswa/kelas" class='sidebar-link'>
                        <i class="bi bi-journal-bookmark-fill"></i>
                        <span>Kelas Saya</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="/mahasiswa/enroll" class="sidebar-link">
                        <i class="bi bi-person-fill"></i>
                        <span>Enroll Kelas</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="/mahasiswa/logs" class="sidebar-link">
                        <i class="bi bi-book-fill"></i>
                        <span>Aktivitas anda</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="/mahasiswa/profile" class="sidebar-link">
                        <i class="bi bi-pencil-fill"></i>
                        <span>Profil Anda</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="/logout" class="sidebar-link">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>