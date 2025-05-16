<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: ../inicio/index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_encuesta = intval($_GET['id']);

// obtener título de la encuesta
$sql_titulo = "SELECT titulo FROM encuestas WHERE id_encuesta = ?";
$stmt = $conn->prepare($sql_titulo);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();
$res = $stmt->get_result();
$titulo = $res->fetch_assoc()['titulo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>
    <link rel="stylesheet" href="estadisticas_encuesta.css">
</head>
<body>

<header>
    <div class="nav-izquierda">
        <a href="../inicio/index.php" class="logo">Encuestas</a>
    </div>
    <div class="nav-derecha">
        <?php if (isset($_SESSION['id_usuario'])): ?>
            <span class="usuario">👤 <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <a class="cerrar" href="../logout/cerrar_sesion.php">Cerrar sesión</a>
        <?php endif; ?>
    </div>
</header>

<h2>Estadísticas de: <?= htmlspecialchars($titulo) ?></h2>

<?php
$sql_preguntas = "SELECT id_pregunta, texto FROM preguntas WHERE id_encuesta = ?";
$stmt = $conn->prepare($sql_preguntas);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();
$preguntas = $stmt->get_result();

while ($pregunta = $preguntas->fetch_assoc()):
    $id_pregunta = $pregunta['id_pregunta'];
    $texto_pregunta = $pregunta['texto'];
?>
    <div class="pregunta">
        <h3><?= htmlspecialchars($texto_pregunta) ?></h3>
        <?php
        $sql_respuestas = "SELECT r.id_respuesta, r.texto,
            COUNT(ru.id_respuesta) AS total
            FROM respuestas r
            LEFT JOIN respuestas_usuario ru ON ru.id_respuesta = r.id_respuesta
            WHERE r.id_pregunta = ?
            GROUP BY r.id_respuesta";
        $stmt2 = $conn->prepare($sql_respuestas);
        $stmt2->bind_param("i", $id_pregunta);
        $stmt2->execute();
        $respuestas = $stmt2->get_result();

        // respuesta del usuario
        $sql_mi_resp = "SELECT r.id_respuesta FROM respuestas_usuario ru
                        JOIN respuestas r ON ru.id_respuesta = r.id_respuesta
                        WHERE ru.id_usuario = ? AND r.id_pregunta = ?";
        $stmt3 = $conn->prepare($sql_mi_resp);
        $stmt3->bind_param("ii", $id_usuario, $id_pregunta);
        $stmt3->execute();
        $mi_resp_result = $stmt3->get_result();
        $mi_respuesta_id = $mi_resp_result->num_rows > 0 ? $mi_resp_result->fetch_assoc()['id_respuesta'] : null;

        $total = 0;
        $datos = [];
        while ($r = $respuestas->fetch_assoc()) {
            $total += $r['total'];
            $datos[] = $r;
        }

        foreach ($datos as $r):
            $porcentaje = $total > 0 ? round(($r['total'] / $total) * 100, 1) : 0;
            $es_mia = ($r['id_respuesta'] == $mi_respuesta_id);
        ?>
            <p class="respuesta <?= $es_mia ? 'usuario' : '' ?>">
                <?= htmlspecialchars($r['texto']) ?>:
                <strong><?= $r['total'] ?> votos (<?= $porcentaje ?>%)</strong>
                <?= $es_mia ? '← tu respuesta' : '' ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endwhile; ?>

<div class="botones-volver">
    <a class="volver" href="ver_estadisticas.php">← Volver a mis estadísticas</a>
    <a class="volver" href="../inicio/index.php">← Volver al inicio</a>
</div>
</body>
</html>
