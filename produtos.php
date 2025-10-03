<?php
// produtos.php
require 'db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::getConnection();
$mensagem = '';
$mensagem_erro = '';

// Processar cadastro de produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_produto'])) {
    $nome = trim($_POST['nome']);
    $preco = trim($_POST['preco']);
    $fornecedor_id = $_POST['fornecedor_id'];
    
    if (empty($nome) || empty($preco) || empty($fornecedor_id)) {
        $mensagem_erro = "Todos os campos são obrigatórios.";
    } else {
        try {
            $preco_formatado = str_replace(',', '.', $preco);
            $preco_float = floatval($preco_formatado);
            
            if ($preco_float <= 0) {
                $mensagem_erro = "O preço deve ser maior que zero.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, fornecedor_id) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $preco_float, $fornecedor_id]);
                $mensagem = "Produto <strong>" . htmlspecialchars($nome) . "</strong> cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao cadastrar produto: " . $e->getMessage();
        }
    }
}

// Buscar fornecedores para o select
$fornecedores = [];
try {
    $stmt = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome");
    $fornecedores = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem_erro .= ($mensagem_erro ? '<br>' : '') . "Erro ao buscar fornecedores: " . $e->getMessage();
}

// Buscar produtos
$produtos = [];
try {
    $stmt = $pdo->query("
        SELECT p.id, p.nome, p.preco, f.nome as fornecedor_nome 
        FROM produtos p 
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
        ORDER BY p.nome
    ");
    $produtos = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem_erro .= ($mensagem_erro ? '<br>' : '') . "Erro ao buscar produtos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Produtos - Gestão de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h3 class="mb-4">Gestão de Produtos</h3>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger"><?php echo $mensagem_erro; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">Cadastrar Novo Produto</div>
            <div class="card-body">
                <form action="produtos.php" method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nome" class="form-label">Nome do Produto</label>
                            <input type="text" name="nome" id="nome" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="preco" class="form-label">Preço (R$)</label>
                            <input type="text" name="preco" id="preco" class="form-control" placeholder="0,00" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="fornecedor_id" class="form-label">Fornecedor</label>
                            <select name="fornecedor_id" id="fornecedor_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($fornecedores as $forn): ?>
                                    <option value="<?php echo $forn['id']; ?>"><?php echo htmlspecialchars($forn['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="cadastrar_produto" class="btn btn-primary w-100">Cadastrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <h4 class="mt-5 mb-3">Produtos Cadastrados</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Fornecedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produtos)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum produto cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($produtos as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['id']); ?></td>
                                <td><?php echo htmlspecialchars($prod['nome']); ?></td>
                                <td>R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($prod['fornecedor_nome']); ?></td>
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