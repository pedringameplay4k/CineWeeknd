<?php
session_start();
require 'User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $user = new User();
        $authenticatedUser = $user->authenticate($email, $password);

        if ($authenticatedUser) {
            $_SESSION['user_id'] = $authenticatedUser['id'];
            $_SESSION['user_name'] = $authenticatedUser['name'];

            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Email ou senha incorretos.'); window.location.href='paglog.php';</script>";
        }
    } else {
        echo "<script>alert('Preencha todos os campos obrigatórios.'); window.location.href='paglog.php';</script>";
    }
}
?>