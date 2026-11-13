<?php

date_default_timezone_set('Europe/Madrid');

$host = 'localhost';
$db = 'appcc';
$user = 'TU_USUARIO';
$pass = 'TU_CONTRASEÑA';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
} catch (PDOException $e) {
    die('Error de conexión a la base de datos');
}