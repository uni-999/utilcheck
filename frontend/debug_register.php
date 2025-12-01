<?php
header('Content-Type: text/html; charset=utf-8');
echo "<pre>";

// Включим все ошибки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== ДИАГНОСТИКА РЕГИСТРАЦИИ ===\n\n";

// Проверяем конфигурацию
echo "1. Проверка конфигурации:\n";
$config_file = 'config.php';
if (file_exists($config_file)) {
    echo "✅ config.php существует\n";
    
    // Проверяем подключение к БД
    require_once 'config.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Подключение к БД успешно\n";
        
        // Проверяем таблицу users
        try {
            $stmt = $db->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "✅ Таблица users существует\n";
            echo "   Столбцы: " . implode(', ', $columns) . "\n";
            
            // Проверяем существующих пользователей
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            echo "   Пользователей в базе: " . $result['count'] . "\n";
            
        } catch (Exception $e) {
            echo "❌ Ошибка таблицы users: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ Ошибка подключения к БД\n";
    }
} else {
    echo "❌ config.php не найден\n";
}

echo "\n2. Тестирование регистрации:\n";

// Тестовые данные
$test_data = [
    'full_name' => 'Тест Пользователь',
    'email' => 'test' . rand(1000, 9999) . '@test.com',
    'login_username' => 'testuser' . rand(1000, 9999),
    'password' => 'testpassword123',
    'confirm_password' => 'testpassword123'
];

echo "   Тестовые данные:\n";
print_r($test_data);

// Отправляем запрос к register.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "   HTTP код: " . $http_code . "\n";
echo "   Ответ сервера: " . $response . "\n";

if ($error) {
    echo "   Ошибка cURL: " . $error . "\n";
}

$result = json_decode($response, true);
if ($result) {
    if ($result['success']) {
        echo "✅ РЕГИСТРАЦИЯ УСПЕШНА!\n";
        echo "   Сообщение: " . $result['message'] . "\n";
        if (isset($result['user_info'])) {
            echo "   Данные пользователя:\n";
            print_r($result['user_info']);
        }
    } else {
        echo "❌ ОШИБКА РЕГИСТРАЦИИ:\n";
        echo "   Сообщение: " . $result['message'] . "\n";
    }
} else {
    echo "❌ НЕВЕРНЫЙ JSON ОТВЕТ\n";
    echo "   Raw ответ: " . htmlspecialchars($response) . "\n";
}

echo "\n3. Проверка файлов:\n";
$files = ['config.php', 'register.php', 'login.php', 'auth.html', 'app.js', 'styles.css'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ " . $file . " - существует\n";
    } else {
        echo "❌ " . $file . " - НЕ НАЙДЕН\n";
    }
}

echo "\n=== КОНЕЦ ДИАГНОСТИКИ ===\n";
echo "</pre>";

echo '<br><a href="auth.html">Вернуться к форме регистрации</a>';
?>