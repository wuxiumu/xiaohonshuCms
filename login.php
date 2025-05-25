<?php
session_start();
$config = require __DIR__ . '/config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (isset($config['users'][$user]) && $config['users'][$user] === $pass) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>登录 - <?= $config['site_name'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; background: #f0f0f0; padding: 50px; }
        .login-box { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input, button { width: 90%; padding: 10px; margin-top: 10px; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>登录 <?= $config['site_name'] ?></h2>
    <form method="post">
        <input type="text" name="username" placeholder="用户名" required>
        <input type="password" name="password" placeholder="密码" required>
        <button type="submit" style="background: #4CAF50; color: #fff; border: none; cursor: pointer;width: 30%;">登录</button>
    </form>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>
</body>
</html>