<?php
require __DIR__ . '/../../config/db.php';

function fecha_valida(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

// Lista de equipos para el desplegable de filtro
$equipos = $pdo->query('SELECT id, nombre FROM equipos ORDER BY nombre')->fetchAll();

// Leer filtros de la dirección
$equipo_id    = filter_input(INPUT_GET, 'equipo_id', FILTER_VALIDATE_INT) ?: null;
$desde        = trim($_GET['desde'] ?? '');
$hasta        = trim($_GET['hasta'] ?? '');
$solo_alertas = isset($_GET['solo_alertas']);

if ($desde !== '' && !fecha_valida($desde)) {
    $desde = '';
}
if ($hasta !== '' && !fecha_valida($hasta)) {
    $hasta = '';
}

// Construir las condiciones
$condiciones = [];
$params      = [];

if ($equipo_id) {
    $condiciones[]          = 'r.equipo_id = :equipo_id';
    $params[':equipo_id']   = $equipo_id;
}
if ($desde !== '') {
    $condiciones[]      = 'r.fecha_hora >= :desde';
    $params[':desde']   = $desde . ' 00:00:00';
}
if ($hasta !== '') {
    $condiciones[]      = 'r.fecha_hora <= :hasta';
    $params[':hasta']   = $hasta . ' 23:59:59';
}
if ($solo_alertas) {
    $condiciones[] = '(r.temperatura < e.temp_min OR r.temperatura > e.temp_max)';
}

$where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

$sql = "SELECT r.id, r.temperatura, r.fecha_hora, r.operario, r.observaciones,
               e.nombre AS equipo, e.temp_min, e.temp_max
        FROM registros_temperatura r
        INNER JOIN equipos e ON e.id = r.equipo_id
        $where
        ORDER BY r.fecha_hora DESC
        LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();

$estado = $_GET['estado'] ?? '';

require __DIR__ . '/../../includes/header.php';
?>

<h1 class="h3 mb-3">Registros de temperatura</h1>

<?php if ($estado === 'ok'): ?>
    <div class="alert alert-success">Registro guardado correctamente.</div>
<?php elseif ($estado === 'alerta'): ?>
    <div class="alert alert-warning">
        Registro guardado, pero la temperatura está <strong>fuera de rango</strong>.
    </div>
<?php endif; ?>

<a href="crear.php" class="btn btn-primary mb-3">Nuevo registro</a>

<form method="get" class="row g-2 align-items-end mb-4">
    <div class="col-md-3">
        <label for="equipo_id" class="form-label">Equipo</label>
        <select class="form-select" id="equipo_id" name="equipo_id">
            <option value="">Todos</option>
            <?php foreach ($equipos as $e): ?>
                <option value="<?= (int)$e['id'] ?>" <?= $equipo_id === (int)$e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2">
        <label for="desde" class="form-label">Desde</label>
        <input type="date" class="form-control" id="desde" name="desde" value="<?= htmlspecialchars($desde) ?>">
    </div>

    <div class="col-md-2">
        <label for="hasta" class="form-label">Hasta</label>
        <input type="date" class="form-control" id="hasta" name="hasta" value="<?= htmlspecialchars($hasta) ?>">
    </div>

    <div class="col-md-2">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="solo_alertas" name="solo_alertas" value="1"
                <?= $solo_alertas ? 'checked' : '' ?>>
            <label class="form-check-label" for="solo_alertas">Solo fuera de rango</label>
        </div>
    </div>

    <div class="col-md-3">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

<p class="text-muted"><?= count($registros) ?> registro(s) mostrado(s)</p>

<?php if (!$registros): ?>
    <div class="alert alert-info">No hay registros que coincidan.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Equipo</th>
                    <th>Temperatura (°C)</th>
                    <th>Estado</th>
                    <th>Operario</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                    <?php
                    $fuera = (float)$r['temperatura'] < (float)$r['temp_min']
                        || (float)$r['temperatura'] > (float)$r['temp_max'];
                    ?>
                    <tr class="<?= $fuera ? 'table-danger' : '' ?>">
                        <td><?= date('d/m/Y H:i', strtotime($r['fecha_hora'])) ?></td>
                        <td><?= htmlspecialchars($r['equipo']) ?></td>
                        <td><?= $r['temperatura'] ?></td>
                        <td>
                            <?php if ($fuera): ?>
                                <span class="badge text-bg-danger">Fuera de rango</span>
                            <?php else: ?>
                                <span class="badge text-bg-success">OK</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['operario']) ?></td>
                        <td><?= htmlspecialchars($r['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>