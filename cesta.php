<?php
// criar_cesta.php
require 'db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::getConnection();
$mensagem = '';
$mensagem_erro = '';

// Buscar produtos disponíveis
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
    $mensagem_erro = "Erro ao buscar produtos: " . $e->getMessage();
}

// Processar criação da cesta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produtos'])) {
    $produtos_selecionados = $_POST['produtos'];
    
    if (empty($produtos_selecionados)) {
        $mensagem_erro = "Selecione pelo menos um produto para a cesta.";
    } else {
        $usuario_id = $_SESSION['usuario_id'];
        $produtos_validos = [];
        
        // Validar IDs dos produtos
        foreach ($produtos_selecionados as $produto_id) {
            $id = filter_var($produto_id, FILTER_VALIDATE_INT);
            if ($id !== false) {
                $produtos_validos[] = $id;
            }
        }
        
        if (empty($produtos_validos)) {
            $mensagem_erro = "IDs de produtos inválidos detectados.";
        } else {
            $pdo->beginTransaction();
            
            try {
                // Criar a cesta
                $stmt = $pdo->prepare("INSERT INTO cesta (usuario_id) VALUES (?)");
                $stmt->execute([$usuario_id]);
                $cesta_id = $pdo->lastInsertId();
                
                // Adicionar produtos à cesta
                $sql_parts = [];
                $params = [];
                
                foreach ($produtos_validos as $produto_id) {
                    $sql_parts[] = '(?, ?)';
                    $params[] = $cesta_id;
                    $params[] = $produto_id;
                }
                
                $sql = "INSERT INTO cesta_produtos (cesta_id, produto_id) VALUES " . implode(', ', $sql_parts);
                $stmt_produtos = $pdo->prepare($sql);
                $stmt_produtos->execute($params);
                
                $pdo->commit();
                
                $_SESSION['mensagem_cesta'] = "Cesta criada com sucesso com " . count($produtos_validos) . " produtos.";
                header("Location: visualizar_cesta.php");
                exit();
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensagem_erro = "Erro ao criar a cesta: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Cesta - Gestão de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h3 class="mb-4">Criar Nova Cesta</h3>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger"><?php echo $mensagem_erro; ?></div>
        <?php endif; ?>

        <?php if (empty($produtos)): ?>
            <div class="alert alert-warning">
                Não há produtos cadastrados no sistema. <a href="produtos.php">Cadastre produtos</a> primeiro.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Selecione os produtos para sua cesta</h5>
                </div>
                <div class="card-body">
                    <form action="criar_cesta.php" method="POST">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="50px">Selecionar</th>
                                        <th>Produto</th>
                                        <th>Fornecedor</th>
                                        <th>Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos as $produto): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="produtos[]" value="<?php echo $produto['id']; ?>" class="form-check-input">
                                            </td>
                                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($produto['fornecedor_nome']); ?></td>
                                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">Criar Cesta</button>
                            <a href="visualizar_cesta.php" class="btn btn-secondary">Ver Cesta Atual</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>