<?php
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Verificar se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: adm-login.php');
    exit;
}

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Verifica se foi feito um filtro de pesquisa
$searchQuery = '';
$searchValue = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $searchQuery = " WHERE customer_name LIKE ? OR order_id = ?";
    $searchValue = ["%$search%", $search];
}

// Consulta SQL para obter os dados dos pedidos
try {
    $stmt = $pdo->prepare("SELECT * FROM orders" . $searchQuery . " ORDER BY order_date DESC");
    if ($searchValue) {
        $stmt->execute($searchValue);
    } else {
        $stmt->execute();
    }
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao consultar dados dos pedidos: " . $e->getMessage()]);
}

// Fechar conexão
$pdo = null;
?>
