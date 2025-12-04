<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Зарегистрированные пользователи</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr>
        <th>ID</th>
        <th>Полное имя</th>
        <th>Email</th>
        <th>Username</th>
        <th>Логин для входа</th>
        <th>Пароль (открытый)</th>
        <th>Дата регистрации</th>
      </tr>";

$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();

while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
    echo "<td>" . htmlspecialchars($user['login_username']) . "</td>";
    echo "<td>" . htmlspecialchars($user['password_plain'] ?? '') . "</td>";
    echo "<td>" . $user['created_at'] . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><a href='auth.html'>Вернуться к авторизации</a>";
?>