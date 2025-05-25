<?php
date_default_timezone_set('Asia/Shanghai');
session_start();
$config = require __DIR__ . '/config.php';

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}