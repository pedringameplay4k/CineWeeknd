<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: paglog.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$filme = $_POST['filme'];
$quantity = 1;  // Supondo que a quantidade seja sempre 1

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CineWeeknd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verificar se o user_id existe na tabela users_register
$stmt = $conn->prepare("SELECT id FROM users_register WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_id_check);
$stmt->fetch();
$stmt->close();

if (!$user_id_check) {
    die("Usuário não encontrado. Verifique se está logado corretamente.");
}

// Obter o id do filme
$stmt = $conn->prepare("SELECT id FROM filmes WHERE titulo = ?");
$stmt->bind_param("s", $filme);
$stmt->execute();
$stmt->bind_result($filme_id);
$stmt->fetch();
$stmt->close();

if (!$filme_id) {
    header('Location: index.php');
    exit();
}

// Registrar a compra
$stmt = $conn->prepare("INSERT INTO purchases (user_id, filme_id, quantity) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $user_id, $filme_id, $quantity);

if ($stmt->execute()) {
    // Redirecionar para a página de histórico de compras
    header('Location: purchase_history.php');
} else {
    die("Erro ao registrar a compra: " . $stmt->error);
}

$stmt->close();
$conn->close();

exit();
?>
