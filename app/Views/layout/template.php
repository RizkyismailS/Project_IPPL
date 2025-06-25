
<!-- template untuk dashboard dosen -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Dashboard' ?></title>
  <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap.css">

    <link rel="shortcut icon" href="/assets/images/favicon.svg" type="image/x-icon">  <link rel="stylesheet" href="/assets/css/bootstrap.css">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="/assets/css/custom-responsive.css">
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <!-- <link rel="stylesheet" href="/assets/vendors/apexcharts/apexcharts.css"> -->
  <link rel="stylesheet" href="/assets/vendors/fontawesome/all.min.css">
</head>

<?php 
$session = session();
$role = $session->get('role') ?? 'guest';

// Menentukan sidebar berdasarkan role dengan mapping
$sidebarMap = [
    'admin' => 'admin_sidebar',
    'dosen' => 'dosen_sidebar',
    'mahasiswa' => 'student_sidebar',
    'guest' => 'guest_sidebar' // Fallback sidebar untuk tamu
];

// Ambil sidebar yang sesuai atau gunakan fallback
$currentSidebar = $sidebarMap[$role] ?? $sidebarMap['guest'];

// Set page title based on role
$roleTitles = [
    'admin' => 'Admin Panel',
    'dosen' => 'Dosen Dashboard',
    'mahasiswa' => 'Mahasiswa Dashboard',
    'guest' => 'Login'
];
$roleTitle = $roleTitles[$role] ?? 'Dashboard';
?>
<body>
  <div id="app">
    <!-- Mobile Navigation Header -->
    <div class="mobile-nav d-md-none">
      <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
          <button class="navbar-toggler" type="button" id="mobile-toggle">
            <span class="navbar-toggler-icon"></span>
          </button>
          <span class="navbar-brand mb-0 h1"><?= $roleTitle ?></span>
          <div class="dropdown">
            <button class="btn btn-primary" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
              <li><a class="dropdown-item" href="<?= base_url($role . '/profile') ?>">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= base_url('logout') ?>">Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
    
    <?= view("layout/{$currentSidebar}"); ?>
    <div id="main">
      <?= $this->renderSection('content') ?>
    </div>
  </div>  <script src="/assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
  <script src="/assets/js/bootstrap.bundle.min.js"></script>

  <script src="/assets/vendors/dayjs/dayjs.min.js"></script>
  <!-- <script src="/assets/vendors/apexcharts/apexcharts.js"></script> -->
  <!-- <script src="/assets/js/pages/ui-apexchart.js"></script> -->
  <script src="/assets/vendors/fontawesome/all.min.js"></script>

  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/pages/dashboard.js"></script>
   <!-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script> -->
     <!-- Responsive sidebar toggle script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Mobile navbar hamburger toggle
      const mobileToggle = document.getElementById('mobile-toggle');
      if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
          const sidebar = document.querySelector('#sidebar');
          if (sidebar) {
            sidebar.classList.toggle('active');
            
            // Toggle aria-expanded attribute for accessibility
            const expanded = sidebar.classList.contains('active');
            mobileToggle.setAttribute('aria-expanded', expanded);
            
            // Add overlay when sidebar is open
            if (expanded) {
              const overlay = document.createElement('div');
              overlay.id = 'sidebar-overlay';
              overlay.className = 'sidebar-overlay';
              overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', false);
                document.body.removeChild(overlay);
              });
              document.body.appendChild(overlay);
            } else {
              const overlay = document.getElementById('sidebar-overlay');
              if (overlay) {
                document.body.removeChild(overlay);
              }
            }
          }
        });
      }

      // Original sidebar-toggler (x button)
      const toggleSidebar = document.querySelector('.sidebar-toggler');
      if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function(e) {
          e.preventDefault();
          const sidebar = document.querySelector('#sidebar');
          if (sidebar) {
            sidebar.classList.remove('active');
            
            // Remove overlay when sidebar is closed
            const overlay = document.getElementById('sidebar-overlay');
            if (overlay) {
              document.body.removeChild(overlay);
            }
          }
        });
      }

      // Submenu toggles
      const submenuToggles = document.querySelectorAll('.submenu-toggle');
      submenuToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
          e.preventDefault();
          const parentLi = this.closest('.sidebar-item.has-sub');
          if (parentLi) {
            parentLi.classList.toggle('active');
            const submenu = parentLi.querySelector('.submenu');
            if (submenu) {
              if (submenu.style.display === 'none' || submenu.style.display === '') {
                submenu.style.display = 'block';
              } else {
                submenu.style.display = 'none';
              }
            }
          }
        });
      });
      
      // Auto-expand active submenus
      document.querySelectorAll('.sidebar-item.has-sub.active > .submenu').forEach(function(submenu) {
        submenu.style.display = 'block';
      });
      
      // Handle responsive tables
      const tables = document.querySelectorAll('table');
      tables.forEach(table => {
        if (!table.parentElement.classList.contains('table-responsive')) {
          const wrapper = document.createElement('div');
          wrapper.className = 'table-responsive';
          table.parentNode.insertBefore(wrapper, table);
          wrapper.appendChild(table);
        }
      });
    });
  </script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    if (calendarEl) {
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: <?= isset($events) ? json_encode($events) : '[]'; ?>
      });
      calendar.render();
    }
  });
</script>

  <?= $this->renderSection('scripts') ?>

</body>
</html>
