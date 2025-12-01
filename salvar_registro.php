<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

include 'conexao.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['erro'=>'não autorizado']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['erro'=>'payload inválido','detail'=>$raw]);
    exit;
}

$nome = trim($data['nome'] ?? '');
$horas = trim($data['horas'] ?? '');
$usuario = $_SESSION['usuario'];

if ($nome === '' || $horas === '') {
    http_response_code(400);
    echo json_encode(['erro'=>'nome ou horas vazias']);
    exit;
}

$stmt = $conexao->prepare("INSERT INTO registros (usuario_email, funcionario_nome, horas_trabalhadas) VALUES (?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro'=>'prepare falhou','detail'=>$conexao->error]);
    exit;
}

$stmt->bind_param("sss", $usuario, $nome, $horas);

if ($stmt->execute()) {
    echo json_encode(['sucesso'=>true]);
} else {
    http_response_code(500);
    echo json_encode(['erro'=>'falha ao salvar','detail'=>$stmt->error]);
}
