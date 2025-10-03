<?php
// esvaziar_cesta.php
require 'db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::getConnection();
$usuario_id = $_SESSION['usuario_id'];

try {
    // Buscar a cesta mais recente do usuário
    $stmt = $pdo->prepare("
        SELECT id 
        FROM cesta 
        WHERE usuario_id = ? 
        ORDER BY data_criacao DESC 
        LIMIT 1
    ");
    $stmt->execute([$usuario_id]);
    $cesta = $stmt->fetch();
    
    if ($cesta) {
        // Deletar produtos da cesta
        $stmt = $pdo->prepare("DELETE FROM cesta_produtos WHERE cesta_id = ?");
        $stmt->execute([$cesta['id']]);
        
        $_SESSION['mensagem_cesta'] = "Cesta esvaziada com sucesso!";
    } else {
        $_SESSION['mensagem_cesta'] = "Nenhuma cesta encontrada para esvaziar.";
    }
} catch (PDOException $e) {
    $_SESSION['mensagem_cesta'] = "Erro ao esvaziar cesta: " . $e->getMessage();
}

header("Location: visualizar_cesta.php");
exit();
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