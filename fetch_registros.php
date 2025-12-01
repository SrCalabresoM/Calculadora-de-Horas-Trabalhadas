<?php
session_start();
include 'conexao.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['erro'=>'não autorizado']);
    exit;
}

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = max(1, intval($_GET['perPage'] ?? 5));
$offset = ($page - 1) * $perPage;

$usuario = $_SESSION['usuario'];

$sql = "SELECT id, funcionario_nome, horas_trabalhadas, data_registro
        FROM registros
        WHERE usuario_email = ?
        AND funcionario_nome LIKE ?
        ORDER BY data_registro DESC
        LIMIT ? OFFSET ?";

$stmt = $conexao->prepare($sql);
$like = "%$search%";
$stmt->bind_param("ssii", $usuario, $like, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$registros = [];
while($row = $result->fetch_assoc()){
    $registros[] = $row;
}


$stmtTotal = $conexao->prepare("SELECT COUNT(*) as total FROM registros WHERE usuario_email = ? AND funcionario_nome LIKE ?");
$stmtTotal->bind_param("ss", $usuario, $like);
$stmtTotal->execute();
$resTotal = $stmtTotal->get_result()->fetch_assoc();
$total = intval($resTotal['total']);

echo json_encode([
    'total' => $total,
    'page' => $page,
    'perPage' => $perPage,
    'registros' => $registros
]);
