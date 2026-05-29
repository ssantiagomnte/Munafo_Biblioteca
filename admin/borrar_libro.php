<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
iniciarSesion();
requerirAdmin();

$pdo = conectar();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) { header('Location: ../index.php'); exit; }

// Verificar que no tenga préstamos activos antes de borrar
$stmt = $pdo->prepare('SELECT COUNT(*) FROM prestamos WHERE libro_id = :id AND fecha_devolucion IS NULL');
$stmt->execute([':id' => $id]);
if ((int)$stmt->fetchColumn() > 0) {
    $_SESSION['msg'] = 'No se puede borrar: el libro tiene préstamos activos.';
    header('Location: ../index.php'); exit;
}

$pdo->prepare('DELETE FROM libros WHERE id = :id')->execute([':id' => $id]);
$_SESSION['msg'] = 'Libro eliminado correctamente.';
header('Location: ../index.php');
exit;