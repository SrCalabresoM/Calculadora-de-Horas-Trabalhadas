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
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['erro'=>'id inválido']);
    exit;
}

$usuario = $_SESSION['usuario'];
$stmtCheck = $conexao->prepare("SELECT id FROM registros WHERE id = ? AND usuario_email = ?");
$stmtCheck->bind_param("is", $id, $usuario);
$stmtCheck->execute();
$res = $stmtCheck->get_result();
if ($res->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['erro'=>'registro não encontrado ou sem permissão']);
    exit;
}

$stmt = $conexao->prepare("DELETE FROM registros WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro'=>'prepare falhou','detail'=>$conexao->error]);
    exit;
}
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(['sucesso'=>true]);
} else {
    http_response_code(500);
    echo json_encode(['erro'=>'falha ao excluir','detail'=>$stmt->error]);
}
