<?php
session_start();

// Kalau sudah login, langsung arahkan ke index
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = $_POST['username'] ?? '';
    $passwordInput = $_POST['password'] ?? '';

    $userFile = __DIR__ . '/Users/users.json';
    if (file_exists($userFile)) {
        $users = json_decode(file_get_contents($userFile), true);

        foreach ($users as $user) {
            if ($user['username'] === $usernameInput && password_verify($passwordInput, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            }
        }
    }

    $error = "Username atau password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | PERMADI</title>
    <link rel="icon" href="/title.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .login-box {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<main class="flex-fill">
    <div class="login-box">
        <h4 class="text-center mb-4 fw-bold">Login PERMADI</h4>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Login</button>
            <a href="/Users/request_reset.php">Lupa Password ?</a>
        </form>
    </div>
</main>

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
?>
