<?php

require_once __DIR__ . '/../config/app.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to fetch user data from user_accounts
    $sql = "SELECT * FROM user_accounts WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if password matches using password_verify()
        if (password_verify($password, $user['hash_password'])) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: " . BASE_URL . "/dashboard/admin_dashboard.php");
        } else {
            header("Location: " . BASE_URL . "/dashboard/user_dashboard.php");
        }
        exit;
    }
        } else {
            // Invalid email or password
            echo "<script>alert('Invalid email or password.');</script>";
        }

        $stmt->close();
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>TransGo - Login</title>

    <!-- Custom fonts for this template-->
    <link href="<?= BASE_URL ?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= BASE_URL ?>/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .logo-container img {
            max-width: 100%;
            max-height: 300px;
        }
    </style>

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <!-- Left: Logo -->
                            <div class="col-lg-6 d-none d-lg-flex logo-container">
                                <img src="logo.png" />
                            </div>

                            <!-- Right: Login Form -->
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form class="user" method="POST" action="login.php">
    <div class="form-group">
        <input type="email" name="email" class="form-control form-control-user" placeholder="Enter Email Address..." required>
    </div>
    <div class="form-group">
        <input type="password" name="password" class="form-control form-control-user" placeholder="Password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
</form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.html">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Right -->
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?= BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= BASE_URL ?>/js/sb-admin-2.min.js"></script>

</body>

</html>
