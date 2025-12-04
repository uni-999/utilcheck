<?php
// Очищаем буфер
if (ob_get_level() > 0) {
    ob_clean();
}

// Устанавливаем заголовки
header('Content-Type: application/json; charset=utf-8');

// Подключаем конфиг
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

try {
    // Проверяем метод
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Только POST запросы разрешены');
    }

    // Получаем данные
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('Нет данных в запросе');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Неверный JSON: ' . json_last_error_msg());
    }

    // Извлекаем данные
    $full_name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $login_username = trim($data['login_username'] ?? '');
    $password = $data['password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';

    // ВАЛИДАЦИЯ
    if (empty($full_name)) throw new Exception('Введите полное имя');
    if (empty($email)) throw new Exception('Введите email');
    if (empty($login_username)) throw new Exception('Введите логин');
    if (empty($password)) throw new Exception('Введите пароль');
    if (empty($confirm_password)) throw new Exception('Подтвердите пароль');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Неверный формат email');
    }

    if (strlen($password) < 8) {
        throw new Exception('Пароль должен быть не менее 8 символов');
    }

    // ПРОВЕРКА СОВПАДЕНИЯ ПАРОЛЕЙ
    if ($password !== $confirm_password) {
        throw new Exception('Пароли не совпадают');
    }

    if (strlen($login_username) < 3) {
        throw new Exception('Логин должен быть не менее 3 символов');
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $login_username)) {
        throw new Exception('Логин может содержать только латинские буквы, цифры и подчеркивание');
    }

    // ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Ошибка подключения к базе данных');
    }

    // ПРОВЕРКА УНИКАЛЬНОСТИ EMAIL
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Пользователь с таким email уже существует');
    }

    // ПРОВЕРКА УНИКАЛЬНОСТИ ЛОГИНА
    $stmt = $db->prepare("SELECT id FROM users WHERE login_username = ?");
    $stmt->execute([$login_username]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Пользователь с таким логином уже существует');
    }

    // ГЕНЕРАЦИЯ USERNAME (упрощенная)
    $username = generateUsername($full_name, $login_username);

    // ХЕШИРОВАНИЕ ПАРОЛЯ
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // СОЗДАНИЕ ПОЛЬЗОВАТЕЛЯ
    $stmt = $db->prepare("INSERT INTO users (full_name, email, username, login_username, password_hash, password_plain) VALUES (?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $full_name,
        $email,
        $username,
        $login_username,
        $password_hash,
        $password
    ]);

    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Регистрация успешно завершена! Теперь вы можете войти в систему.';
        $response['user_info'] = [
            'id' => $db->lastInsertId(),
            'username' => $username,
            'login' => $login_username
        ];
    } else {
        throw new Exception('Ошибка при создании пользователя');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Выводим результат
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;

// Функция для генерации username
function generateUsername($full_name, $login_username) {
    // Если имя содержит кириллицу, используем логин
    if (preg_match('/[а-яА-Я]/', $full_name)) {
        return $login_username;
    }
    
    // Для латинских имен создаем username из имени
    $parts = explode(' ', $full_name);
    $first_name = strtolower($parts[0]);
    $last_initial = isset($parts[1]) ? '_' . strtolower(substr($parts[1], 0, 1)) : '';
    
    $username = preg_replace('/[^a-z0-9_]/', '', $first_name . $last_initial);
    
    return empty($username) ? $login_username : $username;
}
?>