<?php
// header.php - Header unificado para todas as páginas

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Definir título da página se não estiver definido
if (!isset($page_title)) {
    $page_title = "Gestão de Produtos";
}

// Definir nome do usuário com fallback
$usuario_nome = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário';
$usuario_email = isset($_SESSION['usuario_email']) ? $_SESSION['usuario_email'] : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?php echo $page_title; ?></title>
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
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    /* Sidebar Styles */
    .sidebar {
        background: linear-gradient(180deg, var(--primary) 0%, #224abe 100%);
        min-height: 100vh;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        position: fixed;
        width: 250px;
        z-index: 1000;
        transition: all 0.3s;
    }

    .sidebar .nav-link {
        color: rgba(255,255,255,.8);
        padding: 1rem 1.5rem;
        margin: 0.2rem 0.5rem;
        border-radius: 0.35rem;
        transition: all 0.3s;
        text-decoration: none;
        display: block;
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
    .top-navbar {
        background: white;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        position: fixed;
        top: 0;
        right: 0;
        left: 250px;
        z-index: 999;
        height: 70px;
        transition: all 0.3s;
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
        border: 2px solid white;
    }

    /* Main Content */
    .main-content {
        margin-left: 250px;
        margin-top: 70px;
        padding: 2rem;
        min-height: calc(100vh - 70px);
        transition: all 0.3s;
    }

    /* Títulos */
    .page-title {
        color: var(--dark);
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    /* Cards */
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

    /* Botões */
    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-primary {
        background: linear-gradient(45deg, var(--primary), #6610f2);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.3);
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: 250px;
        }
        
        .sidebar.mobile-show {
            transform: translateX(0);
        }
        
        .top-navbar {
            left: 0;
        }
        
        .main-content {
            margin-left: 0;
        }
        
        .sidebar-toggle {
            display: block !important;
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

    .fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    /* Overlay para mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }

    .sidebar-overlay.show {
        display: block;
    }
  </style>
</head>
<body>
  <!-- Overlay para mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar">
    <div class="position-sticky pt-3">
      <div class="text-center mb-4 mt-3">
        <h4 class="text-white">
          <i class="fas fa-boxes me-2"></i>
          Gestão de Produtos
        </h4>
        <small class="text-white-50">Sistema Completo</small>
      </div>
      
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>" href="produtos.php">
            <i class="fas fa-box"></i>
            Produtos
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'fornecedores.php' ? 'active' : ''; ?>" href="fornecedores.php">
            <i class="fas fa-truck"></i>
            Fornecedores
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'criar_cesta.php' ? 'active' : ''; ?>" href="criar_cesta.php">
            <i class="fas fa-shopping-cart"></i>
            Criar Cesta
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'visualizar_cesta.php' ? 'active' : ''; ?>" href="visualizar_cesta.php">
            <i class="fas fa-eye"></i>
            Ver Cesta
          </a>
        </li>
      </ul>

      <div class="mt-5 p-3">
        <div class="card bg-light">
          <div class="card-body text-center">
            <small class="text-muted">Usuário: <?php echo htmlspecialchars($usuario_nome); ?></small>
            <br>
            <small class="text-muted">v2.0</small>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Top Navbar -->
  <nav class="top-navbar navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <button class="navbar-toggler sidebar-toggle d-lg-none" type="button" id="sidebarToggle">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="navbar-brand d-none d-lg-block">
        <h5 class="mb-0 text-dark"><?php echo $page_title; ?></h5>
      </div>
      
      <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
              <div class="user-avatar d-inline-flex">
                <?php echo strtoupper(substr($usuario_nome, 0, 1)); ?>
              </div>
              <span class="ms-2 d-none d-md-inline"><?php echo htmlspecialchars($usuario_nome); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <span class="dropdown-item-text">
                  <small>Logado como:</small><br>
                  <strong><?php echo htmlspecialchars($usuario_email); ?></strong>
                </span>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="dashboard.php">
                  <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="logout.php">
                  <i class="fas fa-sign-out-alt me-2"></i>Sair
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="main-content" id="mainContent">