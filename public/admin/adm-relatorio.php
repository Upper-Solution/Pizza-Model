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

// Define o número de linhas por página
$rowsPerPage = 30;

// Obtém a página atual
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Calcula o deslocamento
$offset = ($page - 1) * $rowsPerPage;

// Verifica se foi feito um filtro de pesquisa
$searchQuery = '';
$searchValue = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $searchQuery .= " AND (customer_name LIKE ? OR order_id = ?)";
    $searchValue[] = "%$search%";
    $searchValue[] = $search;
}

// Filtros adicionais
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

// Consulta SQL para obter o total de pedidos (para paginação)
try {
    $countQuery = "SELECT COUNT(*) FROM orders WHERE 1=1" . $searchQuery;
    $countStmt = $pdo->prepare($countQuery);
    if ($searchValue) {
        $countStmt->execute($searchValue);
    } else {
        $countStmt->execute();
    }
    $totalRows = $countStmt->fetchColumn();
} catch (PDOException $e) {
    echo "Erro ao contar pedidos: " . $e->getMessage();
}

// Consulta SQL para obter os dados dos pedidos com paginação
try {
    $dataQuery = "SELECT * FROM orders WHERE 1=1" . $searchQuery . " ORDER BY order_date DESC LIMIT $rowsPerPage OFFSET $offset";
    $stmt = $pdo->prepare($dataQuery);
    if ($searchValue) {
        $stmt->execute($searchValue);
    } else {
        $stmt->execute();
    }
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar dados dos pedidos: " . $e->getMessage();
}

// Fechar conexão
$pdo = null;

// Calcula o número total de páginas
$totalPages = ceil($totalRows / $rowsPerPage);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/relatorio.css">
    <title>Visualizar Pedidos</title>
</head>
<body>
    <header>
        <h1>Administração de Pedidos</h1>
    </header>
    <div class="container">
        <h2>Lista de Pedidos</h2>
        <div class="search-container">
            <form method="get" action="">
                <!-- Formulário de filtro existente -->
                <input type="text" name="search" placeholder="Pesquisar por nome ou ID" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '', ENT_QUOTES); ?>">
                
                <select name="month">
                    <option value="">Mês</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (isset($_GET['month']) && $_GET['month'] == $i) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
                
                <select name="year">
                    <option value="">Ano</option>
                    <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo (isset($_GET['year']) && $_GET['year'] == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                
                <select name="status">
                    <option value="">Status</option>
                    <option value="recusado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'recusado') ? 'selected' : ''; ?>>Recusado</option>
                    <option value="entregue" <?php echo (isset($_GET['status']) && $_GET['status'] == 'entregue') ? 'selected' : ''; ?>>Entregue</option>
                </select>
                
                <input type="submit" value="Filtrar">
            </form>
            <form method="get" action="export.php" style="margin-top: 10px;">
                <!-- Formulário de exportação -->
                <input type="hidden" name="search" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '', ENT_QUOTES); ?>">
                <input type="hidden" name="month" value="<?php echo htmlspecialchars(isset($_GET['month']) ? $_GET['month'] : '', ENT_QUOTES); ?>">
                <input type="hidden" name="year" value="<?php echo htmlspecialchars(isset($_GET['year']) ? $_GET['year'] : '', ENT_QUOTES); ?>">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars(isset($_GET['status']) ? $_GET['status'] : '', ENT_QUOTES); ?>">
                <input type="submit" value="Exportar CSV">
            </form>
        </div>
        <?php if (!empty($orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID do Cliente</th>
                        <th>Data do Pedido</th>
                        <th>Valor Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order["order_id"]); ?></td>
                            <td><?php echo htmlspecialchars($order["customer_name"]); ?></td>
                            <td><?php echo htmlspecialchars($order["order_date"]); ?></td>
                            <td><?php echo htmlspecialchars($order["total"]); ?></td>
                            <td><?php echo htmlspecialchars($order["status"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['month']) ? '&month=' . urlencode($_GET['month']) : ''; ?><?php echo isset($_GET['year']) ? '&year=' . urlencode($_GET['year']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>">« Anterior</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['month']) ? '&month=' . urlencode($_GET['month']) : ''; ?><?php echo isset($_GET['year']) ? '&year=' . urlencode($_GET['year']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>">Próximo »</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Nenhum pedido encontrado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
