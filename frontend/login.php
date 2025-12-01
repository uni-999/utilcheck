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
    $login = trim($data['login'] ?? '');
    $password = $data['password'] ?? '';

    // ВАЛИДАЦИЯ
    if (empty($login)) throw new Exception('Введите логин или email');
    if (empty($password)) throw new Exception('Введите пароль');

    // ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Ошибка подключения к базе данных');
    }

    // ПОИСК ПОЛЬЗОВАТЕЛЯ
    $query = "SELECT id, username, login_username, password_hash, full_name, is_active 
              FROM users 
              WHERE (login_username = :login OR email = :login) AND is_active = 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':login', $login);
    
    if (!$stmt->execute()) {
        throw new Exception('Ошибка выполнения запроса');
    }

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // ПРОВЕРКА ПАРОЛЯ
        if (password_verify($password, $user['password_hash'])) {
            // ОБНОВЛЕНИЕ ВРЕМЕНИ ВХОДА
            $update_query = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();

            // СОЗДАНИЕ СЕССИИ
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_username'] = $user['login_username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;

            $response['success'] = true;
            $response['message'] = 'Вход выполнен успешно!';
            $response['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name']
            ];
            
        } else {
            throw new Exception('Неверный логин или пароль');
        }
    } else {
        throw new Exception('Неверный логин или пароль');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Выводим результат
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>