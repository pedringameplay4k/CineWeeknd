<?php
session_start();
require 'User.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: paglog.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CineWeeknd";

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obter as compras do usuário
$stmt = $conn->prepare("SELECT filmes.titulo, purchases.quantity, purchases.purchase_date FROM purchases JOIN filmes ON purchases.filme_id = filmes.id WHERE purchases.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$purchases = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Compras</title>
    <link rel="stylesheet" href="purchases.css">
</head>
<body>
    <div class="container">
        <h1>Minhas Compras</h1>
        <?php if (empty($purchases)): ?>
            <p>Você ainda não fez nenhuma compra.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Filme</th>
                        <th>Quantidade</th>
                        <th>Data da Compra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($purchase['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($purchase['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($purchase['purchase_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
