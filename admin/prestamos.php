<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
iniciarSesion();
requerirAdmin();

$pdo    = conectar();
$filtro = $_GET['estado'] ?? 'activos';
$extra  = $filtro === 'todos' ? '' : 'WHERE p.fecha_devolucion IS NULL';

$prestamos = $pdo->query(
    "SELECT p.*, s.nombre AS socio, s.apellido, s.nro_socio, l.titulo, l.autor, l.isbn
     FROM prestamos p
     JOIN socios s ON s.id = p.socio_id
     JOIN libros l ON l.id = p.libro_id
     $extra
     ORDER BY p.fecha_devolucion IS NULL DESC, p.fecha_retiro DESC"
)->fetchAll();

$activos = (int)$pdo->query('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL')->fetchColumn();
$total   = (int)$pdo->query('SELECT COUNT(*) FROM prestamos')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamos – Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="logo"><a href="../index.php" style="color:inherit;display:flex;align-items:center;gap:10px"><span>📚</span> Biblioteca Virtual</a></div>
        <nav class="site-nav">
            <a href="../catalogo.php">Catálogo</a>
            <a href="cargar_libro.php" class="nav-admin">➕ Cargar libro</a>
            <a href="socios.php"       class="nav-admin">👥 Socios</a>
            <a href="../logout.php" class="nav-salir">Salir</a>
        </nav>
    </div>
</header>

<main>

<h1 class="page-titulo">Gestión de préstamos</h1>
<p class="page-subtitulo">Control de todos los préstamos realizados</p>

<div class="stats-row">
    <div class="stat-card">
        <span class="stat-numero"><?= $activos ?></span>
        <span class="stat-label">Activos ahora</span>
    </div>
    <div class="stat-card">
        <span class="stat-numero"><?= $total ?></span>
        <span class="stat-label">Total histórico</span>
    </div>
</div>

<div class="filtros">
    <a href="?estado=activos" class="<?= $filtro!=='todos'?'activo':'' ?>">Solo activos</a>
    <a href="?estado=todos"   class="<?= $filtro==='todos' ?'activo':'' ?>">Todos</a>
</div>

<?php if (empty($prestamos)): ?>
    <div class="vacio"><span>📭</span><p>No hay préstamos para mostrar.</p></div>
<?php else: ?>
<div class="tabla-wrap">
<table class="tabla">
    <thead>
        <tr><th>#</th><th>Socio</th><th>N° Socio</th><th>Libro</th><th>Autor</th><th>ISBN</th><th>Retiro</th><th>Devolución</th><th>Estado</th></tr>
    </thead>
    <tbody>
    <?php foreach ($prestamos as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['socio']) ?> <?= htmlspecialchars($p['apellido']) ?></td>
        <td>#<?= $p['nro_socio'] ?></td>
        <td><strong><?= htmlspecialchars($p['titulo']) ?></strong></td>
        <td><?= htmlspecialchars($p['autor']) ?></td>
        <td><?= htmlspecialchars($p['isbn']) ?></td>
        <td><?= $p['fecha_retiro'] ?></td>
        <td><?= $p['fecha_devolucion'] ?? '—' ?></td>
        <td>
            <?= $p['fecha_devolucion']
                ? '<span class="badge badge-verde">Devuelto</span>'
                : '<span class="badge badge-naranja">En préstamo</span>' ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

</main>

<footer class="site-footer">
    <p>Biblioteca Virtual &copy; <?= date('Y') ?></p>
</footer>

</body>
</html>
