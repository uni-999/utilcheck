<?php
header('Content-Type: application/json; charset=utf-8');

// Тестовые данные для проверки
$testData = [
    'login' => 'admin',
    'password' => 'password'
];

$url = 'http://' . $_SERVER['HTTP_HOST'] . '/login.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($testData))
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo json_encode([
    'http_code' => $httpCode,
    'curl_error' => $error,
    'response' => $response,
    'response_json' => json_decode($response, true)
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>