<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    echo 'Usuário não está logado.';
    exit();
}

// Lógica para finalizar o pedido aqui

echo 'Pedido finalizado com sucesso!';
?>

