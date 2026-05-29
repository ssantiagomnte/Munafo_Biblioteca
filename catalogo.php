<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
iniciarSesion();

$pdo   = conectar();
$q     = trim($_GET['q']    ?? '');
$autor = trim($_GET['autor'] ?? '');
$tema  = trim($_GET['tema']  ?? '');

$where  = ['1=1'];
$params = [];
if ($q)     { $where[] = '(titulo LIKE :q OR isbn LIKE :q2)'; $params[':q'] = $params[':q2'] = "%$q%"; }
if ($autor) { $where[] = 'autor LIKE :autor'; $params[':autor'] = "%$autor%"; }
if ($tema)  { $where[] = 'tema  LIKE :tema';  $params[':tema']  = "%$tema%";  }

$stmt = $pdo->prepare('SELECT * FROM libros WHERE '.implode(' AND ',$where).' ORDER BY titulo');
$stmt->execute($params);
$libros = $stmt->fetchAll();
$temas  = $pdo->query('SELECT DISTINCT tema FROM libros ORDER BY tema')->fetchAll(PDO::FETCH_COLUMN);
$u      = usuario();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo – Biblioteca Virtual</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="logo"><a href="index.php" style="color:inherit;display:flex;align-items:center;gap:10px"><span>📚</span> Biblioteca Virtual</a></div>
        <nav class="site-nav">
            <a href="index.php">Inicio</a>
            <?php if ($u): ?>
                <?php if (esAdmin()): ?>
                    <a href="admin/cargar_libro.php" class="nav-admin">➕ Cargar libro</a>
                    <a href="admin/prestamos.php"    class="nav-admin">📋 Préstamos</a>
                    <a href="admin/socios.php"       class="nav-admin">👥 Socios</a>
                <?php else: ?>
                    <a href="mis_prestamos.php">Mis préstamos</a>
                <?php endif; ?>
                <span class="nav-usuario">👤 <?= htmlspecialchars($u['nombre']) ?></span>
                <a href="logout.php" class="nav-salir">Salir</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
                <a href="registro.php" class="nav-cta">Registrarse</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>

<?php if (!empty($_SESSION['msg'])): ?>
    <div class="msg msg-ok"><?= htmlspecialchars($_SESSION['msg']) ?></div>
    <?php unset($_SESSION['msg']); ?>
<?php endif; ?>

<h1 class="page-titulo">Catálogo de libros</h1>
<p class="page-subtitulo"><?= count($libros) ?> libro<?= count($libros)!=1?'s':'' ?> encontrado<?= count($libros)!=1?'s':'' ?></p>

<div class="buscador-box">
    <form method="get" action="catalogo.php">
        <div class="campo">
            <label>Título / ISBN</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar...">
        </div>
        <div class="campo">
            <label>Autor</label>
            <input type="text" name="autor" value="<?= htmlspecialchars($autor) ?>" placeholder="Nombre del autor...">
        </div>
        <div class="campo">
            <label>Tema</label>
            <select name="tema">
                <option value="">Todos los temas</option>
                <?php foreach ($temas as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= $tema===$t?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end">
            <button type="submit" class="btn btn-primario">Buscar</button>
            <?php if ($q||$autor||$tema): ?>
                <a href="catalogo.php" class="btn btn-outline">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (empty($libros)): ?>
    <div class="vacio"><span>📭</span><p>No se encontraron libros con esos criterios.</p></div>
<?php else: ?>
<div class="tabla-wrap">
<table class="tabla">
    <thead>
        <tr>
            <th>ISBN</th><th>Título</th><th>Autor</th><th>Tema</th>
            <th>Editorial</th><th>Año</th><th>Stock</th><th>Acción</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($libros as $libro): ?>
    <tr>
        <td><?= htmlspecialchars($libro['isbn']) ?></td>
        <td><strong><?= htmlspecialchars($libro['titulo']) ?></strong></td>
        <td><?= htmlspecialchars($libro['autor']) ?></td>
        <td><span class="badge badge-naranja"><?= htmlspecialchars($libro['tema']) ?></span></td>
        <td><?= htmlspecialchars($libro['editorial']) ?></td>
        <td><?= $libro['anio'] ?></td>
        <td>
            <?php if ($libro['stock_disp'] > 0): ?>
                <span class="badge badge-verde"><?= $libro['stock_disp'] ?>/<?= $libro['stock_total'] ?></span>
            <?php else: ?>
                <span class="badge badge-rojo">Sin stock</span>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($u && $u['rol']==='socio' && $libro['stock_disp']>0): ?>
                <form method="post" action="retirar.php" style="display:inline">
                    <input type="hidden" name="libro_id" value="<?= $libro['id'] ?>">
                    <button type="submit" class="btn btn-dorado btn-sm">Retirar</button>
                </form>
            <?php elseif (!$u && $libro['stock_disp']>0): ?>
                <a href="login.php" class="btn btn-outline btn-sm">Iniciar sesión</a>
            <?php elseif (esAdmin()): ?>
                <a href="admin/editar_libro.php?id=<?= $libro['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                <a href="admin/borrar_libro.php?id=<?= $libro['id'] ?>" class="btn btn-peligro btn-sm"
                   onclick="return confirm('¿Seguro que querés borrar este libro?')">Borrar</a>
            <?php else: ?>
                <span style="color:#bbb;font-size:.82rem">Sin stock</span>
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
