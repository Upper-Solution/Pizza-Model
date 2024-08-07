<?php
header('Content-Type: application/json');

//Caso ocorra algum erro na API, liberar essas linhas para resolução e rodar apiGetDB no navegador
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
// Inclui o arquivo de configuração
require_once '../config/config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die(json_encode(["error" => "Não foi possível conectar ao banco de dados."]));
}

// Query para buscar os pedidos
$sql = "SELECT taxaEntrega, descontoPedido FROM Empresa";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verifica se há pedidos
if (empty($orders)) {
    $orders = ["message" => "No orders found"];
}

// Retornar os pedidos como JSON
echo json_encode($orders);

$pdo = null;
?>