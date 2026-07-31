<style>
    .top-nav {
        position: sticky; 
        top: 0; 
        z-index: 100; 
        padding: var(--spacing-3) 0;
    }
    
    .nav-container {
        max-width: 1200px; 
        margin: 0 auto; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 0 var(--spacing-4);
    }
    
    .nav-brand {
        display: flex; 
        align-items: center; 
        gap: var(--spacing-2); 
        font-weight: 700; 
        color: var(--text-primary); 
        font-size: 1.125rem;
        letter-spacing: -0.03em;
    }
    
    .nav-brand img {
        height: 28px; 
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }

    .nav-links {
        display: flex; 
        gap: var(--spacing-2);
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: 0.5rem 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
        border-radius: var(--radius-md);
        transition: all 0.2s ease;
        font-size: 0.875rem;
        text-decoration: none;
    }
    
    .nav-link:hover {
        color: var(--text-primary);
        background-color: var(--bg-card-hover);
    }
    
    .nav-link.active {
        color: var(--text-primary);
        background-color: var(--bg-card-hover);
        position: relative;
    }
    
    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        width: 16px;
        height: 2px;
        background-color: var(--accent-color);
        border-radius: 2px;
    }

    .bottom-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 100;
        padding: var(--spacing-2) var(--spacing-4);
        padding-bottom: calc(var(--spacing-2) + env(safe-area-inset-bottom));
        justify-content: space-around;
        align-items: center;
        border-top: 1px solid var(--border-subtle);
    }

    .bottom-nav-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: var(--text-secondary);
        font-size: 0.65rem;
        font-weight: 500;
        text-decoration: none;
        padding: 8px;
        border-radius: var(--radius-md);
    }

    .bottom-nav-link i {
        font-size: 1.25rem;
    }

    .bottom-nav-link.active {
        color: var(--accent-color);
    }

    @media (max-width: 768px) {
        .nav-links {
            display: none !important;
        }
        .bottom-nav {
            display: flex;
        }
        /* Spacer to prevent content from being hidden behind bottom nav */
        body {
            padding-bottom: 80px;
        }
    }
</style>

<!-- DESKTOP NAV -->
<nav class="top-nav glass">
    <div class="nav-container">
        
        <div style="display: flex; align-items: center; gap: var(--spacing-8);">
            <div class="nav-brand">
                <img src="icon/money-bag.png" alt="Logo">
                Financeira
            </div>

            <div class="nav-links">
                <?php $current = basename($_SERVER['PHP_SELF']); ?>
                <a href="dashboard.php" class="nav-link <?php echo $current == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="ph ph-squares-four"></i> Dashboard
                </a>
                <a href="extrato.php" class="nav-link <?php echo $current == 'extrato.php' ? 'active' : ''; ?>">
                    <i class="ph ph-list-dashes"></i> Extrato
                </a>
                <a href="categorias.php" class="nav-link <?php echo $current == 'categorias.php' ? 'active' : ''; ?>">
                    <i class="ph ph-tag"></i> Categorias
                </a>
                <a href="cartao.php" class="nav-link <?php echo $current == 'cartao.php' ? 'active' : ''; ?>">
                    <i class="ph ph-credit-card"></i> Cartões
                </a>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: var(--spacing-2);">
            <button id="theme-toggle" class="btn btn-ghost" title="Alternar Tema" style="padding: 0.5rem; border-radius: var(--radius-full);">
                <i class="ph ph-moon" id="theme-icon" style="font-size: 1.25rem;"></i>
            </button>
            <a href="logout.php" class="btn btn-ghost" style="color: var(--danger-text); padding: 0.5rem; border-radius: var(--radius-full);" title="Sair">
                <i class="ph ph-sign-out" style="font-size: 1.25rem;"></i>
            </a>
        </div>
    </div>
</nav>

<!-- MOBILE BOTTOM NAV -->
<nav class="bottom-nav glass">
    <?php $current = basename($_SERVER['PHP_SELF']); ?>
    <a href="dashboard.php" class="bottom-nav-link <?php echo $current == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="ph <?php echo $current == 'dashboard.php' ? 'ph-squares-four-fill' : 'ph-squares-four'; ?>"></i>
        Resumo
    </a>
    <a href="extrato.php" class="bottom-nav-link <?php echo $current == 'extrato.php' ? 'active' : ''; ?>">
        <i class="ph <?php echo $current == 'extrato.php' ? 'ph-list-dashes-fill' : 'ph-list-dashes'; ?>"></i>
        Extrato
    </a>
    <a href="categorias.php" class="bottom-nav-link <?php echo $current == 'categorias.php' ? 'active' : ''; ?>">
        <i class="ph <?php echo $current == 'categorias.php' ? 'ph-tag-fill' : 'ph-tag'; ?>"></i>
        Tags
    </a>
    <a href="cartao.php" class="bottom-nav-link <?php echo $current == 'cartao.php' ? 'active' : ''; ?>">
        <i class="ph <?php echo $current == 'cartao.php' ? 'ph-credit-card-fill' : 'ph-credit-card'; ?>"></i>
        Cartão
    </a>
</nav>

<script>
    const themeBtn = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const htmlEl = document.documentElement;

    const setAppTheme = (tema) => {
        htmlEl.setAttribute('data-theme', tema);
        localStorage.setItem('theme', tema);
        if (themeIcon) {
            themeIcon.className = tema === 'light' ? 'ph ph-sun' : 'ph ph-moon';
        }
    };

    const initialTheme = localStorage.getItem('theme') || 'dark';
    setAppTheme(initialTheme);

    if (themeBtn) {
        themeBtn.onclick = () => {
            const currentTheme = htmlEl.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setAppTheme(newTheme);
        };
    }
</script>