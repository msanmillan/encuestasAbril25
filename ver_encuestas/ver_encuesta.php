<?php
include '../conexion.php';

// comprobamos si llega el id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'id de encuesta no válido']);
    exit();
}

$id_encuesta = intval($_GET['id']);

// sacamos título y autor
$sql = "SELECT e.titulo, u.nombre AS autor
        FROM encuestas e
        JOIN usuarios u ON e.creador_id = u.id_usuario
        WHERE e.id_encuesta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['error' => 'encuesta no encontrada']);
    exit();
}

$encuesta = $res->fetch_assoc();
$datos = [
    'titulo' => $encuesta['titulo'],
    'autor' => $encuesta['autor'],
    'preguntas' => []
];

// sacamos preguntas
$sql = "SELECT id_pregunta, texto FROM preguntas WHERE id_encuesta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();
$res_preg = $stmt->get_result();

while ($preg = $res_preg->fetch_assoc()) {
    $id_pregunta = $preg['id_pregunta'];

    // sacamos respuestas con sus ID
    $sql_resp = "SELECT id_respuesta, texto FROM respuestas WHERE id_pregunta = ?";
    $stmt_resp = $conn->prepare($sql_resp);
    $stmt_resp->bind_param("i", $id_pregunta);
    $stmt_resp->execute();
    $res_resp = $stmt_resp->get_result();

    $respuestas = [];
    while ($r = $res_resp->fetch_assoc()) {
        $respuestas[] = [
            'id' => $r['id_respuesta'],
            'texto' => $r['texto']
        ];
    }

    $datos['preguntas'][] = [
        'texto' => $preg['texto'],
        'respuestas' => $respuestas
    ];
}

header('Content-Type: application/json');
echo json_encode($datos);
