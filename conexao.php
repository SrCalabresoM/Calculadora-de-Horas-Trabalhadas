<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "sistema_login";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>
