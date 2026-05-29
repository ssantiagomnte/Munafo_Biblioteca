<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
iniciarSesion();
requerirLogin();

$u   = usuario();
$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prestamo_id'])) {
    $pid  = (int)$_POST['prestamo_id'];
    $stmt = $pdo->prepare('SELECT p.*,l.titulo FROM prestamos p JOIN libros l ON l.id=p.libro_id WHERE p.id=:pid AND p.socio_id=:uid AND p.fecha_devolucion IS NULL');
    $stmt->execute([':pid'=>$pid,':uid'=>$u['id']]);
    $p = $stmt->fetch();
    if ($p) {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE prestamos SET fecha_devolucion=CURDATE() WHERE id=:pid')->execute([':pid'=>$pid]);
        $pdo->prepare('UPDATE libros SET stock_disp=stock_disp+1 WHERE id=:lid')->execute([':lid'=>$p['libro_id']]);
        $pdo->commit();
        $_SESSION['msg'] = 'Devolviste "' . $p['titulo'] . '" correctamente.';
    }
    header('Location: mis_prestamos.php'); exit;
}

$stmt = $pdo->prepare(
    'SELECT p.*, l.titulo, l.autor, l.isbn
     FROM prestamos p JOIN libros l ON l.id=p.libro_id
     WHERE p.socio_id=:uid
     ORDER BY p.fecha_devolucion IS NULL DESC, p.fecha_retiro DESC'
);
$stmt->execute([':uid'=>$u['id']]);
$prestamos = $stmt->fetchAll();
$activos   = count(array_filter($prestamos, fn($p)=>!$p['fecha_devolucion']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis préstamos – Biblioteca Virtual</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="logo"><a href="index.php" style="color:inherit;display:flex;align-items:center;gap:10px"><span>📚</span> Biblioteca Virtual</a></div>
        <nav class="site-nav">
            <a href="index.php">Inicio</a>
            <a href="catalogo.php">Catálogo</a>
            <span class="nav-usuario">👤 <?= htmlspecialchars($u['nombre']) ?></span>
            <a href="logout.php" class="nav-salir">Salir</a>
        </nav>
    </div>
</header>

<main>

<h1 class="page-titulo">Mis préstamos</h1>
<p class="page-subtitulo">Socio N° <?= $u['nro_socio'] ?> — <?= $u['nombre'] ?> <?= $u['apellido'] ?></p>

<?php if (!empty($_SESSION['msg'])): ?>
    <div class="msg msg-ok"><?= htmlspecialchars($_SESSION['msg']) ?></div>
    <?php unset($_SESSION['msg']); ?>
<?php endif; ?>

<div class="stats-row">
    <div class="stat-card">
        <span class="stat-numero"><?= $activos ?>/2</span>
        <span class="stat-label">Ejemplares activos</span>
    </div>
    <div class="stat-card">
        <span class="stat-numero"><?= count($prestamos) ?></span>
        <span class="stat-label">Total histórico</span>
    </div>
</div>

<div class="cupos-row">
    <div class="cupo-item <?= $activos>=1?'activo':'' ?>">Ejemplar 1 <?= $activos>=1?'✓':'' ?></div>
    <div class="cupo-item <?= $activos>=2?'activo':'' ?>">Ejemplar 2 <?= $activos>=2?'✓':'' ?></div>
</div>

<?php if (empty($prestamos)): ?>
    <div class="vacio"><span>📭</span><p>Todavía no retiraste ningún libro.</p></div>
<?php else: ?>
<div class="tabla-wrap">
<table class="tabla">
    <thead>
        <tr><th>Título</th><th>Autor</th><th>ISBN</th><th>Retiro</th><th>Devolución</th><th>Estado</th><th>Acción</th></tr>
    </thead>
    <tbody>
    <?php foreach ($prestamos as $p): ?>
    <tr>
        <td><strong><?= htmlspecialchars($p['titulo']) ?></strong></td>
        <td><?= htmlspecialchars($p['autor']) ?></td>
        <td><?= htmlspecialchars($p['isbn']) ?></td>
        <td><?= $p['fecha_retiro'] ?></td>
        <td><?= $p['fecha_devolucion'] ?? '—' ?></td>
        <td>
            <?= $p['fecha_devolucion']
                ? '<span class="badge badge-verde">Devuelto ✓</span>'
                : '<span class="badge badge-naranja">En préstamo</span>' ?>
        </td>
        <td>
            <?php if (!$p['fecha_devolucion']): ?>
            <form method="post" style="display:inline">
                <input type="hidden" name="prestamo_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-peligro btn-sm"
                        onclick="return confirm('¿Confirmar devolución?')">Devolver</button>
            </form>
            <?php else: ?> — <?php endif; ?>
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
