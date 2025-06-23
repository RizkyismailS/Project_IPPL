
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="/admin/dashboard"><h3>Admin Panel</h3></a>
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
                    <a href="/admin/dashboard" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item <?= (uri_string() == 'admin/dosen/list') ? 'active' : '' ?>">
                    <a href="/admin/dosen/list" class="sidebar-link">
                        <i class="bi bi-person-fill"></i>
                        <span>Manage Dosen</span>
                    </a>
                </li>

                <li class="sidebar-item <?= (uri_string() == 'admin/mahasiswa/list') ? 'active' : '' ?>">
                    <a href="/admin/mahasiswa/list" class="sidebar-link">
                        <i class="bi bi-book-fill"></i>
                        <span>Manage Mahasiswa</span>
                    </a>
                </li>

                

                <li class="sidebar-item <?= (uri_string() == 'admin/sesi') ? 'active' : '' ?>">
                    <a href="/admin/sesi" class='sidebar-link'>
                        <i class="bi bi-card-checklist"></i>
                        <span>Manajemen Sesi</span>
                    </a>
                </li>

                <li class="sidebar-item <?= (uri_string() == 'admin/matakuliah') ? 'active' : '' ?>">
                    <a href="/admin/matakuliah" class='sidebar-link'>
                        <i class="bi bi-card-checklist"></i>
                        <span>Manajemen Mata kuliah</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="logs" class="sidebar-link">
                        <i class="bi bi-graph-up"></i>
                        <span>Monitoring</span>
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