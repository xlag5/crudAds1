<?php
// navbar.php - Componente de navegação para outras páginas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-boxes me-2"></i>Gestão de Produtos
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="produtos.php">
            <i class="fas fa-box me-1"></i>Produtos
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="fornecedores.php">
            <i class="fas fa-truck me-1"></i>Fornecedores
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="visualizar_cesta.php">
            <i class="fas fa-shopping-cart me-1"></i>Minha Cesta
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
          </a>
          <ul class="dropdown-menu">
            <li><span class="dropdown-item-text">Logado como: <strong><?php echo htmlspecialchars($_SESSION['usuario_email']); ?></strong></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>