<?php
?>
<style>
    .sidebar-item.has-sub .submenu {
        display: none;
        padding-left: 15px;
    }

    .sidebar-item.has-sub.active .submenu {
        display: block;
    }

    .submenu-toggle:after {
        content: "\f107";
        /* Font Awesome chevron-down icon */
        font-family: "Bootstrap-icons";
        float: right;
        transition: transform 0.3s;
    }

    .sidebar-item.has-sub.active .submenu-toggle:after {
        transform: rotate(180deg);
    }
</style>
<script src="<?= base_url('assets/js/bootstrap.js') ?>"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="<?= base_url('admin/dashboard') ?>">
                        <h3>Admin Panel</h3>
                    </a>
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

                <li class="sidebar-item <?= (uri_string() == 'admin/dashboard') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item has-sub <?= (strpos(uri_string(), 'admin/dosen') !== false) ? 'active' : '' ?>">
                    <a href="javascript:void(0)" class="sidebar-link submenu-toggle">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Manajemen Dosen</span>
                    </a>
                    <ul class="submenu <?= (strpos(uri_string(), 'admin/dosen') !== false) ? 'active' : '' ?>">
                        <li class="submenu-item <?= (uri_string() == 'admin/dosen/list') ? 'active' : '' ?>">
                            <a href="<?= base_url('admin/dosen/list') ?>">Daftar Dosen</a>
                        </li>
                        <li class="submenu-item <?= (uri_string() == 'admin/dosen/create') ? 'active' : '' ?>">
                            <a href="<?= base_url('admin/dosen/create') ?>">Tambah Dosen</a>
                        </li>
                    </ul>
                </li>


                <li
                    class="sidebar-item has-sub <?= (strpos(uri_string(), 'admin/mahasiswa') !== false) ? 'active' : '' ?>">
                    <a href="javascript:void(0)" class="sidebar-link submenu-toggle">
                        <i class="bi bi-people-fill"></i>
                        <span>Manajemen Mhs</span>
                    </a>
                    <ul class="submenu <?= (strpos(uri_string(), 'admin/mahasiswa') !== false) ? 'active' : '' ?>">
                        <li class="submenu-item <?= (uri_string() == 'admin/mahasiswa/list') ? 'active' : '' ?>">
                            <a href="<?= base_url('admin/mahasiswa/list') ?>">Daftar Mahasiswa</a>
                        </li>
                        <li class="submenu-item <?= (uri_string() == 'admin/mahasiswa/create') ? 'active' : '' ?>">
                            <a href="<?= base_url('admin/mahasiswa/create') ?>">Tambah Mahasiswa</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item <?= (uri_string() == 'admin/sesi') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/sesi') ?>" class='sidebar-link'>
                        <i class="bi bi-calendar-check-fill"></i>
                        <span>Manajemen Sesi</span>
                    </a>
                </li>

                <li class="sidebar-item <?= (uri_string() == 'admin/matakuliah') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/matakuliah') ?>" class='sidebar-link'>
                        <i class="bi bi-journal-text"></i>
                        <span>Manajemen Mata Kuliah</span>
                    </a>
                </li>

                <li class="sidebar-item <?= (uri_string() == 'admin/logs') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/logs') ?>" class="sidebar-link">
                        <i class="bi bi-graph-up"></i>
                        <span>Log Aktivitas</span>
                    </a>
                </li>

                <li class="sidebar-title">User</li>

                

                <li class="sidebar-item">
                    <a href="<?= base_url('logout') ?>" class="sidebar-link">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get all submenu toggle elements
        var submenuToggles = document.querySelectorAll('.submenu-toggle');

        // Add click event to each toggle
        submenuToggles.forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();

                // Get the parent li element
                var parentLi = this.parentElement;

                // Toggle active class on parent li
                parentLi.classList.toggle('active');

                // Find the submenu inside this parent
                var submenu = parentLi.querySelector('.submenu');

                // Toggle display of submenu
                if (submenu) {
                    if (submenu.style.display === 'none' || submenu.style.display === '') {
                        submenu.style.display = 'block';
                    } else {
                        submenu.style.display = 'none';
                    }
                }
            });
        });

        // Automatically expand active submenus on page load
        var activeSubmenus = document.querySelectorAll('.sidebar-item.has-sub.active > .submenu');
        activeSubmenus.forEach(function (submenu) {
            submenu.style.display = 'block';
        });
    });
</script>