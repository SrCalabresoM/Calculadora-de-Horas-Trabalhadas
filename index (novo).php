<?php
session_start();
include('conexao.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cnpj = $_POST['cnpj'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE cnpj = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $cnpj);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = $usuario['nome'];
            header("Location: home.php");
        } else {
            echo "Senha incorreta.";
        }
    } else {
        echo "Usuário não encontrado.";
    }
}
?>

<style>
label {
    font-weight: bold;
    border: 1px solid #ccc;
    padding: 3px 6px;
    display: inline-block;
    margin-bottom: 4px;
}
</style>

<form method="POST">
    <label for="cnpj"> CNPJ: </label> <input type="text" name="cnpj" required><br>

    <label for="senha"> Senha: </label> <input type="password" name="senha" required><br>

    <input type="submit" value="Entrar">
</form>

<p><button onclick="window.location.href = 'cadastro.php'">Criar conta</button></p>
