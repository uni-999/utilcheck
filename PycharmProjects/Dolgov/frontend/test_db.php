<?php
require_once 'config.php';

echo "<h2>Тест подключения к базе данных OpenServer</h2>";

// Тест подключения
$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "<p style='color: green;'>✅ Успешное подключение к MySQL</p>";
    
    // Проверяем существование базы данных
    try {
        $stmt = $conn->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch();
        echo "<p>📊 База данных: " . $result['db_name'] . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка доступа к базе: " . $e->getMessage() . "</p>";
    }
    
    // Проверяем таблицу users
    try {
        $stmt = $conn->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Таблица users существует</p>";
            
            // Проверяем данные
            $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            echo "<p>👥 Пользователей в базе: " . $result['count'] . "</p>";
            
            // Показываем пользователей
            $stmt = $conn->query("SELECT id, username, email FROM users");
            echo "<h3>Зарегистрированные пользователи:</h3>";
            while ($row = $stmt->fetch()) {
                echo "<p>- " . $row['username'] . " (" . $row['email'] . ")</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Таблица users не существует</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Не удалось подключиться к MySQL</p>";
    echo "<p>Проверьте:</p>";
    echo "<ul>";
    echo "<li>Запущен ли MySQL в OpenServer</li>";
    echo "<li>Порт подключения (должен быть 3307)</li>";
    echo "<li>Логин и пароль</li>";
    echo "<li>Существует ли база данных 'netguardian'</li>";
    echo "</ul>";
}

echo "<br><a href='auth.html'>Перейти к авторизации</a>";
?>