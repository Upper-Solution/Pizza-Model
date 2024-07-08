<?php
$host = 'localhost';
$dbname = 'delivery_app';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configura o PDO para lançar exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>
