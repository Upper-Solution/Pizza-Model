<?php
session_start();

// Inclui o arquivo de configuração
require_once '../config.php';

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
    $searchQuery = " WHERE nome LIKE ? OR id = ?";
    $searchValue = "%$search%";
}

// Verifica se um formulário de edição foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $imagem = null;

    if (is_uploaded_file($_FILES['imagem']['tmp_name'])) {
        $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
    }

    try {
        if ($imagem) {
            $stmt = $pdo->prepare("UPDATE Pizzas SET nome = ?, descricao = ?, preco = ?, imagem = ? WHERE id = ?");
            $stmt->execute([$nome, $descricao, $preco, $imagem, $edit_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE Pizzas SET nome = ?, descricao = ?, preco = ? WHERE id = ?");
            $stmt->execute([$nome, $descricao, $preco, $edit_id]);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        echo "Erro ao atualizar pizza: " . $e->getMessage();
    }
}

// Verifica se um formulário de adição foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pizza'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $imagem = file_get_contents($_FILES['imagem']['tmp_name']);

    try {
        $stmt = $pdo->prepare("INSERT INTO Pizzas (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $preco, $imagem]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        echo "Erro ao adicionar pizza: " . $e->getMessage();
    }
}

// Consulta SQL para obter os dados das pizzas
try {
    $stmt = $pdo->prepare("SELECT * FROM Pizzas" . $searchQuery . " ORDER BY id DESC");
    $stmt->execute($searchValue ? [$searchValue, $search] : []);
    $pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar dados das pizzas: " . $e->getMessage();
}

// Fechar conexão
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/adm.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Pizzaria - Menu</title>
</head>
<body>
    <div class="container">
        <div class="pizzas-container">
            <!-- Novo container para título e botão de voltar -->
            <div class="header-container">
                <h2 class="pizzas-title">Lanches Cadastrados</h2>
                <a href="adm-painel.php" class="btn-back">Voltar</a>
            </div>
            <!-- Formulário de pesquisa -->
            <form method="GET" action="">
                <label for="search">Pesquisar:</label>
                <input type="text" id="search" name="search" placeholder="Nome ou ID" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                <button type="submit">Buscar</button>
            </form>
            <!-- Botão para adicionar pizza -->
            <button class="btn-add-pizza" onclick="showAddForm()">Adicionar Pizza</button>
            <!-- Formulário de adição, inicialmente escondido -->
            <div id="add-form" class="add-form" style="display:none;">
                <form method="POST" action="" enctype="multipart/form-data">
                    <h3>Adicionar Nova Pizza</h3>
                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" required>
                    <br>
                    <label for="descricao">Descrição:</label>
                    <textarea name="descricao" required></textarea>
                    <br>
                    <label for="preco">Preço (R$):</label>
                    <input type="number" name="preco" step="0.01" required>
                    <br>
                    <label for="imagem">Imagem:</label>
                    <input type="file" name="imagem" accept="image/*">
                    <br>
                    <button type="submit" name="add_pizza">Adicionar</button>
                    <button type="button" onclick="hideAddForm()">Cancelar</button>
                </form>
            </div>
            <!-- Tabela de pizzas -->
            <table class="table-bg">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Descrição</th>
                        <th scope="col">Preço (R$)</th>
                        <th scope="col">Imagem</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pizzas): ?>
                        <?php foreach ($pizzas as $pizza): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pizza['id']); ?></td>
                                <td><?php echo htmlspecialchars($pizza['nome']); ?></td>
                                <td><?php echo htmlspecialchars($pizza['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($pizza['preco']); ?></td>
                                <td>
                                    <?php if ($pizza['imagem']): ?>
                                        <img src="exibir_imagem.php?id=<?php echo htmlspecialchars($pizza['id']); ?>" alt="Imagem da Pizza" style="max-width: 100px;">
                                    <?php else: ?>
                                        <span>Sem imagem</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Botão para mostrar o formulário de edição -->
                                    <button class="btn-edit" onclick="showEditForm(<?php echo htmlspecialchars($pizza['id']); ?>)">Editar</button>
                                    <a href="delete_pizza.php?id=<?php echo htmlspecialchars($pizza['id']); ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta pizza?');">Excluir</a>
                                </td>
                            </tr>
                            <!-- Formulário de edição, inicialmente escondido -->
                            <tr id="edit-form-<?php echo htmlspecialchars($pizza['id']); ?>" class="edit-form" style="display:none;">
                                <td colspan="6">
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($pizza['id']); ?>">
                                        <label for="nome">Nome:</label>
                                        <input type="text" name="nome" value="<?php echo htmlspecialchars($pizza['nome']); ?>" required>
                                        <br>
                                        <label for="descricao">Descrição:</label>
                                        <textarea name="descricao" required><?php echo htmlspecialchars($pizza['descricao']); ?></textarea>
                                        <br>
                                        <label for="preco">Preço (R$):</label>
                                        <input type="number" name="preco" step="0.01" value="<?php echo htmlspecialchars($pizza['preco']); ?>" required>
                                        <br>
                                        <label for="imagem">Imagem:</label>
                                        <input type="file" name="imagem" accept="image/*">
                                        <br>
                                        <button type="submit">Salvar</button>
                                        <button type="button" onclick="hideEditForm(<?php echo htmlspecialchars($pizza['id']); ?>)">Cancelar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Nenhuma pizza encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function showAddForm() {
            document.getElementById('add-form').style.display = 'block';
        }

        function hideAddForm() {
            document.getElementById('add-form').style.display = 'none';
        }

        function showEditForm(id) {
            document.getElementById('edit-form-' + id).style.display = 'table-row';
        }

        function hideEditForm(id) {
            document.getElementById('edit-form-' + id).style.display = 'none';
        }
    </script>
</body>
</html>
