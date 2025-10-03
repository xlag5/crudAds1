<?php
// criar_cesta.php
require 'db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Criar Cesta - Gestão de Produtos";
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
                // Verificar se já existe uma cesta ativa
                $stmt = $pdo->prepare("
                    SELECT id FROM cesta 
                    WHERE usuario_id = ? 
                    ORDER BY data_criacao DESC 
                    LIMIT 1
                ");
                $stmt->execute([$usuario_id]);
                $cesta_existente = $stmt->fetch();
                
                if ($cesta_existente) {
                    // Usar cesta existente
                    $cesta_id = $cesta_existente['id'];
                    
                    // Limpar produtos existentes da cesta
                    $stmt = $pdo->prepare("DELETE FROM cesta_produtos WHERE cesta_id = ?");
                    $stmt->execute([$cesta_id]);
                    
                    $mensagem = "Cesta atualizada com sucesso!";
                } else {
                    // Criar nova cesta
                    $stmt = $pdo->prepare("INSERT INTO cesta (usuario_id) VALUES (?)");
                    $stmt->execute([$usuario_id]);
                    $cesta_id = $pdo->lastInsertId();
                    $mensagem = "Cesta criada com sucesso!";
                }
                
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
                
                $_SESSION['mensagem_cesta'] = $mensagem . " " . count($produtos_validos) . " produtos adicionados.";
                header("Location: visualizar_cesta.php");
                exit();
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensagem_erro = "Erro ao criar/atualizar a cesta: " . $e->getMessage();
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Variáveis CSS */
    :root {
        --primary: #4e73df;
        --success: #1cc88a;
        --info: #36b9cc;
        --warning: #f6c23e;
        --danger: #e74a3b;
        --dark: #5a5c69;
        --light: #f8f9fc;
    }

    body {
        background-color: #f8f9fc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Sidebar Styles */
    .sidebar {
        background: linear-gradient(180deg, var(--primary) 0%, #224abe 100%);
        min-height: 100vh;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .sidebar .nav-link {
        color: rgba(255,255,255,.8);
        padding: 1rem 1.5rem;
        margin: 0.2rem 0;
        border-radius: 0.35rem;
        transition: all 0.3s;
    }

    .sidebar .nav-link:hover {
        color: #fff;
        background: rgba(255,255,255,.1);
        transform: translateX(5px);
    }

    .sidebar .nav-link.active {
        color: #fff;
        background: rgba(255,255,255,.2);
    }

    .sidebar .nav-link i {
        margin-right: 0.5rem;
        width: 20px;
        text-align: center;
    }

    /* Navbar Styles */
    .navbar {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }

    /* Card Styles */
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        transition: all 0.3s;
        margin-bottom: 1.5rem;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
    }

    /* Estilos para os cards de produtos */
    .product-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        border-radius: 0.75rem;
        cursor: pointer;
        height: 100%;
    }

    .product-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary);
        box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.15);
    }

    .product-card.selected {
        border-color: var(--success);
        background-color: #f8fff9;
        box-shadow: 0 0.5rem 1rem rgba(28, 200, 138, 0.15);
    }

    .product-checkbox {
        position: absolute;
        top: 15px;
        right: 15px;
        transform: scale(1.2);
        cursor: pointer;
    }

    .price-tag {
        background: linear-gradient(45deg, var(--success), #17a673);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: bold;
        font-size: 1.1rem;
        text-align: center;
        display: inline-block;
        margin-top: 0.5rem;
    }

    .supplier-badge {
        background-color: var(--info);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        display: inline-block;
        margin-bottom: 0.5rem;
    }

    /* Estado vazio */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #6c757d;
        border-radius: 1rem;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Resumo sticky */
    .sticky-summary {
        position: sticky;
        top: 20px;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        border: none;
    }

    .sticky-summary .card-header {
        background: linear-gradient(45deg, var(--primary), #6610f2);
        color: white;
        border-radius: 1rem 1rem 0 0 !important;
        border: none;
    }

    /* Lista de produtos no resumo */
    #listaProdutos {
        max-height: 200px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    #listaProdutos::-webkit-scrollbar {
        width: 6px;
    }

    #listaProdutos::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    #listaProdutos::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 3px;
    }

    #listaProdutos::-webkit-scrollbar-thumb:hover {
        background: #224abe;
    }

    /* Botões e interações */
    .btn-success {
        background: linear-gradient(45deg, var(--success), #17a673);
        border: none;
        border-radius: 0.75rem;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(28, 200, 138, 0.3);
    }

    .btn-outline-primary {
        border-radius: 0.75rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        transform: translateY(-1px);
    }

    /* Títulos e textos */
    .page-title {
        color: var(--dark);
        font-weight: 700;
        margin-bottom: 0;
    }

    .card-title {
        color: var(--dark);
        font-weight: 600;
    }

    /* Alertas personalizados */
    .alert {
        border: none;
        border-radius: 0.75rem;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(45deg, #d4edda, #c3e6cb);
        color: #155724;
        border-left: 4px solid var(--success);
    }

    .alert-danger {
        background: linear-gradient(45deg, #f8d7da, #f1b0b7);
        color: #721c24;
        border-left: 4px solid var(--danger);
    }

    /* Badges */
    .badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.4rem 0.8rem;
    }

    /* Grid responsivo */
    @media (max-width: 768px) {
        .product-card {
            margin-bottom: 1rem;
        }
        
        .sticky-summary {
            position: static;
            margin-top: 2rem;
        }
        
        .btn-success {
            width: 100%;
        }
    }

    /* Animações */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .product-card {
        animation: fadeIn 0.5s ease-out;
    }

    /* Dica card */
    .dica-card {
        border-left: 4px solid var(--warning);
        background: linear-gradient(45deg, #fff3cd, #ffeaa7);
    }

    .dica-card h6 {
        color: #856404;
        font-weight: 600;
    }

    .dica-card .text-muted {
        color: #8a6d3b !important;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="position-sticky pt-3">
          <div class="text-center mb-4">
            <h4 class="text-white"><i class="fas fa-boxes me-2"></i>Gestão de Produtos</h4>
          </div>
          
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="dashboard_main.php">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="produtos.php">
                <i class="fas fa-box"></i>
                Produtos
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="fornecedores.php">
                <i class="fas fa-truck"></i>
                Fornecedores
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="criar_cesta.php">
                <i class="fas fa-shopping-cart"></i>
                Criar Cesta
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="visualizar_cesta.php">
                <i class="fas fa-eye"></i>
                Ver Cesta
              </a>
            </li>
          </ul>

          <div class="mt-5 p-3">
            <div class="card bg-light">
              <div class="card-body text-center">
                <small class="text-muted">Sistema de Gestão</small>
                <br>
                <small class="text-muted">v1.0</small>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <!-- Main content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
          <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
              <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                      <?php echo strtoupper(substr($_SESSION['usuario_nome'], 0, 1)); ?>
                    </div>
                  </a>
                  <ul class="dropdown-menu">
                    <li><span class="dropdown-item-text">Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                  </ul>
                </li>
              </ul>
            </div>
          </div>
        </nav>

        <!-- Page content -->
        <div class="py-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0">
              <i class="fas fa-shopping-cart me-2 text-primary"></i>Criar Nova Cesta
            </h2>
            <a href="visualizar_cesta.php" class="btn btn-outline-primary">
              <i class="fas fa-eye me-2"></i>Ver Minha Cesta
            </a>
          </div>

          <?php if ($mensagem): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="fas fa-check-circle me-2"></i><?php echo $mensagem; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i><?php echo $mensagem_erro; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <div class="row">
            <!-- Lista de Produtos -->
            <div class="col-lg-8">
              <?php if (empty($produtos)): ?>
                <div class="card empty-state">
                  <div class="card-body">
                    <i class="fas fa-box-open"></i>
                    <h4 class="mt-3">Nenhum produto disponível</h4>
                    <p class="text-muted mb-4">Não há produtos cadastrados no sistema para adicionar à cesta.</p>
                    <a href="produtos.php" class="btn btn-primary btn-lg">
                      <i class="fas fa-plus me-2"></i>Cadastrar Primeiro Produto
                    </a>
                  </div>
                </div>
              <?php else: ?>
                <div class="card">
                  <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                      <i class="fas fa-boxes me-2 text-primary"></i>
                      Selecione os Produtos
                      <span class="badge bg-primary ms-2"><?php echo count($produtos); ?> disponíveis</span>
                    </h5>
                  </div>
                  <div class="card-body">
                    <form id="cestaForm" method="POST">
                      <div class="row g-3">
                        <?php foreach ($produtos as $index => $produto): ?>
                          <div class="col-md-6 col-lg-4">
                            <div class="card product-card h-100 position-relative" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                              <div class="card-body">
                                <input type="checkbox" 
                                       name="produtos[]" 
                                       value="<?php echo $produto['id']; ?>" 
                                       class="form-check-input product-checkbox"
                                       data-preco="<?php echo $produto['preco']; ?>"
                                       data-nome="<?php echo htmlspecialchars($produto['nome']); ?>">
                                
                                <h6 class="card-title mb-2"><?php echo htmlspecialchars($produto['nome']); ?></h6>
                                
                                <div class="mb-2">
                                  <span class="supplier-badge">
                                    <i class="fas fa-truck me-1"></i>
                                    <?php echo htmlspecialchars($produto['fornecedor_nome']); ?>
                                  </span>
                                </div>
                                
                                <div class="price-tag text-center w-100">
                                  R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      
                      <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                          <i class="fas fa-cart-plus me-2"></i>
                          Criar/Atualizar Cesta
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            
            <!-- Resumo da Cesta -->
            <div class="col-lg-4">
              <div class="card sticky-summary">
                <div class="card-header">
                  <h5 class="card-title mb-0">
                    <i class="fas fa-receipt me-2"></i>
                    Resumo da Cesta
                  </h5>
                </div>
                <div class="card-body">
                  <div id="resumoVazio" class="text-center py-4">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Selecione produtos para ver o resumo</p>
                  </div>
                  
                  <div id="resumoConteudo" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="fw-bold">Produtos selecionados:</span>
                      <strong id="totalProdutos" class="text-primary fs-5">0</strong>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="fw-bold">Valor total:</span>
                      <strong id="valorTotal" class="text-success fs-5">R$ 0,00</strong>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3 fw-bold">
                      <i class="fas fa-list me-2"></i>Produtos selecionados:
                    </h6>
                    <div id="listaProdutos" class="mb-3">
                      <!-- Lista de produtos será preenchida via JavaScript -->
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Dica -->
              <div class="card mt-4 dica-card">
                <div class="card-body">
                  <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Dica</h6>
                  <p class="small text-muted mb-0">
                    Se você já tem uma cesta ativa, selecionar novos produtos irá substituir os produtos existentes.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      const checkboxes = document.querySelectorAll('.product-checkbox');
      const resumoVazio = document.getElementById('resumoVazio');
      const resumoConteudo = document.getElementById('resumoConteudo');
      const totalProdutos = document.getElementById('totalProdutos');
      const valorTotal = document.getElementById('valorTotal');
      const listaProdutos = document.getElementById('listaProdutos');
      
      let produtosSelecionados = [];
      let valorTotalCesta = 0;
      
      function atualizarResumo() {
          produtosSelecionados = [];
          valorTotalCesta = 0;
          
          checkboxes.forEach(checkbox => {
              if (checkbox.checked) {
                  const preco = parseFloat(checkbox.dataset.preco);
                  const nome = checkbox.dataset.nome;
                  
                  produtosSelecionados.push({
                      nome: nome,
                      preco: preco
                  });
                  
                  valorTotalCesta += preco;
              }
          });
          
          // Atualizar interface
          if (produtosSelecionados.length > 0) {
              resumoVazio.style.display = 'none';
              resumoConteudo.style.display = 'block';
              
              totalProdutos.textContent = produtosSelecionados.length;
              valorTotal.textContent = 'R$ ' + valorTotalCesta.toFixed(2).replace('.', ',');
              
              // Atualizar lista de produtos
              listaProdutos.innerHTML = '';
              produtosSelecionados.forEach(produto => {
                  const item = document.createElement('div');
                  item.className = 'd-flex justify-content-between align-items-center mb-2 small border-bottom pb-2';
                  item.innerHTML = `
                      <span class="text-truncate" style="max-width: 60%;">${produto.nome}</span>
                      <span class="text-success fw-bold">R$ ${produto.preco.toFixed(2).replace('.', ',')}</span>
                  `;
                  listaProdutos.appendChild(item);
              });
          } else {
              resumoVazio.style.display = 'block';
              resumoConteudo.style.display = 'none';
          }
          
          // Atualizar cards visuais
          checkboxes.forEach(checkbox => {
              const card = checkbox.closest('.product-card');
              if (checkbox.checked) {
                  card.classList.add('selected');
              } else {
                  card.classList.remove('selected');
              }
          });
      }
      
      // Adicionar eventos aos checkboxes
      checkboxes.forEach(checkbox => {
          checkbox.addEventListener('change', atualizarResumo);
          
          // Adicionar clique no card para selecionar
          const card = checkbox.closest('.product-card');
          card.addEventListener('click', function(e) {
              if (e.target !== checkbox) {
                  checkbox.checked = !checkbox.checked;
                  checkbox.dispatchEvent(new Event('change'));
              }
          });
      });
      
      // Validação do formulário
      document.getElementById('cestaForm').addEventListener('submit', function(e) {
          if (produtosSelecionados.length === 0) {
              e.preventDefault();
              Swal.fire({
                  icon: 'warning',
                  title: 'Cesta vazia',
                  text: 'Por favor, selecione pelo menos um produto para a cesta.',
                  confirmButtonColor: '#4e73df'
              });
              return false;
          }
      });
      
      // Inicializar resumo
      atualizarResumo();
  });
  </script>
</body>
</html>