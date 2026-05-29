<?php
function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}
function usuario(): ?array {
    iniciarSesion();
    return $_SESSION['usuario'] ?? null;
}
function requerirLogin(): void {
    if (!usuario()) { header('Location: login.php'); exit; }
}
function requerirAdmin(): void {
    requerirLogin();
    if (usuario()['rol'] !== 'admin') { header('Location: index.php'); exit; }
}
function esAdmin(): bool {
    $u = usuario(); return $u && $u['rol'] === 'admin';
}
