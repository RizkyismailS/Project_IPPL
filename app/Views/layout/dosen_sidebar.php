<style>

.menu {
  list-style: none;
  padding: 0;
}

</style>
<div id="sidebar" class="active">
  <div class="sidebar-wrapper active">
    <div class="sidebar-header">
      <div class="logo">LOGO</div>
    </div>
    <div class="sidebar-menu">
        <ul class="menu">
      <li><a href="/dosen/dashboard" class="sidebar-link"><i class="bi bi-grid-fill"></i> <span>Beranda</span></a></li>
      <li><a href="/dosen/kelas" class="sidebar-link"><i class="bi bi-person-fill"></i> <span>Buat kelas Baru</span></a></li>
      <li><a href="/dosen/listAbsensi" class="sidebar-link"><i class="bi bi-book-fill"></i> <span>Buat sesi Absensi</span></a></li>
      <li><a href="/dosen/profile" class="sidebar-link"><i class="bi bi-pencil-fill"></i> <span>profile</span></a></li>
      <li><a href="<?= base_url('dosen/logs')?>" class="sidebar-link" ><i class="fas fa-history"></i><span>My Activity History</span></a></li>
      <li><a href="/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
  </div>
</div>
