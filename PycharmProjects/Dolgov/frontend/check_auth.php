<?php
require_once 'config.php';

function checkAuth() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: auth.html');
        exit;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name']
    ];
}

// Проверка для AJAX
if (isset($_GET['check'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode(['authenticated' => false]);
        exit;
    }
    
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name']
        ]
    ]);
    exit;
}

// Стандартная проверка
checkAuth();
?>