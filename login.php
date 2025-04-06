<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT idusuario, nombre, password, rol FROM usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['idusuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Contraseña incorrecta.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
        header("Location: login.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/a2d9d5e97e.js" crossorigin="anonymous"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(-45deg, #1e3c72, #2a5298, #1e90ff, #00bfff);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card {
            background: white;
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-icon {
            font-size: 4rem;
            color: #1e90ff;
        }

        .form-control {
            border-radius: 0.7rem;
        }

        .btn-primary {
            border-radius: 0.7rem;
            background-color: #1e90ff;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #006fd6;
        }

        .form-label {
            font-weight: 600;
        }

        .text-center h3 {
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="card p-4">
        <div class="text-center mb-4">
            <i class="fas fa-user-circle login-icon"></i>
            <h3 class="mt-2">Iniciar Sesión</h3>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock me-2"></i>Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2">
                <i class="fas fa-sign-in-alt me-1"></i>Ingresar
            </button>
        </form>
    </div>



    <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['login_error'])): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '<?php echo $_SESSION['login_error']; ?>',
        confirmButtonColor: '#3085d6'
    });
</script>
<?php unset($_SESSION['login_error']); ?>
<?php endif; ?>
</body>
</html>