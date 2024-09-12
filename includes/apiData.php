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
    $sql = "SELECT id, nome, imagem, preco, descricao, Adicionais FROM Pizzas";
    $stmt = $pdo->query($sql);

    $pizzas = [];

    // Busca todos os resultados da consulta
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Converte a imagem BLOB para base64
        $row['imagem'] = base64_encode($row['imagem']);

        // Processa os adicionais
        if (!empty($row['Adicionais'])) {
            // Separa os IDs de adicionais por vírgula
            $adicionaisIds = explode(',', $row['Adicionais']);
            $adicionaisIds = array_map('trim', $adicionaisIds); // Remove espaços em branco

            // Prepara a consulta para buscar os adicionais
            $placeholders = implode(',', array_fill(0, count($adicionaisIds), '?'));
            $sqlAdicionais = "SELECT id, nome, preco FROM Adicionais WHERE id IN ($placeholders)";
            $stmtAdicionais = $pdo->prepare($sqlAdicionais);

            // Executa a consulta com os IDs dos adicionais
            $stmtAdicionais->execute($adicionaisIds);

            // Recupera os dados dos adicionais
            $adicionaisArray = $stmtAdicionais->fetchAll(PDO::FETCH_ASSOC);
            $row['Adicionais'] = $adicionaisArray; // Atualiza o array de adicionais
        } else {
            $row['Adicionais'] = []; // Se não houver adicionais, retorna um array vazio
        }

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
