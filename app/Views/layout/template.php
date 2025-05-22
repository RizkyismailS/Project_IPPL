
<!-- template untuk dashboard dosen -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'Dashboard' ?></title>
  <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap.css">

    <link rel="shortcut icon" href="/assets/images/favicon.svg" type="image/x-icon">
  <link rel="stylesheet" href="/assets/css/bootstrap.css">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="/assets/vendors/apexcharts/apexcharts.css">
</head>
<body>
  <div id="app">
    <?= $this->include($sidebar) ?>
    <div id="main">
      <?= $this->renderSection('content') ?>
    </div>
  </div>
  <script src="/assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
  <script src="/assets/js/bootstrap.bundle.min.js"></script>

  <script src="/assets/vendors/dayjs/dayjs.min.js"></script>
  <script src="/assets/vendors/apexcharts/apexcharts.js"></script>
  <script src="/assets/js/pages/ui-apexchart.js"></script>

  <script src="assets/js/main.js"></script>
  <script src="/assets/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/pages/dashboard.js"></script>
  <?= $this->renderSection('scripts') ?>

</body>
</html>
