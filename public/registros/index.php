<?php
require __DIR__ . '/../../config/db.php';

$stmt = $pdo->query(
    'SELECT r.id, r.temperatura, r.fecha_hora, r.operario, r.observaciones,
            e.nombre AS equipo, e.temp_min, e.temp_max
     FROM registros_temperatura r
     INNER JOIN equipos e ON e.id = r.equipo_id
     ORDER BY r.fecha_hora DESC
     LIMIT 50'
);
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

<?php if (!$registros): ?>
    <div class="alert alert-info">Todavía no hay registros.</div>
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