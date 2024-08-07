<?php
session_start();
header('Content-Type: application/json');

// Verifica se o usuário está logado
$isLoggedIn = isset($_SESSION['user_id']);
echo json_encode(['loggedIn' => $isLoggedIn]);
?>
