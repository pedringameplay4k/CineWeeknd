<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: paglog.php");
    exit();
}

require 'User.php';

$user = new User();
$feedback = '';

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $userId = $_SESSION['user_id'];

    if (!empty($name) && !empty($email)) {
        if ($user->updateUser($userId, $name, $email, $password)) {
            $feedback = "Informações atualizadas com sucesso!";
            $_SESSION['user_name'] = $name; // Atualiza o nome na sessão
        } else {
            $feedback = "Erro ao atualizar as informações.";
        }
    } else {
        $feedback = "Preencha todos os campos obrigatórios.";
    }
}

// Processar formulário de deleção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $userId = $_SESSION['user_id'];
    if ($user->deleteUser($userId)) {
        session_destroy();
        header("Location: paglog.php");
        exit();
    } else {
        $feedback = "Erro ao deletar a conta.";
    }
}

$userData = $user->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <link rel="stylesheet" type="text/css" href="styless.css">
</head>
<body>
    <div class="container">
        <h1>Perfil</h1>
        <form method="POST" action="profile.php" class="profile-form">
            <div class="form-group">
                <label for="name">Nome:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Senha (deixe em branco para manter a senha atual):</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-buttons">
                <button type="submit" name="update" class="btn btn-update">Atualizar Informações</button>
                <button type="submit" name="delete" class="btn btn-delete" onclick="return confirm('Você tem certeza que deseja deletar sua conta?');">Deletar Conta</button>
            </div>
        </form>
        <p class="feedback"><?php echo $feedback; ?></p>
        <a href="index.php" class="btn btn-back">Voltar para a página inicial</a>
    </div>
</body>
</html>
