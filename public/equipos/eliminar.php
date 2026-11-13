<?php
require __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM equipos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    header('Location: index.php?eliminado=1');
    exit;
} catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1451) {
        $mensaje = 'No se puede eliminar este equipo porque tiene registros de temperatura asociados.';
    } else {
        $mensaje = 'No se ha podido eliminar el equipo.';
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<h1 class="h3 mb-3">Eliminar equipo</h1>

<div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>

<a href="index.php" class="btn btn-secondary">Volver al listado</a>

<?php require __DIR__ . '/../../includes/footer.php'; ?>