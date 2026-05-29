<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
iniciarSesion();
if (usuario()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni      = trim($_POST['dni']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $pass     = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (!$nombre || !$apellido || !$dni || !$email || !$pass)
        $error = 'Completá todos los campos.';
    elseif (!preg_match('/^\d{7,8}$/', $dni))
        $error = 'El DNI debe tener 7 u 8 números, sin puntos ni espacios.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Email inválido.';
    elseif (strlen($pass) < 6)
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    elseif ($pass !== $confirm)
        $error = 'Las contraseñas no coinciden.';
    else {
        $pdo  = conectar();
        $stmt = $pdo->prepare('SELECT id FROM socios WHERE email=:e');
        $stmt->execute([':e'=>$email]);
        if ($stmt->fetch()) {
            $error = 'Ya existe una cuenta con ese email.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM socios WHERE dni=:d');
            $stmt->execute([':d'=>$dni]);
            if ($stmt->fetch()) {
                $error = 'Ya existe una cuenta con ese DNI.';
            } else {
                $nro  = ((int)$pdo->query('SELECT MAX(nro_socio) FROM socios')->fetchColumn()) + 1;
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $pdo->prepare('INSERT INTO socios (nro_socio,nombre,apellido,dni,email,password,rol) VALUES (:n,:nom,:ape,:dni,:e,:p,"socio")')
                    ->execute([':n'=>$nro,':nom'=>$nombre,':ape'=>$apellido,':dni'=>$dni,':e'=>$email,':p'=>$hash]);
                $_SESSION['msg'] = "Cuenta creada. Tu número de socio es el #$nro.";
                header('Location: login.php'); exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse – Biblioteca Virtual</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="pagina-auth">

<div class="auth-contenedor">
    <div class="auth-deco">
        <div class="auth-deco-logo">📚</div>
        <h1>Biblioteca Virtual</h1>
        <p>Creá tu cuenta y empezá a retirar libros hoy mismo.</p>
    </div>
    <div class="auth-form-box">
        <a href="index.php" class="auth-volver">← Volver al inicio</a>

        <h2 class="form-titulo">Crear cuenta</h2>
        <p class="form-subtitulo">Completá tus datos para registrarte como socio</p>

        <?php if ($error): ?><div class="msg msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="post" action="registro.php">
            <div class="form-grid-2">
                <div class="form-grupo">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="form-grupo">
                    <label>Apellido</label>
                    <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                </div>
            </div>
            <div class="form-grupo">
                <label>DNI</label>
                <input type="text" name="dni" required
                       value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>"
                       maxlength="8" pattern="\d{7,8}"
                       title="Solo números, 7 u 8 dígitos"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       placeholder="Ej: 48502228">
                <small>Solo números, sin puntos ni espacios (7 u 8 dígitos)</small>
            </div>
            <div class="form-grupo">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-grid-2">
                <div class="form-grupo">
                    <label>Contraseña</label>
                    <input type="password" name="password" required minlength="6">
                    <small>Mínimo 6 caracteres</small>
                </div>
                <div class="form-grupo">
                    <label>Repetir contraseña</label>
                    <input type="password" name="confirm" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primario btn-full">Crear cuenta</button>
        </form>

        <p class="auth-link">¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
</div>

</body>
</html>
