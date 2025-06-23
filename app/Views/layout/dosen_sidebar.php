<?php
?>
<style>
.menu {
  list-style: none;
  padding: 0;
}

.sidebar-link {
  display: flex;
  align-items: center;
  padding: 0.7rem 1rem;
  text-decoration: none;
  color: #25396f;
  transition: all 0.3s;
}

.sidebar-link:hover {
  background-color: #f0f1f5;
}

.sidebar-link i {
  margin-right: 0.7rem;
}
</style>

<div id="sidebar" class="active">
  <div class="sidebar-wrapper active">
    <div class="sidebar-header">
      <div class="logo">
        <h4>IPPL System</h4>
      </div>
    </div>
    <div class="sidebar-menu">
      <ul class="menu">
        <li><a href="<?= base_url('dosen/dashboard') ?>" class="sidebar-link"><i class="bi bi-grid-fill"></i> <span>Beranda</span></a></li>
        <li><a href="<?= base_url('dosen/kelas') ?>" class="sidebar-link"><i class="bi bi-journal-plus"></i> <span>Kelola Kelas</span></a></li>
        <li><a href="<?= base_url('dosen/kelas/create') ?>" class="sidebar-link"><i class="bi bi-plus-square"></i> <span>Buat Kelas Baru</span></a></li>
        <li><a href="<?= base_url('dosen/profile') ?>" class="sidebar-link"><i class="bi bi-person-fill"></i> <span>Profil</span></a></li>
        <li><a href="<?= base_url('dosen/logs') ?>" class="sidebar-link"><i class="fas fa-history"></i> <span>Riwayat Aktivitas</span></a></li>
        <li><a href="<?= base_url('logout') ?>" class="sidebar-link"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
      </ul>
    </div>
  </div>
</div>