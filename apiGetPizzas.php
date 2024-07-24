<?php

// Habilita a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de configuração
require_once 'config.php';

// Obtém a conexão com o banco de dados
try {
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
    exit();
}

header('Content-Type: application/json');

// Consulta para buscar pizzas e tamanhos
$sql = "SELECT 
            p.id AS pizza_id, 
            p.nome AS pizza_name, 
            p.descricao AS pizza_description,
            p.imagem AS pizza_img,
            t.preco AS tamanho_preco,
            t.tamanho AS tamanho_tamanho
        FROM 
            Pizzas p
        JOIN 
            Tamanhos t ON p.id = t.id";

$result = $pdo->query($sql);

$pizzas = array();

if ($result->rowCount() > 0) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $pizza_id = $row['pizza_id'];
        if (!isset($pizzas[$pizza_id])) {
            $pizzas[$pizza_id] = array(
                'id' => $pizza_id,
                'name' => $row['pizza_name'],
                'img' => $row['pizza_img'],
                'price' => array(),
                'sizes' => array(),
                'description' => $row['pizza_description']
            );
        }
        // Adiciona o preço e o tamanho
        $pizzas[$pizza_id]['price'][] = (float) $row['tamanho_preco'];
        $pizzas[$pizza_id]['sizes'][] = $row['tamanho_tamanho'];
    }

    // Reordena os resultados para garantir que os preços e tamanhos estejam em ordem
    foreach ($pizzas as &$pizza) {
        array_multisort($pizza['sizes'], SORT_ASC, $pizza['price']);
    }

    echo json_encode(array_values($pizzas));
} else {
    echo json_encode(array("error" => "Nenhuma pizza encontrada."));
}

$pdo = null;
?>
