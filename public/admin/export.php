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

// Define os filtros
$searchQuery = '';
$searchValue = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $searchQuery .= " AND (customer_name LIKE ? OR order_id = ?)";
    $searchValue[] = "%$search%";
    $searchValue[] = $search;
}

if (isset($_GET['month']) && !empty($_GET['month'])) {
    $month = $_GET['month'];
    $searchQuery .= " AND MONTH(order_date) = ?";
    $searchValue[] = $month;
}

if (isset($_GET['year']) && !empty($_GET['year'])) {
    $year = $_GET['year'];
    $searchQuery .= " AND YEAR(order_date) = ?";
    $searchValue[] = $year;
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $_GET['status'];
    $searchQuery .= " AND status = ?";
    $searchValue[] = $status;
}

// Consulta SQL para obter os dados dos pedidos com os filtros aplicados
try {
    $dataQuery = "SELECT order_id, customer_name, order_date, total, status FROM orders WHERE 1=1" . $searchQuery . " ORDER BY order_date DESC";
    $stmt = $pdo->prepare($dataQuery);
    $stmt->execute($searchValue);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao consultar dados dos pedidos: " . $e->getMessage());
}

// Fechar conexão
$pdo = null;

// Gera o arquivo CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="pedidos.csv"');

// Adiciona BOM para UTF-8
echo "\xEF\xBB\xBF"; // Adiciona BOM para UTF-8

// Abre o fluxo de saída para escrever o CSV
$output = fopen('php://output', 'w');

// Adiciona o cabeçalho do CSV
fputcsv($output, ['ID', 'ID do Cliente', 'Data do Pedido', 'Valor Total', 'Status']);

// Inicializa a variável para somar o total
$totalValue = 0;

// Adiciona os dados das linhas ao CSV
foreach ($orders as $order) {
    // Adiciona os dados da linha ao CSV
    fputcsv($output, [
        $order['order_id'],
        $order['customer_name'],
        $order['order_date'],
        number_format((float)$order['total'], 2, '.', ''), // Formata o valor total para 2 casas decimais
        $order['status']
    ]);

    // Soma o valor total
    $totalValue += (float)$order['total'];
}

// Adiciona a linha de total ao final do CSV
fputcsv($output, ['Total', '', '', number_format($totalValue, 2, '.', ''), '']);

// Fecha o fluxo de saída
fclose($output);
exit;
