<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro - Gestão de Produtos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">Cadastro de Usuário</h2>
    <form action="register.php" method="POST" class="mx-auto" style="max-width: 400px;">
      <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Senha</label>
        <input type="password" name="senha" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Cadastrar</button>
      <p class="mt-3 text-center">Já tem conta? <a href="index.html">Fazer Login</a></p>
    </form>
  </div>
</body>
</html>