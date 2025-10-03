<?php
require 'db.php';
session_start();


if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}


$pdo = Database::getConnection();
$mensagem = '';
$mensagem_erro = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $nome = trim($_POST['nome']);
    $cnpj = trim($_POST['cnpj']);

    if (empty($nome) || empty($cnpj)) {
        $mensagem_erro = "Nome e CNPJ são obrigatórios.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj) VALUES (?, ?)");
            $stmt->execute([$nome, $cnpj]);
            $mensagem = "Fornecedor **" . htmlspecialchars($nome) . "** cadastrado com sucesso!";
        } catch (PDOException $e) {
         
            if ($e->getCode() == '23000') {
                $mensagem_erro = "Erro: O CNPJ informado já existe no sistema.";
            } else {
                $mensagem_erro = "Erro ao cadastrar fornecedor: " . $e->getMessage();
            }
        }
    }
}


$fornecedores = [];
try {
    $stmt = $pdo->query("SELECT id, nome, cnpj FROM fornecedores ORDER BY nome");
    $fornecedores = $stmt->fetchAll();
} catch (PDOException $e) {
  
    $mensagem_erro .= ($mensagem_erro ? '<br>' : '') . "Erro ao buscar fornecedores: " . $e->getMessage();
}


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fornecedores - Gestão de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
   <?php include 'navbar.php'; ?>
     

    <div class="container mt-4">
        <h3 class="mb-4">Gestão de Fornecedores</h3>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger"><?php echo $mensagem_erro; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">Cadastrar Novo Fornecedor</div>
            <div class="card-body">
                <form action="fornecedores.php" method="POST">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="nome" class="form-label">Nome/Razão Social</label>
                            <input type="text" name="nome" id="nome" class="form-control" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="cnpj" class="form-label">CNPJ</label>
                            <input type="text" name="cnpj" id="cnpj" class="form-control" maxlength="20" required>
                            <div class="form-text">Apenas números ou formato completo com pontos e traços.</div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <h4 class="mt-5 mb-3">Fornecedores Cadastrados</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome/Razão Social</th>
                        <th>CNPJ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fornecedores)): ?>
                        <tr>
                            <td colspan="3" class="text-center">Nenhum fornecedor cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fornecedores as $forn): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($forn['id']); ?></td>
                                <td><?php echo htmlspecialchars($forn['nome']); ?></td>
                                <td><?php echo htmlspecialchars($forn['cnpj']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>