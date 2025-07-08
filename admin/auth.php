<?php
// admin/auth.php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: login.php?error=Todos los campos son obligatorios');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email_admin = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_admin'])) {
        // Credenciales correctas
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_nombre'] = $admin['nombre_admin'];
        header('Location: index.php'); // Redirigir al dashboard
        exit();
    } else {
        // Credenciales incorrectas
        header('Location: login.php?error=Email o contraseña incorrectos');
        exit();
    }
} else {
    // Si se accede directamente, redirigir al login
    header('Location: login.php');
    exit();
}
?>