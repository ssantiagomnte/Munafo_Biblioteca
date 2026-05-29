<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
iniciarSesion();
requerirAdmin();

$pdo = conectar();
$error = $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn      = trim($_POST['isbn']      ?? '');
    $titulo    = trim($_POST['titulo']    ?? '');
    $autor     = trim($_POST['autor']     ?? '');
    $tema      = trim($_POST['tema']      ?? '');
    $editorial = trim($_POST['editorial'] ?? '');
    $anio      = (int)($_POST['anio']     ?? 0);
    $stock     = max(1,(int)($_POST['stock'] ?? 1));

    if (!$isbn || !$titulo || !$autor || !$tema || !$editorial || !$anio)
        $error = 'Todos los campos son obligatorios.';
    else {
        try {
            $pdo->prepare('INSERT INTO libros (isbn,titulo,autor,tema,editorial,anio,stock_total,stock_disp) VALUES (:isbn,:titulo,:autor,:tema,:editorial,:anio,:st,:st2)')
                ->execute([':isbn'=>$isbn,':titulo'=>$titulo,':autor'=>$autor,':tema'=>$tema,':editorial'=>$editorial,':anio'=>$anio,':st'=>$stock,':st2'=>$stock]);
            $ok = "Libro \"$titulo\" cargado con $stock ejemplar(es).";
        } catch (PDOException $e) {
            $error = 'Error: ese ISBN ya existe.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar libro – Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="logo"><a href="../index.php" style="color:inherit;display:flex;align-items:center;gap:10px"><span>📚</span> Biblioteca Virtual</a></div>
        <nav class="site-nav">
            <a href="../catalogo.php">Catálogo</a>
            <a href="prestamos.php" class="nav-admin">📋 Préstamos</a>
            <a href="socios.php"    class="nav-admin">👥 Socios</a>
            <a href="../logout.php" class="nav-salir">Salir</a>
        </nav>
    </div>
</header>

<main>

<h1 class="page-titulo">Cargar nuevo libro</h1>
<p class="page-subtitulo">Completá los datos para agregar un libro al catálogo</p>

<?php if ($error): ?><div class="msg msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($ok):    ?><div class="msg msg-ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

<div class="form-box">
    <form method="post" action="cargar_libro.php">
        <div class="form-grid-2">
            <div class="form-grupo">
                <label>ISBN *</label>
                <input type="text" name="isbn" required placeholder="978-...">
            </div>
            <div class="form-grupo">
                <label>Año de edición *</label>
                <input type="number" name="anio" required min="1000" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
            </div>
        </div>
        <div class="form-grupo">
            <label>Título *</label>
            <input type="text" name="titulo" required>
        </div>
        <div class="form-grid-2">
            <div class="form-grupo">
                <label>Autor *</label>
                <input type="text" name="autor" required>
            </div>
            <div class="form-grupo">
                <label>Editorial *</label>
                <input type="text" name="editorial" required>
            </div>
        </div>
        <div class="form-grid-2">
            <div class="form-grupo">
                <label>Tema *</label>
                <input type="text" name="tema" required placeholder="Ej: Fantasía, Historia...">
            </div>
            <div class="form-grupo">
                <label>Cantidad de ejemplares *</label>
                <input type="number" name="stock" required min="1" value="1">
            </div>
        </div>
        <button type="submit" class="btn btn-primario btn-full">Cargar libro</button>
    </form>
</div>

</main>

<footer class="site-footer">
    <p>Biblioteca Virtual &copy; <?= date('Y') ?></p>
</footer>

</body>
</html>
