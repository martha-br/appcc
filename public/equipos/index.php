<?php
require __DIR__ . '/../../config/db.php';

$stmt = $pdo->query('SELECT id, nombre, ubicacion, temp_min, temp_max FROM equipos ORDER BY nombre');
$equipos = $stmt->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>

<h1 class="h3 mb-3">Equipos</h1>

<?php if (!$equipos): ?>
    <div class="alert alert-info">Todavía no hay equipos registrados.</div>
<?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Ubicación</th>
                <th>Temp. mínima (ºC)</th>
                <th>Temp. máxima (ºC)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($equipos as $equipo): ?>
                <tr>
                    <td><?=  htmlspecialchars($equipo['nombre']) ?></td>
                    <td><?=  htmlspecialchars($equipo['ubicacion']) ?></td>
                    <td><?= $equipo['temp_min'] ?></td>
                    <td><?= $equipo['temp_max'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php require __DIR__ . '/../../includes/footer.php'; ?>