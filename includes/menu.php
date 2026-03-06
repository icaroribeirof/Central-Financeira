<!--

<nav>
    <div class="nav-container">
        <?php $pagina_atual = basename($_SERVER['PHP_SELF']); ?>
        
        <a href="dashboard.php" class="<?php echo $pagina_atual == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="extrato.php" class="<?php echo $pagina_atual == 'extrato.php' ? 'active' : ''; ?>">Extrato</a>
        <a href="categorias.php" class="<?php echo $pagina_atual == 'categorias.php' ? 'active' : ''; ?>">Categorias</a>
        <a href="cartao.php" class="<?php echo $pagina_atual == 'cartao.php' ? 'active' : ''; ?>">Cartões</a>
        
        <a href="logout.php" style="color: #e74c3c; font-weight: bold; margin-left: auto;">Sair</a>

        <button id="theme-toggle">☀️ Modo Claro</button>
    </div>
</nav>

-->

<nav>
    <div class="nav-container">
        <div class="nav-links">
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="extrato.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'extrato.php' ? 'active' : ''; ?>">Extrato</a>
            <a href="categorias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'active' : ''; ?>">Categorias</a>
            <a href="cartao.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'cartao.php' ? 'active' : ''; ?>">Cartões</a>
        </div>
        <div class="nav-actions">
            <a href="logout.php" class="btn-sair" style="color: #ff4d4d; text-decoration: none; font-weight: bold; margin-right: 15px;">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
            <button id="theme-toggle">☀️ Modo Claro</button>
        </div>
    </div>
</nav>