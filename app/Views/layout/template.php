
<!-- template untuk dashboard dosen -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'Dashboard' ?></title>
  <link rel="stylesheet" href="/assets/css/bootstrap.css">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/vendors/bootstrap-icons/bootstrap-icons.css">
</head>
<body>
  <div id="app">
    <?= $this->include($sidebar) ?>
    <div id="main">
      <?= $this->renderSection('content') ?>
    </div>
  </div>
  <script src="/assets/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/main.js"></script>
</body>
</html>
