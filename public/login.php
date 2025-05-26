<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_start();

use Services\Auth;

$mensagem = '';
$auth = new Auth();

// Se já estiver logado, redireciona para index
if (Auth::verificarLogin()) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        header('Location: ../index.php');
        exit;
    } else {
        $mensagem = 'Usuário ou senha inválidos';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Locadora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card-footer {
            background-color: #f8f9fa;
            padding: 1rem;
        }
        .login-tip {
            border-left: 4px solid #0d6efd;
            padding: 15px;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 0 4px 4px 0;
            margin-top: 15px;
        }
        .login-tip h5 {
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .login-tip p {
            margin-bottom: 5px;
        }
        .login-tip code {
            background: #fff;
            padding: 2px 4px;
            border-radius: 4px;
            color: #333;
        }
    </style>
</head>
<body class="bg-light">
    <div class="login-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-lock me-2"></i>Login</h4>
            </div>
            <div class="card-body">
                <?php if ($mensagem): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>
                
                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-1"></i>Usuário
                        </label>
                        <input type="text" id="username" name="username" class="form-control" required>
                        <div class="invalid-feedback">
                            Por favor, informe o nome de usuário.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-key me-1"></i>Senha
                        </label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <div class="invalid-feedback">
                            Por favor, informe a senha.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                    </button>
                </form>
                
                <!-- Seção com a dica de login solicitada -->
                <div class="login-tip mt-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Acesso ao Sistema</h5>
                    <p>Utilize os seguintes dados para acessar o sistema:</p>
                    <p><strong>Administrador:</strong> <code>admin</code> / <code>admin123</code> - Acesso total ao sistema</p>
                    <p><strong>Usuário:</strong> <code>usuario</code> / <code>user123</code> - Acesso limitado</p>
                </div>
            </div>
            <div class="card-footer text-center">
                <small class="text-muted">Sistema de Locadora de Veículos &copy; <?= date('Y') ?></small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário usando Bootstrap
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>