<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
iniciarSesion();
$u = usuario();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Virtual</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="logo"><span>📚</span> Biblioteca Virtual</div>
        <nav class="site-nav">
            <?php if ($u): ?>
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

<?php if (!$u): ?>

    <div class="hero">
        <span class="hero-icono">📚</span>
        <h2>Bienvenido a la Biblioteca</h2>
        <p>Explorá nuestro catálogo, registrate como socio y retirá hasta 2 libros a la vez.</p>
    </div>

    <div class="menu-grid">
        <a href="login.php" class="menu-card destacado">
            <span class="menu-card-icono">🔑</span>
            <h3>Iniciar sesión</h3>
            <p>Accedé con tu cuenta de socio</p>
        </a>
        <a href="registro.php" class="menu-card">
            <span class="menu-card-icono">📝</span>
            <h3>Registrarse</h3>
            <p>Creá tu cuenta de socio gratis</p>
        </a>
        <a href="catalogo.php" class="menu-card">
            <span class="menu-card-icono">📖</span>
            <h3>Ver catálogo</h3>
            <p>Explorá todos los libros disponibles</p>
        </a>
    </div>

<?php elseif ($u['rol'] === 'admin'): ?>

    <div class="bienvenida-box">
        <h2>¡Bienvenido, <?= htmlspecialchars($u['nombre']) ?>!</h2>
        <p>Panel de administrador — gestioná libros, préstamos y socios.</p>
    </div>

    <ul class="menu-lista">
        <li><a href="catalogo.php"><span class="icono">📖</span> Ver catálogo de libros</a></li>
        <li><a href="admin/cargar_libro.php"><span class="icono">➕</span> Cargar nuevo libro</a></li>
        <li><a href="admin/prestamos.php"><span class="icono">📋</span> Ver préstamos</a></li>
        <li><a href="admin/socios.php"><span class="icono">👥</span> Ver socios</a></li>
        <li><a href="logout.php"><span class="icono">🚪</span> Cerrar sesión</a></li>
    </ul>

<?php else: ?>

    <div class="bienvenida-box">
        <h2>¡Bienvenido, <?= htmlspecialchars($u['nombre']) ?> <?= htmlspecialchars($u['apellido']) ?>!</h2>
        <p>Socio N° <?= $u['nro_socio'] ?> — Podés retirar hasta <strong>2 ejemplares</strong> a la vez.</p>
    </div>

    <ul class="menu-lista">
        <li><a href="catalogo.php"><span class="icono">📖</span> Ver catálogo y retirar libros</a></li>
        <li><a href="mis_prestamos.php"><span class="icono">📋</span> Mis préstamos</a></li>
        <li><a href="logout.php"><span class="icono">🚪</span> Cerrar sesión</a></li>
    </ul>

<?php endif; ?>

</main>

<footer class="site-footer">
    <p>Biblioteca Virtual &copy; <?= date('Y') ?> — Proyecto 7° 6 Programación</p>
</footer>

</body>
</html>
