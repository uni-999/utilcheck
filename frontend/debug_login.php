<?php
header('Content-Type: text/html; charset=utf-8');
echo "<pre>";
echo "=== ДИАГНОСТИКА ВХОДА ===\n\n";

// 1. Проверка существующих пользователей
require_once 'config.php';
$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "1. СУЩЕСТВУЮЩИЕ ПОЛЬЗОВАТЕЛИ:\n";
    $stmt = $db->query("SELECT id, username, login_username, email, password_plain FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "   - ID: " . $user['id'] . 
             ", Username: " . $user['username'] . 
             ", Login: " . $user['login_username'] . 
             ", Email: " . $user['email'] . 
             ", Password: " . $user['password_plain'] . "\n";
    }
}

// 2. Тестирование входа
echo "\n2. ТЕСТИРОВАНИЕ ВХОДА:\n";

// Тест 1: Вход по логину
$test_data1 = [
    'login' => 'admin',
    'password' => 'password'
];

echo "   Тест 1 - Вход по логину 'admin':\n";
testLogin($test_data1);

// Тест 2: Вход по email
$test_data2 = [
    'login' => 'admin@netguardian.ru',
    'password' => 'password'
];

echo "\n   Тест 2 - Вход по email 'admin@netguardian.ru':\n";
testLogin($test_data2);

// Тест 3: Вход нового пользователя
if (!empty($users) && count($users) > 1) {
    $last_user = end($users);
    $test_data3 = [
        'login' => $last_user['login_username'],
        'password' => $last_user['password_plain']
    ];
    
    echo "\n   Тест 3 - Вход нового пользователя '" . $last_user['login_username'] . "':\n";
    testLogin($test_data3);
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
echo "</pre>";

// Функция для тестирования входа
function testLogin($test_data) {
    echo "     Данные: " . json_encode($test_data) . "\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'http://' . $_SERVER['HTTP_HOST'] . '/login.php',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($test_data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "     HTTP код: " . $http_code . "\n";
    
    if ($error) {
        echo "     Ошибка cURL: " . $error . "\n";
        return;
    }
    
    // Проверяем JSON
    $result = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if ($result['success']) {
            echo "     ✅ УСПЕХ: " . $result['message'] . "\n";
            echo "     Данные пользователя: " . json_encode($result['user'] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
            
            // Проверяем сессию
            if (isset($result['user']['id'])) {
                echo "     ✅ Сессия создана для пользователя ID: " . $result['user']['id'] . "\n";
            }
        } else {
            echo "     ❌ ОШИБКА: " . $result['message'] . "\n";
        }
    } else {
        echo "     ❌ НЕВЕРНЫЙ JSON: " . htmlspecialchars(substr($response, 0, 200)) . "\n";
    }
}

echo '<br><br><a href="auth.html" style="font-size: 18px; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;">Вернуться ко входу</a>';
?>