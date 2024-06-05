<?php
class User {
    private $conn;

    public function __construct() {
        $this->connectDb();
    }

    private function connectDb() {
        $servername = "localhost";
        $username = "root";
        $password = ""; // Adicione a senha do seu banco de dados aqui
        $dbname = "CineWeeknd";

        // Cria a conexão
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // Verifica a conexão
        if ($this->conn->connect_error) {
            die("Conexão falhou: " . $this->conn->connect_error);
        }
    }

    public function saveUser($name, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO users_register (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $passwordHash);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            return false;
        }
    }

    public function authenticate($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, name, password FROM users_register WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }

        return false;
    }

    public function getUserById($userId) {
        $stmt = $this->conn->prepare("SELECT id, name, email FROM users_register WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateUser($userId, $name, $email, $password) {
        if (empty($password)) {
            $stmt = $this->conn->prepare("UPDATE users_register SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $userId);
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users_register SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $passwordHash, $userId);
        }

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser($userId) {
        // Iniciar transação
        $this->conn->begin_transaction();

        try {
            // Excluir compras do usuário
            $stmt = $this->conn->prepare("DELETE FROM purchases WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            // Excluir logins do usuário
            $stmt = $this->conn->prepare("DELETE FROM users_login WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            // Excluir usuário
            $stmt = $this->conn->prepare("DELETE FROM users_register WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            // Comitar transação
            $this->conn->commit();
            return true;
        } catch (mysqli_sql_exception $exception) {
            // Reverter transação em caso de erro
            $this->conn->rollback();
            throw $exception;
        }
    }
}
?>
