<?php
// dashboard.php
$page_title = "Dashboard - Gestão de Produtos";
require 'header.php';

require 'db.php';
$pdo = Database::getConnection();
$usuario_id = $_SESSION['usuario_id'];

$estatisticas = [
    'total_produtos' => 0,
    'total_fornecedores' => 0,
    'total_cestas' => 0,
    'produtos_cesta_atual' => 0,
    'valor_total_cesta' => 0
];

try {
    // Total de produtos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos");
    $estatisticas['total_produtos'] = $stmt->fetch()['total'];

    // Total de fornecedores
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM fornecedores");
    $estatisticas['total_fornecedores'] = $stmt->fetch()['total'];

    // Total de cestas do usuário
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cesta WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $estatisticas['total_cestas'] = $stmt->fetch()['total'];

    // Cesta atual
    $stmt = $pdo->prepare("
        SELECT c.id 
        FROM cesta c 
        WHERE c.usuario_id = ? 
        ORDER BY c.data_criacao DESC 
        LIMIT 1
    ");
    $stmt->execute([$usuario_id]);
    $cesta_atual = $stmt->fetch();

    if ($cesta_atual) {
        // Produtos na cesta atual
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total, COALESCE(SUM(p.preco), 0) as valor_total
            FROM cesta_produtos cp
            JOIN produtos p ON cp.produto_id = p.id
            WHERE cp.cesta_id = ?
        ");
        $stmt->execute([$cesta_atual['id']]);
        $cesta_info = $stmt->fetch();
        
        $estatisticas['produtos_cesta_atual'] = $cesta_info['total'];
        $estatisticas['valor_total_cesta'] = $cesta_info['valor_total'];
    }

    // Últimos produtos adicionados
    $stmt = $pdo->query("
        SELECT p.nome, p.preco, f.nome as fornecedor_nome, p.data_criacao
        FROM produtos p
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
        ORDER BY p.data_criacao DESC
        LIMIT 5
    ");
    $ultimos_produtos = $stmt->fetchAll();

} catch (PDOException $e) {
    $erro_estatisticas = "Erro ao carregar estatísticas: " . $e->getMessage();
}
?>

<style>
/* Estilos específicos para o dashboard */
.welcome-card {
    background: linear-gradient(45deg, var(--primary), #6610f2);
    color: white;
    border-radius: 1rem;
    border: none;
}

.stat-card {
    border-left: 0.25rem solid transparent;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.card-border-left-primary { border-left-color: var(--primary); }
.card-border-left-success { border-left-color: var(--success); }
.card-border-left-info { border-left-color: var(--info); }
.card-border-left-warning { border-left-color: var(--warning); }

.stat-card .text-xs {
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: 600;
}

.quick-actions .btn {
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    margin: 0.25rem;
    transition: all 0.3s ease;
}

.quick-actions .btn:hover {
    transform: translateY(-2px);
}

.recent-products .list-group-item {
    border: none;
    border-left: 3px solid transparent;
    margin-bottom: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.recent-products .list-group-item:hover {
    border-left-color: var(--primary);
    background-color: var(--light);
}

.price-badge {
    background: linear-gradient(45deg, var(--success), #17a673);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.empty-dashboard {
    padding: 3rem 1rem;
    text-align: center;
    color: #6c757d;
}

.empty-dashboard i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>

<!-- Welcome Card -->
<div class="card welcome-card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="card-title">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>! 👋</h2>
                <p class="card-text mb-0">Bem-vindo ao sistema de gestão de produtos. Aqui está um resumo das suas atividades.</p>
            </div>
            <div class="col-md-4 text-end">
                <i class="fas fa-chart-line fa-4x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card card-border-left-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total de Produtos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estatisticas['total_produtos']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card card-border-left-success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Fornecedores</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estatisticas['total_fornecedores']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card card-border-left-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cestas Criadas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estatisticas['total_cestas']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card card-border-left-warning">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Valor Cesta Atual</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format($estatisticas['valor_total_cesta'], 2, ',', '.'); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Quick Actions -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2 text-warning"></i>Ações Rápidas
                </h5>
            </div>
            <div class="card-body quick-actions">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <a href="produtos.php" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Novo Produto
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="fornecedores.php" class="btn btn-success w-100">
                            <i class="fas fa-truck me-2"></i>Novo Fornecedor
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="criar_cesta.php" class="btn btn-info w-100">
                            <i class="fas fa-cart-plus me-2"></i>Criar Cesta
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="visualizar_cesta.php" class="btn btn-warning w-100">
                            <i class="fas fa-eye me-2"></i>Ver Cesta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cesta Atual -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2 text-success"></i>Cesta Atual
                </h5>
            </div>
            <div class="card-body text-center">
                <?php if ($estatisticas['produtos_cesta_atual'] > 0): ?>
                    <div class="display-4 text-success"><?php echo $estatisticas['produtos_cesta_atual']; ?></div>
                    <p class="text-muted">produtos na cesta</p>
                    <p class="h5 text-dark">Total: R$ <?php echo number_format($estatisticas['valor_total_cesta'], 2, ',', '.'); ?></p>
                    <a href="visualizar_cesta.php" class="btn btn-success mt-2">Ver Detalhes</a>
                <?php else: ?>
                    <div class="empty-dashboard">
                        <i class="fas fa-shopping-cart"></i>
                        <p class="mt-3">Nenhum produto na cesta atual</p>
                        <a href="criar_cesta.php" class="btn btn-primary">Criar Cesta</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2 text-info"></i>Produtos Recentes
                </h5>
                <a href="produtos.php" class="btn btn-sm btn-primary">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (!empty($ultimos_produtos)): ?>
                    <div class="list-group list-group-flush recent-products">
                        <?php foreach ($ultimos_produtos as $produto): ?>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($produto['nome']); ?></h6>
                                        <small class="text-muted">Fornecedor: <?php echo htmlspecialchars($produto['fornecedor_nome']); ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="price-badge">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($produto['data_criacao'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-dashboard">
                        <i class="fas fa-box-open"></i>
                        <p class="mt-3">Nenhum produto cadastrado ainda</p>
                        <a href="produtos.php" class="btn btn-primary">Cadastrar Primeiro Produto</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>