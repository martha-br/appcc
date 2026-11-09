<?php
require __DIR__ . '/../../config/db.php';

$errores   = [];
$nombre    = '';
$ubicacion = '';
$temp_min  = '';
$temp_max  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $temp_min  = trim($_POST['temp_min'] ?? '');
    $temp_max  = trim($_POST['temp_max'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    } elseif (mb_strlen($nombre) > 100) {
        $errores[] = 'El nombre no puede superar los 100 caracteres.';
    }

    if (mb_strlen($ubicacion) > 100) {
        $errores[] = 'La ubicación no puede superar los 100 caracteres.';
    }

    if (!is_numeric($temp_min)) {
        $errores[] = 'La temperatura mínima debe ser un número.';
    }
    if (!is_numeric($temp_max)) {
        $errores[] = 'La temperatura máxima debe ser un número.';
    }

    if (empty($errores) && (float)$temp_min >= (float)$temp_max) {
        $errores[] = 'La temperatura mínima debe ser menor que la máxima.';
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare(
            'INSERT INTO equipos (nombre, ubicacion, temp_min, temp_max)
             VALUES (:nombre, :ubicacion, :temp_min, :temp_max)'
        );
        $stmt->execute([
            ':nombre'    => $nombre,
            ':ubicacion' => $ubicacion !== '' ? $ubicacion : null,
            ':temp_min'  => $temp_min,
            ':temp_max'  => $temp_max,
        ]);

        header('Location: index.php');
        exit;
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<h1 class="h3 mb-3">Nuevo equipo</h1>

<?php if ($errores): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" novalidate>
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre"
            value="<?= htmlspecialchars($nombre) ?>" required>
    </div>

    <div class="mb-3">
        <label for="ubicacion" class="form-label">Ubicación</label>
        <input type="text" class="form-control" id="ubicacion" name="ubicacion"
            value="<?= htmlspecialchars($ubicacion) ?>">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="temp_min" class="form-label">Temperatura mínima (°C)</label>
            <input type="number" step="0.01" class="form-control" id="temp_min" name="temp_min"
                value="<?= htmlspecialchars($temp_min) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="temp_max" class="form-label">Temperatura máxima (°C)</label>
            <input type="number" step="0.01" class="form-control" id="temp_max" name="temp_max"
                value="<?= htmlspecialchars($temp_max) ?>" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php require __DIR__ . '/../../includes/footer.php'; ?>