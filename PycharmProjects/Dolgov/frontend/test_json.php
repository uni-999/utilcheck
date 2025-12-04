<?php
// Тестируем чистоту JSON вывода
header('Content-Type: application/json; charset=utf-8');

$testData = [
    'success' => true,
    'message' => 'Test message',
    'data' => ['test' => 'value']
];

echo json_encode($testData, JSON_UNESCAPED_UNICODE);
?>