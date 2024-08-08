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

// Verifica se um formulário de atualização de imagem foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_image_id'])) {
        $update_image_id = $_POST['update_image_id'];
        $imagem = null;

        if (is_uploaded_file($_FILES['imagem']['tmp_name'])) {
            $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
        }

        try {
            if ($imagem) {
                $stmt = $pdo->prepare("UPDATE Pizzas SET imagem = ? WHERE id = ?");
                $stmt->execute([$imagem, $update_image_id]);
            }
        } catch (PDOException $e) {
            echo "Erro ao atualizar imagem da pizza: " . $e->getMessage();
        }
    } elseif (isset($_POST['save_all_images'])) {
        // Atualizar todas as imagens de uma vez
        foreach ($_FILES['images']['name'] as $key => $name) {
            if (!empty($name) && is_uploaded_file($_FILES['images']['tmp_name'][$key])) {
                $imagem = file_get_contents($_FILES['images']['tmp_name'][$key]);
                $pizza_id = $_POST['pizza_ids'][$key];

                try {
                    $stmt = $pdo->prepare("UPDATE Pizzas SET imagem = ? WHERE id = ?");
                    $stmt->execute([$imagem, $pizza_id]);
                } catch (PDOException $e) {
                    echo "Erro ao atualizar imagem da pizza com ID $pizza_id: " . $e->getMessage();
                }
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Consulta SQL para obter os dados das pizzas
try {
    $stmt = $pdo->prepare("SELECT id, nome, imagem FROM Pizzas ORDER BY id DESC");
    $stmt->execute();
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
    <link rel="stylesheet" href="../css/adm.css">
    <title>Alterar Imagens das Pizzas</title>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="header-title">Alterar Imagens das Pizzas</h1>
            <a href="adm-painel.php" class="btn-back">Voltar</a>
        </div>
    </header>
    <div class="container">
        <div class="pizzas-container">
            <!-- Formulário para atualizar todas as imagens -->
            <form method="POST" action="" enctype="multipart/form-data">
                <table class="table-bg">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Imagem Atual</th>
                            <th>Nova Imagem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pizzas): ?>
                            <?php foreach ($pizzas as $key => $pizza): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pizza['id']); ?></td>
                                    <td><?php echo htmlspecialchars($pizza['nome']); ?></td>
                                    <td>
                                        <?php if ($pizza['imagem']): ?>
                                            <img src="exibir_imagem.php?id=<?php echo htmlspecialchars($pizza['id']); ?>" alt="Imagem da Pizza" class="pizza-image">
                                        <?php else: ?>
                                            <span>Sem imagem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="file" name="images[]" accept="image/*">
                                        <input type="hidden" name="pizza_ids[]" value="<?php echo htmlspecialchars($pizza['id']); ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Nenhuma pizza encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_all_images">Salvar Todas as Imagens</button>
            </form>
        </div>
    </div>
</body>
</html>
