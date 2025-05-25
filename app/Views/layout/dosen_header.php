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
    <li class="breadcrumb-item"><a href="#">Pages</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= esc($breadcrumb ?? 'Dashboard') ?></li>
  </ol>
</nav>

<div class="page-heading d-flex justify-content-between align-items-center mb-4">
  <h3><?= esc($pageTitle ?? 'Main Dashboard') ?></h3>
  <div class="user-profile">
    <span class="user-name">prof. Dr. Paul Morrison S.Pd. M.pd</span>
    <img src="https://i.pravatar.cc/40?img=68" alt="User Avatar" class="user-avatar">
  </div>
</div>
