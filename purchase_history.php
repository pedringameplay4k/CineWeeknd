<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: paglog.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CineWeeknd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "
    SELECT 
        IFNULL(filmes.titulo, combos.nome) AS item,
        IFNULL(filmes.preco, combos.preco) AS preco,
        purchases.quantity,
        purchases.purchase_date 
    FROM purchases 
    LEFT JOIN filmes ON purchases.filme_id = filmes.id 
    LEFT JOIN combos ON purchases.combo_id = combos.id 
    WHERE purchases.user_id = ?
";
$stmt = $conn->prepare($query);
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
    <title>Histórico de Compras</title>
    <link rel="stylesheet" href="purchases.css">
</head>
<body>
    <div class="container">
        <h1>Histórico de Compras</h1>
        <?php if (empty($purchases)): ?>
            <p>Você ainda não fez nenhuma compra.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Data da Compra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($purchase['item']); ?></td>
                            <td><?php echo htmlspecialchars('R$ ' . number_format($purchase['preco'], 2, ',', '.')); ?></td>
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
