<?php
// login.php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    if (empty($email) || empty($senha)) {
        $erro = "Email e senha são obrigatórios.";
    } else {
        $pdo = Database::getConnection();
        
        try {
            $stmt = $pdo->prepare("SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $erro = "Email ou senha incorretos.";
            }
        } catch (PDOException $e) {
            $erro = "Erro ao fazer login: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Gestão de Produtos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-container {
      background: #fff;
      padding: 2.5rem;
      border-radius: 1rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 400px;
      animation: fadeIn 0.8s ease-in-out;
    }

    .login-container h2 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #4e73df;
      text-align: center;
    }

    .form-label {
      font-weight: 500;
    }

    .form-control {
      border-radius: 0.5rem;
      padding: 0.75rem;
    }

    .btn-primary {
      background: #4e73df;
      border: none;
      border-radius: 0.5rem;
      padding: 0.75rem;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
    }

    .btn-primary:hover {
      background: #2e59d9;
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .login-container p {
      margin-top: 1rem;
      text-align: center;
      font-size: 0.95rem;
    }

    .login-container a {
      color: #1cc88a;
      text-decoration: none;
      font-weight: 500;
    }

    .login-container a:hover {
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    
    <?php if (isset($erro)): ?>
      <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
      <p>Não tem conta? <a href="register.php">Cadastre-se</a></p>
    </form>
  </div>
</body>
</html>