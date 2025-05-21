
<!-- template untuk dashboard admin dan mahasiswa -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/pages/dashboard.css">
    
</head>

<body>
    
    <div class="layout-wrapper">

        <!-- Header -->
        <div class="header">
            <div><strong>LOGO</strong></div>
            <div><a href="#" style="color: white;">Logout</a></div>
        </div>

        <!-- Main Body -->
        <div class="main-wrapper">
            <div class="sidebar">
                <?= $this->include($sidebar) ?>
            </div>
            <div class="content">
                <?= $this->renderSection('content') ?>
            </div>
        </div>

    </div>
</body>
</html>
