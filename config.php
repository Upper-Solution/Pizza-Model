<?php
$host = '127.0.0.1';  // Endereço IP do banco de dados
$port = '3306';  // Porta padrão do MySQL
$dbname = 'u778175734_PIzzaDB';
$username = 'u778175734_upper';
$password = '5pp2rr2s4l5t34N';

try {
    // Adiciona a porta na string de conexão
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    // Configura o PDO para lançar exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conectado com sucesso!";
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>