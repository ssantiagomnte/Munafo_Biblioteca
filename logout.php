<?php
require_once 'includes/auth.php';
iniciarSesion();
session_destroy();
header('Location: index.php');
exit;
