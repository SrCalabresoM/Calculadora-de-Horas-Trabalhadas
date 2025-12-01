<?php
session_start();
include('conexao.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $cnpj = $_POST['cnpj'];
    $sql = "INSERT INTO usuarios (nome, email, senha, cnpj) VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senha, $cnpj);
    function consultarCNPJ($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj); // Remove caracteres não numéricos
        $url = "https://www.receitaws.com.br/v1/cnpj/$cnpj"; // Endpoint da API
    
        // Fazendo a requisição para a API
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        
        if (isset($data['status']) && $data['status'] == 'OK') {
            return true;
        } else {
            return false;
        }
    }
    
    if (consultarCNPJ($cnpj)) {
        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso! <button onclick=\"window.location.href = 'index.php'\">Sair</button>";
        } else {
            echo "Erro ao cadastrar: " . $stmt->error;
        }
    } else {
        echo "Erro ao cadastrar: CNPJ inválido";
    }
    
    if ($stmt->execute() && consultarCNPJ($cnpj)) {
    // Salvar dados na sessão
    $_SESSION['nome'] = $nome;
    $_SESSION['email'] = $email;

    // Salvar dados em cookies por 30 dias
    setcookie("nome", $nome, time() + (86400 * 30), "/");
    setcookie("email", $email, time() + (86400 * 30), "/");

    echo "Cadastro realizado com sucesso! <button onclick=\"window.location.href = 'index.php'\">Sair</button>";
}
}
?>

<style>
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        border: 2px solid #ccc;
        padding: 8px;
        border-radius: 4px;
    }

    input[type="submit"] {
        margin-top: 10px;
        padding: 8px 15px;
    }
</style>


<form method="POST">
    Nome: <input type="text" name="nome" required value="<?php echo isset($_COOKIE['nome']) ? $_COOKIE['nome'] : ''; ?>"><br>
    Email: <input type="email" name="email" required value="<?php echo isset($_COOKIE['email']) ? $_COOKIE['email'] : ''; ?>"><br>
    Senha: <input type="password" name="senha" required><br>
    CNPJ: <input type="text" name="cnpj" required><br>
    <input type="submit" value="Cadastrar">
</form>
