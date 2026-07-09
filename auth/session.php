<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

if (($_SESSION['user']['tipo'] ?? '') !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}
