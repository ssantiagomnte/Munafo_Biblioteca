<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
iniciarSesion();
if (usuario()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if (!$email || !$pass) {
        $error = 'Completá todos los campos.';
    } else {
        $stmt = conectar()->prepare('SELECT * FROM socios WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            unset($u['password']);
            $_SESSION['usuario'] = $u;
            header('Location: index.php'); exit;
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión – Biblioteca Virtual</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="pagina-auth">

<div class="auth-contenedor">
    <div class="auth-deco">
        <div class="auth-deco-logo">📚</div>
        <h1>Biblioteca Virtual</h1>
        <p>Iniciá sesión para acceder al catálogo y retirar libros.</p>
    </div>
    <div class="auth-form-box">
        <a href="index.php" class="auth-volver">← Volver al inicio</a>

        <h2 class="form-titulo">Iniciar sesión</h2>
        <p class="form-subtitulo">Ingresá tus datos para continuar</p>

        <?php if ($error): ?><div class="msg msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (!empty($_SESSION['msg'])): ?>
            <div class="msg msg-ok"><?= htmlspecialchars($_SESSION['msg']) ?></div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-grupo">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primario btn-full">Entrar</button>
        </form>

        <p class="auth-link">¿No tenés cuenta? <a href="registro.php">Registrarse</a></p>
    </div>
</div>

</body>
</html>
