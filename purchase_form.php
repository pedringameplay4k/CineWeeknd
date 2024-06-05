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

$combo_id = isset($_GET['combo_id']) ? (int)$_GET['combo_id'] : 0;

if ($combo_id > 0) {
    $query = "SELECT nome, preco FROM combos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $combo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $combo = $result->fetch_assoc();
    $stmt->close();

    if (!$combo) {
        die("Combo não encontrado.");
    }
} else {
    die("ID de combo inválido.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($quantity > 0) {
        $query = "INSERT INTO purchases (user_id, combo_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Erro na preparação da query: " . $conn->error);
        }
        $stmt->bind_param("iii", $user_id, $combo_id, $quantity);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: purchase_history.php');
            exit();
        } else {
            die("Erro na execução da query: " . $stmt->error);
        }
    } else {
        echo "Quantidade inválida.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Combo</title>
    <link rel="stylesheet" href="form.css">
</head>
<body>
    <div class="container">
        <h1>Comprar Combo: <?php echo htmlspecialchars($combo['nome']); ?></h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="quantity">Quantidade:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" required>
            </div>
            <div class="form-group">
                <button type="submit">Comprar</button>
            </div>
        </form>
        <p>Preço: R$<?php echo htmlspecialchars($combo['preco']); ?></p>
    </div>
</body>
</html>
