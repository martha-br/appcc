<?php
require __DIR__ . '/../../config/db.php';

$equipos = $pdo->query('SELECT id, nombre, temp_min, temp_max FROM equipos ORDER BY nombre')->fetchAll();

$errores       = [];
$equipo_id     = '';
$temperatura   = '';
$fecha_hora    = date('Y-m-d\TH:i');
$operario      = '';
$observaciones = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipo_id     = trim($_POST['equipo_id'] ?? '');
    $temperatura   = trim($_POST['temperatura'] ?? '');
    $fecha_hora    = trim($_POST['fecha_hora'] ?? '');
    $operario      = trim($_POST['operario'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    // 1. Equipo
    $equipo = null;
    $id = filter_var($equipo_id, FILTER_VALIDATE_INT);
    if (!$id) {
        $errores[] = 'Selecciona un equipo.';
    } else {
        $stmt = $pdo->prepare('SELECT id, temp_min, temp_max FROM equipos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $equipo = $stmt->fetch();
        if (!$equipo) {
            $errores[] = 'El equipo seleccionado no existe.';
        }
    }

    // 2. Temperatura
    if (!is_numeric($temperatura)) {
        $errores[] = 'La temperatura debe ser un número.';
    } elseif ((float)$temperatura < -999.99 || (float)$temperatura > 999.99) {
        $errores[] = 'La temperatura debe estar entre -999,99 y 999,99.';
    }

    // 3. Fecha y hora
    $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $fecha_hora);
    if (!$fecha) {
        $errores[] = 'La fecha y hora no son válidas.';
    }

    // 4. Operario y observaciones
    if ($operario === '') {
        $errores[] = 'El operario es obligatorio.';
    } elseif (mb_strlen($operario) > 100) {
        $errores[] = 'El operario no puede superar los 100 caracteres.';
    }
    if (mb_strlen($observaciones) > 400) {
        $errores[] = 'Las observaciones no pueden superar los 400 caracteres.';
    }

    // 5. ¿Fuera de rango?
    $fuera_de_rango = false;
    if ($equipo && is_numeric($temperatura)) {
        $t = (float)$temperatura;
        $fuera_de_rango = $t < (float)$equipo['temp_min'] || $t > (float)$equipo['temp_max'];

        if ($fuera_de_rango && $observaciones === '') {
            $errores[] = 'Temperatura fuera de rango: indica en observaciones la causa o la acción correctora.';
        }
    }

    // 6. Guardar
    if (empty($errores)) {
        $stmt = $pdo->prepare(
            'INSERT INTO registros_temperatura (equipo_id, temperatura, fecha_hora, operario, observaciones)
             VALUES (:equipo_id, :temperatura, :fecha_hora, :operario, :observaciones)'
        );
        $stmt->execute([
            ':equipo_id'     => $equipo['id'],
            ':temperatura'   => $temperatura,
            ':fecha_hora'    => $fecha->format('Y-m-d H:i:s'),
            ':operario'      => $operario,
            ':observaciones' => $observaciones !== '' ? $observaciones : null,
        ]);

        header('Location: index.php?estado=' . ($fuera_de_rango ? 'alerta' : 'ok'));
        exit;
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<h1 class="h3 mb-3">Nuevo registro de temperatura</h1>

<?php if ($errores): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!$equipos): ?>
    <div class="alert alert-info">
        Antes de registrar temperaturas necesitas crear al menos un equipo.
        <a href="/appcc/public/equipos/crear.php">Crear equipo</a>
    </div>
<?php else: ?>
    <form method="post" novalidate>
        <div class="mb-3">
            <label for="equipo_id" class="form-label">Equipo</label>
            <select class="form-select" id="equipo_id" name="equipo_id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($equipos as $e): ?>
                    <option value="<?= (int)$e['id'] ?>"
                        <?= (string)$e['id'] === (string)$equipo_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nombre']) ?>
                        (<?= $e['temp_min'] ?> a <?= $e['temp_max'] ?> °C)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="temperatura" class="form-label">Temperatura (°C)</label>
                <input type="number" step="0.01" class="form-control" id="temperatura" name="temperatura"
                    value="<?= htmlspecialchars($temperatura) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="fecha_hora" class="form-label">Fecha y hora</label>
                <input type="datetime-local" class="form-control" id="fecha_hora" name="fecha_hora"
                    value="<?= htmlspecialchars($fecha_hora) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="operario" class="form-label">Operario</label>
            <input type="text" class="form-control" id="operario" name="operario"
                value="<?= htmlspecialchars($operario) ?>" required>
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($observaciones) ?></textarea>
            <div class="form-text">Obligatorio si la temperatura está fuera de rango.</div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar registro</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>