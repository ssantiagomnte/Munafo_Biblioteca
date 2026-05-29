<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
iniciarSesion();
requerirAdmin();

$socios = conectar()->query(
    "SELECT s.*, COUNT(p.id) AS total_prestamos,
            SUM(p.fecha_devolucion IS NULL) AS activos
     FROM socios s
     LEFT JOIN prestamos p ON p.socio_id = s.id
     WHERE s.rol = 'socio'
     GROUP BY s.id
     ORDER BY s.nro_socio"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socios – Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="logo"><a href="../index.php" style="color:inherit;display:flex;align-items:center;gap:10px"><span>📚</span> Biblioteca Virtual</a></div>
        <nav class="site-nav">
            <a href="../catalogo.php">Catálogo</a>
            <a href="cargar_libro.php" class="nav-admin">➕ Cargar libro</a>
            <a href="prestamos.php"    class="nav-admin">📋 Préstamos</a>
            <a href="../logout.php" class="nav-salir">Salir</a>
        </nav>
    </div>
</header>

<main>

<h1 class="page-titulo">Socios registrados</h1>
<p class="page-subtitulo"><?= count($socios) ?> socio<?= count($socios)!=1?'s':'' ?> en el sistema</p>

<?php if (empty($socios)): ?>
    <div class="vacio"><span>👥</span><p>No hay socios registrados todavía.</p></div>
<?php else: ?>
<div class="tabla-wrap">
<table class="tabla">
    <thead>
        <tr><th>N° Socio</th><th>Nombre</th><th>Apellido</th><th>DNI</th><th>Email</th><th>Préstamos totales</th><th>Activos</th></tr>
    </thead>
    <tbody>
    <?php foreach ($socios as $s): ?>
    <tr>
        <td>#<?= $s['nro_socio'] ?></td>
        <td><?= htmlspecialchars($s['nombre']) ?></td>
        <td><?= htmlspecialchars($s['apellido']) ?></td>
        <td><?= htmlspecialchars($s['dni']) ?></td>
        <td><?= htmlspecialchars($s['email']) ?></td>
        <td><?= $s['total_prestamos'] ?></td>
        <td>
            <?php if ($s['activos'] > 0): ?>
                <span class="badge badge-naranja"><?= $s['activos'] ?> activo<?= $s['activos']>1?'s':'' ?></span>
            <?php else: ?>
                <span class="badge badge-verde">Ninguno</span>
            <?php endif; ?>
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
