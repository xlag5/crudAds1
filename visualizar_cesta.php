<?php
// visualizar_cesta.php
$page_title = "Minha Cesta - Gestão de Produtos";
require 'header.php';

require 'db.php';
$pdo = Database::getConnection();
$usuario_id = $_SESSION['usuario_id'];

// Buscar a cesta atual do usuário
$cesta = null;
$produtos_cesta = [];
$total = 0;

try {
    // Buscar a cesta mais recente do usuário
    $stmt = $pdo->prepare("
        SELECT id, data_criacao 
        FROM cesta 
        WHERE usuario_id = ? 
        ORDER BY data_criacao DESC 
        LIMIT 1
    ");
    $stmt->execute([$usuario_id]);
    $cesta = $stmt->fetch();
    
    if ($cesta) {
        // Buscar produtos da cesta
        $stmt = $pdo->prepare("
            SELECT p.id, p.nome, p.preco, f.nome as fornecedor_nome
            FROM cesta_produtos cp
            JOIN produtos p ON cp.produto_id = p.id
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
            WHERE cp.cesta_id = ?
        ");
        $stmt->execute([$cesta['id']]);
        $produtos_cesta = $stmt->fetchAll();
        
        // Calcular total
        foreach ($produtos_cesta as $produto) {
            $total += $produto['preco'];
        }
    }
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar cesta: " . $e->getMessage();
}

// Mensagens de feedback
if (isset($_SESSION['mensagem_cesta'])) {
    $mensagem = $_SESSION['mensagem_cesta'];
    unset($_SESSION['mensagem_cesta']);
}
?>

<style>
/* Estilos específicos para a página de cesta */
.cesta-header {
    background: linear-gradient(135deg, var(--primary), #6610f2);
    color: white;
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
}

.cesta-vazia {
    padding: 4rem 2rem;
    text-align: center;
    border-radius: 1rem;
    background: white;
}

.cesta-vazia i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
    color: var(--primary);
}

.product-card {
    border-left: 4px solid var(--primary);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
    background: white;
}

.product-card:hover {
    border-left-color: var(--success);
    transform: translateX(5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
}

.supplier-badge {
    background: linear-gradient(45deg, var(--info), #2c9faf);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.price-tag {
    background: linear-gradient(45deg, var(--success), #17a673);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: bold;
    font-size: 1.1rem;
}

.total-card {
    background: linear-gradient(135deg, var(--success), #17a673);
    color: white;
    border-radius: 1rem;
    border: none;
}

.stats-card {
    background: linear-gradient(135deg, var(--info), #2c9faf);
    color: white;
    border-radius: 1rem;
    border: none;
}

.actions-card {
    border-radius: 1rem;
    border: none;
}

.btn-remove {
    background: linear-gradient(45deg, var(--danger), #c71c1c);
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(231, 74, 59, 0.3);
}

.empty-state-icon {
    font-size: 5rem;
    opacity: 0.7;
    margin-bottom: 1.5rem;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: rgba(255,255,255,0.1);
    border-radius: 0.5rem;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.8rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .cesta-header {
        padding: 1.5rem;
        text-align: center;
    }
    
    .quick-stats {
        grid-template-columns: 1fr;
    }
    
    .product-card {
        margin-bottom: 1rem;
    }
}

.animation-delay-1 { animation-delay: 0.1s; }
.animation-delay-2 { animation-delay: 0.2s; }
.animation-delay-3 { animation-delay: 0.3s; }
.animation-delay-4 { animation-delay: 0.4s; }
</style>

<!-- Header da Cesta -->
<div class="cesta-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2">
                <i class="fas fa-shopping-cart me-3"></i>Minha Cesta
            </h1>
            <p class="mb-0 opacity-75">Gerencie os produtos da sua cesta de compras</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group">
                <a href="criar_cesta.php" class="btn btn-light btn-lg me-2">
                    <i class="fas fa-plus me-2"></i>Adicionar
                </a>
                <?php if ($cesta && !empty($produtos_cesta)): ?>
                    <form action="esvaziar_cesta.php" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-outline-light btn-lg" 
                                onclick="return confirmAction('Tem certeza que deseja esvaziar toda a cesta?')">
                            <i class="fas fa-trash me-2"></i>Esvaziar
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (isset($mensagem)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $mensagem; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($mensagem_erro)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $mensagem_erro; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!$cesta): ?>
    <!-- Estado: Nenhuma cesta criada -->
    <div class="card cesta-vazia">
        <div class="card-body">
            <i class="fas fa-shopping-cart empty-state-icon"></i>
            <h3 class="text-muted mb-3">Nenhuma cesta criada</h3>
            <p class="text-muted mb-4">Você ainda não criou uma cesta. Comece adicionando alguns produtos ao seu carrinho.</p>
            <a href="criar_cesta.php" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-cart-plus me-2"></i>Criar Primeira Cesta
            </a>
        </div>
    </div>
<?php elseif (empty($produtos_cesta)): ?>
    <!-- Estado: Cesta vazia -->
    <div class="card cesta-vazia">
        <div class="card-body">
            <i class="fas fa-box-open empty-state-icon"></i>
            <h3 class="text-muted mb-3">Cesta vazia</h3>
            <p class="text-muted mb-4">Sua cesta foi criada, mas ainda não tem produtos. Adicione alguns itens para começar.</p>
            <a href="criar_cesta.php" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-plus me-2"></i>Adicionar Produtos
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Estado: Cesta com produtos -->
    <div class="row">
        <!-- Lista de Produtos -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>
                            Produtos na Cesta
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Criada em: <?php echo date('d/m/Y à\s H:i', strtotime($cesta['data_criacao'])); ?>
                        </small>
                    </div>
                    <span class="badge bg-primary fs-6"><?php echo count($produtos_cesta); ?> itens</span>
                </div>
                <div class="card-body">
                    <?php foreach ($produtos_cesta as $index => $produto): ?>
                        <div class="card product-card fade-in animation-delay-<?php echo ($index % 4) + 1; ?>">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-1 text-center">
                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <strong><?php echo $index + 1; ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <h6 class="mb-2 fw-bold text-dark"><?php echo htmlspecialchars($produto['nome']); ?></h6>
                                        <span class="supplier-badge">
                                            <i class="fas fa-truck me-1"></i>
                                            <?php echo htmlspecialchars($produto['fornecedor_nome']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <span class="price-tag">
                                            R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <form action="remover_produto_cesta.php" method="POST" class="d-inline">
                                            <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                            <button type="submit" class="btn btn-remove" 
                                                    onclick="return confirmAction('Remover <?php echo htmlspecialchars($produto['nome']); ?> da cesta?')">
                                                <i class="fas fa-times me-1"></i>Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar - Resumo e Ações -->
        <div class="col-lg-4">
            <!-- Resumo do Pedido -->
            <div class="card total-card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-receipt fa-3x mb-3 opacity-75"></i>
                    <h4 class="mb-4">Resumo do Pedido</h4>
                    
                    <div class="quick-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo count($produtos_cesta); ?></div>
                            <div class="stat-label">Itens</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">R$ <?php echo number_format($total, 2, ',', '.'); ?></div>
                            <div class="stat-label">Total</div>
                        </div>
                    </div>
                    
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    
                    <div class="mt-4">
                        <a href="criar_cesta.php" class="btn btn-light btn-lg w-100 mb-2">
                            <i class="fas fa-edit me-2"></i>Editar Cesta
                        </a>
                        <a href="produtos.php" class="btn btn-outline-light btn-lg w-100">
                            <i class="fas fa-plus me-2"></i>Mais Produtos
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Estatísticas -->
            <div class="card stats-card mb-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-chart-bar me-2"></i>Estatísticas
                    </h6>
                    
                    <?php
                    $preco_mais_caro = 0;
                    $preco_mais_barato = PHP_FLOAT_MAX;
                    
                    foreach ($produtos_cesta as $produto) {
                        if ($produto['preco'] > $preco_mais_caro) {
                            $preco_mais_caro = $produto['preco'];
                        }
                        if ($produto['preco'] < $preco_mais_barato) {
                            $preco_mais_barato = $produto['preco'];
                        }
                    }
                    
                    $valor_medio = count($produtos_cesta) > 0 ? $total / count($produtos_cesta) : 0;
                    ?>
                    
                    <div class="mb-3">
                        <small>Valor médio por item:</small>
                        <div class="fw-bold fs-5">R$ <?php echo number_format($valor_medio, 2, ',', '.'); ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <small>Produto mais caro:</small>
                        <div class="fw-bold fs-5">R$ <?php echo number_format($preco_mais_caro, 2, ',', '.'); ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <small>Produto mais barato:</small>
                        <div class="fw-bold fs-5">R$ <?php echo number_format($preco_mais_barato, 2, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Ações Rápidas -->
            <div class="card actions-card">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Ações Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="produtos.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-box me-2"></i>Ver Produtos
                        </a>
                        <a href="fornecedores.php" class="btn btn-outline-info btn-lg">
                            <i class="fas fa-truck me-2"></i>Ver Fornecedores
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Animação personalizada para os cards
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach((card, index) => {
        // Adicionar delay progressivo baseado na posição
        card.style.animationDelay = (index * 0.1) + 's';
    });
    
    // Efeito de hover nos botões de ação
    const actionButtons = document.querySelectorAll('.actions-card .btn');
    actionButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php require 'footer.php'; ?>