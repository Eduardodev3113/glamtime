<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/src/Database.php';

try {
    $pdo = Database::getConnection();
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Falha ao conectar à base de dados. Contate o administrador.');
}