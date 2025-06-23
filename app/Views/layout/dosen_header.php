<?php
?>
<style>
.user-profile {
  display: flex;
  align-items: center;
  background-color: #fff;
  padding: 8px 16px;
  border-radius: 9999px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  gap: 10px;
}
.user-profile .user-name {
  font-weight: 600;
  color: #333;
  font-size: 0.875rem;
}
.user-profile .user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}
</style>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= esc($breadcrumb ?? 'Dashboard') ?></li>
  </ol>
</nav>

<div class="page-heading d-flex justify-content-between align-items-center mb-4">
  <h3><?= esc($pageTitle ?? 'Dashboard Dosen') ?></h3>
  <div class="user-profile">
    <span class="user-name"><?= esc($nama_user ?? 'Dosen') ?></span>
    <?php if(isset($foto_profil) && !empty($foto_profil)): ?>
      <img src="<?= base_url('uploads/profil/' . $foto_profil) ?>" alt="User Avatar" class="user-avatar">
    <?php else: ?>
      <img src="<?= base_url('assets/images/default-avatar.png') ?>" alt="Default Avatar" class="user-avatar">
    <?php endif; ?>
  </div>
</div>