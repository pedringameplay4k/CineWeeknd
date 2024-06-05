<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: paglog.php');
    exit();
}

$filme_id = $_GET['filme_id'] ?? null;

if (!$filme_id) {
    header('Location: index.php');
    exit();
}

// Conectar ao banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CineWeeknd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obter o filme selecionado
$stmt = $conn->prepare("SELECT titulo, preco FROM filmes WHERE id = ?");
$stmt->bind_param("i", $filme_id);
$stmt->execute();
$stmt->bind_result($filme_titulo, $filme_preco);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$filme_titulo) {
    header('Location: index.php');
    exit();
}

$user_email = $_SESSION['user_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Compra</title>
    <link rel="stylesheet" href="purchase.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function togglePaymentFields() {
            var paymentMethod = document.getElementById("payment_method").value;
            var creditCardFields = document.getElementById("credit_card_fields");
            var pixFields = document.getElementById("pix_fields");
            var boletoFields = document.getElementById("boleto_fields");

            creditCardFields.classList.add("hidden");
            pixFields.classList.add("hidden");
            boletoFields.classList.add("hidden");

            if (paymentMethod === "credit_card") {
                creditCardFields.classList.remove("hidden");
            } else if (paymentMethod === "pix") {
                pixFields.classList.remove("hidden");
            } else if (paymentMethod === "boleto") {
                boletoFields.classList.remove("hidden");
            }
        }

        $(document).ready(function() {
            $("#cep").blur(function() {
                var cep = $(this).val().replace(/\D/g, '');

                if (cep != "") {
                    var validacep = /^[0-9]{8}$/;

                    if(validacep.test(cep)) {
                        $("#rua").val("...");
                        $("#bairro").val("...");
                        $("#cidade").val("...");
                        $("#uf").val("...");

                        $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                            if (!("erro" in dados)) {
                                $("#rua").val(dados.logradouro);
                                $("#bairro").val(dados.bairro);
                                $("#cidade").val(dados.localidade);
                                $("#uf").val(dados.uf);
                            } else {
                                alert("CEP não encontrado.");
                            }
                        });
                    } else {
                        alert("Formato de CEP inválido.");
                    }
                }
            });
        });
    </script>
</head>
<body>
    <div class="purchase-container">
        <h2>Compra de Ingressos</h2>
        <form action="confirm_purchase.php" method="POST">
            <div class="form-group">
                <label for="filme">Filme</label>
                <input type="text" id="filme" name="filme" value="<?php echo htmlspecialchars($filme_titulo); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="text" id="preco" name="preco" value="<?php echo 'R$ ' . number_format($filme_preco, 2, ',', '.'); ?>" readonly>
            </div>
            <div class="form-group address-group">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" required>
                <label for="rua">Rua</label>
                <input type="text" id="rua" name="rua" required>
                <label for="bairro">Bairro</label>
                <input type="text" id="bairro" name="bairro" required>
                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade" required>
                <label for="uf">UF</label>
                <input type="text" id="uf" name="uf" required>
            </div>
            <div class="form-group">
                <label for="payment_method">Método de Pagamento</label>
                <select id="payment_method" name="payment_method" onchange="togglePaymentFields()" required>
                    <option value="credit_card">Cartão de Crédito</option>
                    <option value="debit_card">Cartão de Débito</option>
                    <option value="pix">PIX</option>
                    <option value="boleto">Boleto</option>
                </select>
            </div>
            <div id="credit_card_fields" class="hidden">
                <div class="form-group">
                    <label for="credit_card_number">Número do Cartão</label>
                    <input type="text" id="credit_card_number" name="credit_card_number">
                </div>
                <div class="form-group">
                    <label for="credit_card_name">Nome no Cartão</label>
                    <input type="text" id="credit_card_name" name="credit_card_name">
                </div>
                <div class="form-group">
                    <label for="credit_card_expiry">Data de Validade</label>
                    <input type="text" id="credit_card_expiry" name="credit_card_expiry">
                </div>
                <div class="form-group">
                    <label for="credit_card_cvv">CVV</label>
                    <input type="text" id="credit_card_cvv" name="credit_card_cvv">
                </div>
            </div>
            <div id="pix_fields" class="hidden">
                <div class="form-group">
                    <label for="pix_key">Chave PIX</label>
                    <input type="text" id="pix_key" name="pix_key" readonly value="00020126580014BR.GOV.BCB.PIX013618c8656d-d12b-4ac0-8b1c-27c52862679f5204000053039865802BR5925Pedro Henrique Santos de 6009SAO PAULO62140510YN8Ffo9NtH630429C4">
                </div>
                <div class="form-group">
                    <h3>QR Code para PIX</h3>
                    <img src="assets/images/qrcode_pix.jpg" alt="QR Code PIX">
                </div>
            </div>
            <div id="boleto_fields" class="hidden">
                <p>O boleto será gerado após a confirmação da compra.</p>
            </div>
            <div class="form-group">
                <label for="confirm_email">Confirmação de Email</label>
                <input type="email" id="confirm_email" name="confirm_email" value="<?php echo htmlspecialchars($user_email); ?>" required>
            </div>
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" required>
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" required>
            </div>
            <div class="form-group">
                <button type="submit">Confirmar Compra</button>
            </div>
        </form>
    </div>
</body>
</html>
