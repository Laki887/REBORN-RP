<?php
session_start();

// Настройки подключения к БД
$host = 'localhost';
$dbname = 'news_site';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Базовый URL сайта (используется для ссылок на изображения)
define('BASE_URL', 'http://localhost/news-site/'); // замените на свой домен
?>