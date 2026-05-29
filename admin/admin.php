<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
iniciarSesion();
requerirAdmin();

$pdo    = conectar();
$filtro = $_GET['estado'] ?? 'activos';
$extra  = $filtro === 'todos' ? '' : 'WHERE p.fecha_devolucion IS NULL';

$prestamos = $pdo->query(
    "SELECT p.*, s.nombre AS socio, s.nro_socio, l.titulo, l.autor, l.isbn
     FROM prestamos p
     JOIN socios s ON s.id = p.socio_id
     JOIN libros l ON l.id = p.libro_id
     $extra
     ORDER BY p.fecha_devolucion IS NULL DESC, p.fecha_retiro DESC"
)->fetchAll();

$activos = (int)$pdo->query('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Préstamos – Admin</title></head>
<body>
<h1>Gestión de préstamos</h1>
<nav>
    <a href="../index.php">← Catálogo</a> |
    <a href="cargar_libro.php">Cargar libro</a> |
    <a href="socios.php">Ver socios</a> |
    <a href="../logout.php">Cerrar sesión</a>
</nav>
<hr>
<p>Préstamos activos: <strong><?= $activos ?></strong></p>
<p>
    <a href="?estado=activos">Solo activos</a> |
    <a href="?estado=todos">Todos</a>
</p>

<?php if (empty($prestamos)): ?>
    <p>No hay préstamos para mostrar.</p>
<?php else: ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>#</th><th>Socio</th><th>N° Socio</th><th>Libro</th>
            <th>Autor</th><th>ISBN</th><th>Retiro</th><th>Devolución</th><th>Estado</th>
        </tr>
        <?php foreach ($prestamos as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['socio']) ?></td>
            <td>#<?= $p['nro_socio'] ?></td>
            <td><?= htmlspecialchars($p['titulo']) ?></td>
            <td><?= htmlspecialchars($p['autor']) ?></td>
            <td><?= htmlspecialchars($p['isbn']) ?></td>
            <td><?= $p['fecha_retiro'] ?></td>
            <td><?= $p['fecha_devolucion'] ?? '—' ?></td>
            <td><?= $p['fecha_devolucion'] ? 'Devuelto' : 'En préstamo' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
</body>
</html>