<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/pages/auth.css">

    
</head>

<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="logo">LOGO</div>
            <div class="login-role">Log in as Teacher</div>
        </div>

    <?= $this->include('layout/auth/header'); ?>
        <!-- Content -->
        <div class="login-content">
            <div class="login-box">
                <h1>LOGIN</h1>
                <form action="#">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter your email address">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Enter your password">
                    </div>
                    <button type="submit" class="btn btn-login">Log in</button>
                </form>
                <div class="login-footer mt-4">
                    <p>Tidak punya akun? <a href="#">mendaftar</a></p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
