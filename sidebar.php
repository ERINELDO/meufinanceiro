<!-- includes/sidebar.php -->
<nav id="sidebar" class="sidebar d-md-block bg-dark">
  <div class="sidebar-header d-flex align-items-center px-3 mb-4">
    <div class="pro-sidebar-logo d-flex align-items-center">
      <div class="logo-box">
        F
      </div>
      <h5 class="text-white m-0">Financeiro</h5>
    </div>
    <button id="btn-collapse" class="btn btn-sm btn-link text-white ms-auto d-none d-md-inline" aria-label="Colapsar menu" title="Colapsar menu">
      <i class="ri-arrow-left-s-line fs-5"></i>
    </button>
  </div>

  <ul class="menu list-unstyled px-2">
    <li class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
      <a href="dashboard.php" class="menu-link">
        <span class="menu-icon"><i class="ri-dashboard-2-line"></i></span>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <li class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'lancamento.php' ? 'active' : '' ?>">
      <a href="lancamento.php" class="menu-link">
        <span class="menu-icon"><i class="ri-file-edit-line"></i></span>
        <span class="menu-title">Lançamentos</span>
      </a>
    </li>
    <li class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'listagem.php' ? 'active' : '' ?>">
      <a href="listagem.php" class="menu-link">
        <span class="menu-icon"><i class="ri-list-check"></i></span>
        <span class="menu-title">Listagem</span>
      </a>
    </li>
    <li class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'tarefas.php' ? 'active' : '' ?>">
      <a href="tarefas.php" class="menu-link">
        <span class="menu-icon"><i class="ri-task-line"></i></span>
        <span class="menu-title">Tarefas</span>
      </a>
    </li>
  <ul class="menu-sub">
    <li class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'caixa.php' ? 'active' : '' ?>">
      <a href="caixa.php" class="menu-link">
        <span class="menu-icon"><i class="ri-wallet-3-line"></i></span>
        <span class="menu-title">Carteira</span>
      </a>
    </li>
    <li class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'historicocaixa.php' ? 'active' : '' ?>">
      <a href="historicocaixa.php" class="menu-link">
        <span class="menu-icon"><i class="ri-exchange-dollar-line"></i></span>
        <span class="menu-title">Saques da Carteira</span>
      </a>
    </li>
  </ul>
</li>
   </li>
    <li class="menu-item mt-4">
      <a href="logout.php" class="menu-link">
        <span class="menu-icon"><i class="ri-logout-box-r-line"></i></span>
        <span class="menu-title">Sair</span>
      </a>
    </li>
  </ul>

  <button id="btn-toggle" class="sidebar-toggle-btn d-md-none" aria-label="Abrir menu">
    <i class="ri-menu-line"></i>
  </button>
</nav>
