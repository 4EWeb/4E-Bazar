<?php
// admin/login.php
session_start();
// Si ya hay una sesión de admin, redirigir al panel
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}
$error = $_GET['error'] ?? null;
$logout = $_GET['logout'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Administrador</title>
    <link rel="stylesheet" href="css_admin/admin_styles.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="logo-container">
                <h3>4E Bazar - Admin</h3>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($logout): ?>
                <div class="alert alert-success">Has cerrado sesión correctamente.</div>
            <?php endif; ?>

            <form action="auth.php" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>