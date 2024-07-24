<?php

// Inclui o arquivo de configuração
require_once 'config.php';

// Obtém a conexão com o banco de dados
try {
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    exit();
}

header('Content-Type: application/json');

// Consulta para buscar pizzas e tamanhos
$sql = "SELECT 
            p.id AS pizza_id, 
            p.nome, 
            p.descricao, 
            t.id AS tamanho_id, 
            t.tamanho, 
            t.preco
        FROM 
            pizzas p
        JOIN 
            tamanhos t ON p.id = t.pizza_id";
$result = $pdo->query($sql);

$pizzas = array();

if ($result->rowCount() > 0) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $pizza_id = $row['pizza_id'];
        if (!isset($pizzas[$pizza_id])) {
            $pizzas[$pizza_id] = array(
                'id' => $pizza_id,
                'nome' => $row['nome'],
                'descricao' => $row['descricao'],
                'tamanhos' => array()
            );
        }
        $pizzas[$pizza_id]['tamanhos'][] = array(
            'id' => $row['tamanho_id'],
            'tamanho' => $row['tamanho'],
            'preco' => $row['preco']
        );
    }
}

echo json_encode(array_values($pizzas));

$pdo = null;
?>
