<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
iniciarSesion();
requerirLogin();

$u = usuario();
if ($u['rol'] !== 'socio' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$libro_id = (int)($_POST['libro_id'] ?? 0);
$pdo      = conectar();

// Verificar que no tenga ya 2 préstamos activos
$stmt = $pdo->prepare('SELECT COUNT(*) FROM prestamos WHERE socio_id=:uid AND fecha_devolucion IS NULL');
$stmt->execute([':uid' => $u['id']]);
if ((int)$stmt->fetchColumn() >= 2) {
    $_SESSION['msg'] = 'Ya tenés 2 ejemplares retirados. Devolvé uno antes de retirar otro.';
    header('Location: index.php'); exit;
}

// Verificar stock (transacción para evitar problemas si dos personas retirar a la vez)
$pdo->beginTransaction();
$stmt = $pdo->prepare('SELECT * FROM libros WHERE id=:id FOR UPDATE');
$stmt->execute([':id' => $libro_id]);
$libro = $stmt->fetch();

if (!$libro || $libro['stock_disp'] <= 0) {
    $pdo->rollBack();
    $_SESSION['msg'] = 'El libro no está disponible.';
    header('Location: index.php'); exit;
}

$pdo->prepare('INSERT INTO prestamos (socio_id,libro_id,fecha_retiro) VALUES (:uid,:lid,CURDATE())')
    ->execute([':uid'=>$u['id'], ':lid'=>$libro_id]);
$pdo->prepare('UPDATE libros SET stock_disp=stock_disp-1 WHERE id=:id')
    ->execute([':id'=>$libro_id]);
$pdo->commit();

$_SESSION['msg'] = 'Retiraste "' . $libro['titulo'] . '" correctamente.';
header('Location: index.php');
exit;
