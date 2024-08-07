<?php
header('Content-Type: application/json');

// Inclui o arquivo de configuração
require_once '../config/config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    echo json_encode(["error" => "Não foi possível conectar ao banco de dados."]);
    exit();
}

try {
    // Consulta para obter os dados das pizzas
    $sql = "SELECT id, nome, imagem, preco, descricao FROM Pizzas";
    $stmt = $pdo->query($sql);

    $pizzas = [];

    // Busca todos os resultados da consulta
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Converte a imagem BLOB para base64
        $row['imagem'] = base64_encode($row['imagem']);
        // Adiciona o resultado no array de pizzas
        $pizzas[] = $row;
    }

    // Retorna os dados em formato JSON
    echo json_encode($pizzas, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    // Retorna uma mensagem de erro em caso de falha na consulta
    echo json_encode(["error" => "Erro na consulta: " . $e->getMessage()]);
}
?>
