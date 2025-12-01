<?php
header('Content-Type: text/html; charset=utf-8');
echo "<pre>";
echo "=== ПОЛНАЯ ДИАГНОСТИКА СИСТЕМЫ ===\n\n";

// 1. Проверка сервера
echo "1. ИНФОРМАЦИЯ О СЕРВЕРЕ:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "   Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

// 2. Проверка расширений
echo "\n2. ПРОВЕРКА РАСШИРЕНИЙ PHP:\n";
$extensions = ['pdo_mysql', 'json', 'mbstring', 'session'];
foreach ($extensions as $ext) {
    echo "   " . $ext . ": " . (extension_loaded($ext) ? '✅' : '❌') . "\n";
}

// 3. Проверка конфигурации БД
echo "\n3. ПРОВЕРКА БАЗЫ ДАННЫХ:\n";
$config_file = 'config.php';
if (file_exists($config_file)) {
    echo "   ✅ config.php существует\n";
    
    require_once 'config.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "   ✅ Подключение к MySQL успешно\n";
        
        // Проверка таблицы
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                echo "   ✅ Таблица users существует\n";
                
                // Проверка структуры
                $stmt = $db->query("DESCRIBE users");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "   Структура таблицы:\n";
                foreach ($columns as $col) {
                    echo "     - " . $col['Field'] . " (" . $col['Type'] . ")\n";
                }
                
                // Проверка данных
                $stmt = $db->query("SELECT COUNT(*) as count FROM users");
                $count = $stmt->fetch()['count'];
                echo "   Пользователей в базе: " . $count . "\n";
                
                if ($count > 0) {
                    $stmt = $db->query("SELECT id, username, login_username, email FROM users");
                    $users = $stmt->fetchAll();
                    echo "   Список пользователей:\n";
                    foreach ($users as $user) {
                        echo "     - ID: " . $user['id'] . ", Username: " . $user['username'] . 
                             ", Login: " . $user['login_username'] . ", Email: " . $user['email'] . "\n";
                    }
                }
            } else {
                echo "   ❌ Таблица users не существует\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Ошибка проверки таблицы: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Не удалось подключиться к MySQL\n";
    }
} else {
    echo "   ❌ config.php не найден\n";
}

// 4. Проверка файлов
echo "\n4. ПРОВЕРКА ФАЙЛОВ:\n";
$files = [
    'config.php',
    'register.php', 
    'login.php',
    'auth.html',
    'app.js',
    'styles.css',
    'check_auth.php',
    'logout.php'
];

foreach ($files as $file) {
    $exists = file_exists($file);
    $size = $exists ? filesize($file) : 0;
    echo "   " . ($exists ? '✅' : '❌') . " " . $file . " (" . $size . " bytes)\n";
}

// 5. Тестирование регистрации
echo "\n5. ТЕСТИРОВАНИЕ РЕГИСТРАЦИИ:\n";

// Тест 1: Простая регистрация
$test_data1 = [
    'full_name' => 'Иван Иванов',
    'email' => 'ivan' . rand(1000, 9999) . '@test.com',
    'login_username' => 'ivan' . rand(1000, 9999),
    'password' => 'password123',
    'confirm_password' => 'password123'
];

echo "   Тест 1 - Простая регистрация:\n";
testRegistration($test_data1);

// Тест 2: Русское имя
$test_data2 = [
    'full_name' => 'Амир Халиков',
    'email' => 'amir' . rand(1000, 9999) . '@test.com',
    'login_username' => 'amir_khalikov',
    'password' => 'amirpidor229',
    'confirm_password' => 'amirpidor229'
];

echo "\n   Тест 2 - Русское имя:\n";
testRegistration($test_data2);

// 6. Проверка сессий
echo "\n6. ПРОВЕРКА СЕССИЙ:\n";
echo "   session_status(): " . session_status() . " (2 = PHP_SESSION_ACTIVE)\n";
echo "   session_id(): " . (session_id() ?: 'не установлена') . "\n";

// 7. Проверка прав доступа
echo "\n7. ПРОВЕРКА ПРАВ ДОСТУПА:\n";
$writable_files = ['config.php', 'register.php', 'login.php'];
foreach ($writable_files as $file) {
    if (file_exists($file)) {
        $writable = is_writable($file);
        echo "   " . $file . ": " . ($writable ? '✅ запись разрешена' : '❌ запись запрещена') . "\n";
    }
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
echo "</pre>";

// Функция для тестирования регистрации
function testRegistration($test_data) {
    echo "     Данные: " . json_encode($test_data, JSON_UNESCAPED_UNICODE) . "\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'http://' . $_SERVER['HTTP_HOST'] . '/register.php',
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
            if (isset($result['user_info'])) {
                echo "     Данные пользователя: " . json_encode($result['user_info'], JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "     ❌ ОШИБКА: " . $result['message'] . "\n";
        }
    } else {
        echo "     ❌ НЕВЕРНЫЙ JSON: " . htmlspecialchars(substr($response, 0, 200)) . "\n";
    }
}

echo '<br><br><a href="auth.html" style="font-size: 18px; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;">Вернуться к регистрации</a>';
?>