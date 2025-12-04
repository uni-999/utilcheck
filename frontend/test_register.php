<?php
// Простой тест регистрации
header('Content-Type: text/html; charset=utf-8');

if ($_POST) {
    // Тестируем напрямую
    $test_data = [
        'full_name' => 'Тест Пользователь',
        'email' => 'test' . rand(1000,9999) . '@test.com',
        'login_username' => 'testuser' . rand(1000,9999),
        'password' => 'testpassword123',
        'confirm_password' => 'testpassword123'
    ];
    
    echo "<h3>Тестовые данные:</h3>";
    echo "<pre>" . print_r($test_data, true) . "</pre>";
    
    // Отправляем запрос к register.php
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/register.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "<h3>Ответ сервера (код $http_code):</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    $result = json_decode($response, true);
    if ($result && $result['success']) {
        echo "<p style='color: green;'>✅ Регистрация успешна!</p>";
    } else {
        echo "<p style='color: red;'>❌ Ошибка: " . ($result['message'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo '<form method="post">
        <button type="submit">Протестировать регистрацию</button>
    </form>';
}

echo '<br><a href="auth.html">Вернуться к форме регистрации</a>';
?>