<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$servername = "localhost";
$username = "root";
$password = ""; // Adicione a senha do seu banco de dados aqui
$dbname = "CineWeeknd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Buscar os dados da tabela "combos"
$sql = "SELECT id, nome, preco FROM combos";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combos de Pipoca</title>
    <link rel="stylesheet" href="combos.css"> <!-- Adicione seu arquivo CSS aqui -->
</head>
<body>
<section id="pricing" class="pricing_area pt-120 pb-130">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section_title text-center pb-25">
                    <h4 class="title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">Combos!</h4>
                    <p class="wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.4s">Dê uma olhada nos nossos combos de pipoca e aperitivos.</p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="col-lg-4 col-md-8 col-sm-10">';
                    echo '<div class="single_pricing text-center mt-30 wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">';
                    echo '<h4 class="pricing_title">' . $row["nome"] . '</h4>';
                    echo '<span class="price">R$' . $row["preco"] . '</span>';
                    echo '<h4 class="pricing_title">Opções:</h4>';
                    echo '<ul class="pricing_list">';
                    echo '<li>Pipoca Doce</li>';
                    echo '<li>Pipoca S/ Sal</li>';
                    echo '<li>Pipoca com Nutella (+R$5)</li>';
                    echo '<li>Coca-Cola 500Ml (+R$3)</li>';
                    echo '</ul>';
                    echo '<a href="#0" class="mian-btn">EU QUEROO!!</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p>Nenhum combo encontrado.</p>";
            }
            ?>
        </div>
    </div>
</section>
</body>
</html>

<?php
$conn->close();
?>
