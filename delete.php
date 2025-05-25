<?php
require 'auth.php';
require_login();
$config = require 'config.php';

$file = basename($_GET['file'] ?? '');
$path = $config['post_dir'] . '/' . $file;

if (preg_match('/\.md$/', $file) && file_exists($path)) {
    unlink($path);
}
header("Location: dashboard.php");