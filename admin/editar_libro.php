<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
iniciarSesion();
requerirAdmin();

$pdo   = conectar();
$error = $ok = '';
$id    = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ../catalogo.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM libros WHERE id = :id');
$stmt->execute([':id' => $id]);
$libro = $stmt->fetch();
if (!$libro) { header('Location: ../catalogo.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn      = trim($_POST['isbn']      ?? '');
    $titulo    = trim($_POST['titulo']    ?? '');
    $autor     = trim($_POST['autor']     ?? '');
    $tema      = trim($_POST['tema']      ?? '');
    $editorial = trim($_POST['editorial'] ?? '');
    $anio      = (int)($_POST['anio']     ?? 0);
    $stock     = max(1,(int)($_POST['stock'] ?? 1));

    if (!$isbn || !$titulo || !$autor || !$tema || !$editorial || !$anio) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        try {
            $pdo->prepare(
                'UPDATE libros SET isbn=:isbn,titulo=:titulo,autor=:autor,
                 tema=:tema,editorial=:editorial,anio=:anio,
                 stock_total=:st,stock_disp=stock_disp+(:st-stock_total)
                 WHERE id=:id'
            )->execute([':isbn'=>$isbn,':titulo'=>$titulo,':autor'=>$autor,
                        ':tema'=>$tema,':editorial'=>$editorial,':anio'=>$anio,
                        ':st'=>$stock,':id'=>$id]);
            $ok = 'Libro actualizado correctamente.';
            $stmt = $pdo->prepare('SELECT * FROM libros WHERE id=:id');
            $stmt->execute([':id'=>$id]);
            $libro = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Error: ese ISBN ya está en uso por otro libro.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar libro – Admin</title>
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
            <a href="socios.php"       class="nav-admin">👥 Socios</a>
            <a href="../logout.php" class="nav-salir">Salir</a>
        </nav>
    </div>
</header>

<main>

<h1 class="page-titulo">Editar libro</h1>
<p class="page-subtitulo">Modificá los datos del libro seleccionado</p>

<?php if ($error): ?><div class="msg msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($ok):    ?><div class="msg msg-ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

<div class="form-box">
    <form method="post">
        <div class="form-grid-2">
            <div class="form-grupo">
                <label>ISBN *</label>
                <input type="text" name="isbn" required value="<?= htmlspecialchars($libro['isbn']) ?>">
            </div>
            <div class="form-grupo">
                <label>Año de edición *</label>
                <input type="number" name="anio" required min="1000" max="<?= date('Y') ?>" value="<?= $libro['anio'] ?>">
            </div>
        </div>
        <div class="form-grupo">
            <label>Título *</label>
            <input type="text" name="titulo" required value="<?= htmlspecialchars($libro['titulo']) ?>">
        </div>
        <div class="form-grid-2">
            <div class="form-grupo">
                <label>Autor *</label>
                <input type="text" name="autor" required value="<?= htmlspecialchars($libro['autor']) ?>">
            </div>
            <div class="form-grupo">
                <label>Editorial *</label>
                <input type="text" name="editorial" required value="<?= htmlspecialchars($libro['editorial']) ?>">
            </div>
        </div>
        <div class="form-grid-2">
            <div class="form-grupo">
                <label>Tema *</label>
                <input type="text" name="tema" required value="<?= htmlspecialchars($libro['tema']) ?>">
            </div>
            <div class="form-grupo">
                <label>Stock total *</label>
                <input type="number" name="stock" required min="1" value="<?= $libro['stock_total'] ?>">
                <small>Disponibles ahora: <?= $libro['stock_disp'] ?></small>
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px">
            <button type="submit" class="btn btn-primario">Guardar cambios</button>
            <a href="../catalogo.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

</main>

<footer class="site-footer">
    <p>Biblioteca Virtual &copy; <?= date('Y') ?></p>
</footer>

</body>
</html>
