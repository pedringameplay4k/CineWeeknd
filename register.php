
        <?php
        require 'User.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $name = $_POST['name'] ?? '';

            if (!empty($email) && !empty($password) && !empty($name)) {
                $user = new User();
                if ($user->saveUser($name, $email, $password)) {
                    echo "<script>alert('Formulário enviado com sucesso. Clique OK para fazer login.'); window.location.href='paglog.php';</script>";
                } else {
                    echo "<script>alert('Erro ao enviar o formulário. Tente novamente.');</script>";
                }
            } else {
                echo "<script>alert('Preencha todos os campos obrigatórios.');</script>";
            }
        } else {
            echo "<script>alert('Método de requisição inválido.');</script>";
        }
        ?>
    </div>
</body>
</html>
