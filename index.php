<?php
require_once 'db_connect.php';

// Se já estiver logado, vai direto para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'];
    $senha = $_POST['senha'];

    if ($acao == 'login') {
        $email = $_POST['email']; 
        
        $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data && password_verify($senha, $user_data['senha'])) {
            $_SESSION['usuario_id'] = $user_data['id'];
            $_SESSION['usuario_nome'] = $user_data['nome'];
            header("Location: dashboard.php");
            exit();
        } else {
            $erro = "E-mail ou senha incorretos!";
        }
    } 
    elseif ($acao == 'cadastrar') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $check->execute(['email' => $email]);
            
            if ($check->fetch()) {
                $erro = "Este e-mail já está cadastrado!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :pass)");
                $stmt->execute([
                    'nome'  => $nome,
                    'email' => $email,
                    'pass'  => $senha_hash
                ]);
                $sucesso = "Conta criada com sucesso! Faça login.";
            }
        } catch (PDOException $e) {
            $erro = "Erro ao criar conta. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central Financeira - Acesso</title>
    <!-- New CSS -->
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/components.css">
    
    <link rel="shortcut icon" href="icon/money-bag.png">
    <link rel="apple-touch-icon" href="icon/icon.png">
    <meta name="apple-mobile-web-app-title" content="Central Financeira">
    
    <!-- Phosphor Icons for a more premium look -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .auth-layout {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-body);
            padding: var(--spacing-4);
            /* Optional background pattern for premium feel */
            background-image: radial-gradient(var(--border-subtle) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        
        .auth-card {
            width: 100%;
            max-width: 400px;
            animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            background-color: var(--bg-card);
        }

        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }

        .auth-logo {
            width: 48px;
            height: 48px;
            margin-bottom: var(--spacing-4);
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }
        
        .auth-logo:hover {
            transform: scale(1.05);
        }

        .auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.03em;
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-top: var(--spacing-1);
        }

        .tab-system {
            display: flex;
            background: var(--bg-body);
            padding: 4px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-strong);
            margin-bottom: var(--spacing-6);
        }

        .tab-btn {
            flex: 1;
            padding: 8px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab-btn.active {
            background: var(--bg-card);
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
        }

        .alert {
            padding: var(--spacing-3) var(--spacing-4);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-4);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }

        .alert-error {
            background-color: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .theme-toggle-fixed {
            position: fixed;
            top: var(--spacing-4);
            right: var(--spacing-4);
            background: var(--bg-card);
            border: 1px solid var(--border-strong);
            color: var(--text-secondary);
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        .theme-toggle-fixed:hover {
            color: var(--text-primary);
            transform: scale(1.1);
            border-color: var(--border-focus);
        }
        
        .btn-full {
            width: 100%;
            margin-top: var(--spacing-2);
            padding: 0.75rem 1rem;
        }
    </style>
</head>

<body>
    <button id="theme-toggle" class="theme-toggle-fixed" title="Alternar Tema">
        <i class="ph ph-moon" id="theme-icon" style="font-size: 1.25rem;"></i>
    </button>

    <div class="auth-layout">
        <div class="card auth-card">
            <div class="auth-header">
                <img src="icon/money-bag.png" alt="Logo" class="auth-logo">
                <h1 class="auth-title">Central Financeira</h1>
                <p class="auth-subtitle">Controle inteligente para o seu dinheiro</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-error animate-fade-in">
                    <i class="ph ph-warning-circle" style="font-size: 1.125rem;"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success animate-fade-in">
                    <i class="ph ph-check-circle" style="font-size: 1.125rem;"></i>
                    <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>

            <div class="tab-system">
                <button id="tab-login" class="tab-btn active" onclick="switchTab('login')">Entrar</button>
                <button id="tab-register" class="tab-btn" onclick="switchTab('register')">Cadastrar</button>
            </div>

            <form id="loginForm" class="auth-form animate-fade-in" method="POST">
                <input type="hidden" name="acao" value="login">
                
                <div class="input-group">
                    <label class="label">E-mail</label>
                    <div class="input-wrapper">
                        <i class="ph ph-envelope-simple"></i>
                        <input type="email" name="email" class="input" placeholder="seu@email.com" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label class="label">Senha</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="senha" class="input" placeholder="Sua senha" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Acessar Plataforma</button>
            </form>

            <form id="registerForm" class="auth-form animate-fade-in" method="POST" style="display: none;">
                <input type="hidden" name="acao" value="cadastrar">
                
                <div class="input-group">
                    <label class="label">Nome</label>
                    <div class="input-wrapper">
                        <i class="ph ph-user"></i>
                        <input type="text" name="nome" class="input" placeholder="Como quer ser chamado?" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label class="label">E-mail</label>
                    <div class="input-wrapper">
                        <i class="ph ph-envelope-simple"></i>
                        <input type="email" name="email" class="input" placeholder="seu@email.com" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label class="label">Nova Senha</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="senha" class="input" placeholder="Crie uma senha forte" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full" style="background-color: var(--success); box-shadow: none;">Criar Conta</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(type) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const tabLogin = document.getElementById('tab-login');
            const tabRegister = document.getElementById('tab-register');
            
            if (type === 'login') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                tabLogin.classList.add('active');
                tabRegister.classList.remove('active');
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                tabLogin.classList.remove('active');
                tabRegister.classList.add('active');
            }
        }

        // Theme Toggle Logic
        const btn = document.getElementById('theme-toggle');
        const icon = document.getElementById('theme-icon');
        const html = document.documentElement;

        const aplicarTema = (tema) => {
            html.setAttribute('data-theme', tema);
            localStorage.setItem('theme', tema);
            if (icon) {
                icon.className = tema === 'light' ? 'ph ph-sun' : 'ph ph-moon';
            }
        };

        const temaInicial = localStorage.getItem('theme') || 'dark';
        aplicarTema(temaInicial);

        btn.onclick = () => {
            const temaAtual = html.getAttribute('data-theme');
            const novoTema = temaAtual === 'light' ? 'dark' : 'light';
            aplicarTema(novoTema);
        };
    </script>
</body>
</html>
