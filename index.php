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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Central Financeira</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="icon/money-bag.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="login-body">
    <button id="theme-toggle" class="theme-switcher-btn">
        <span>🌙 Modo Escuro</span>
    </button>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="icon/money-bag.png" alt="Logo" class="login-logo">
                <h1>Central Financeira</h1>
                
                <?php if ($erro): ?>
                    <p style="color: #ff4d4d; font-size: 0.8rem; margin-top: 10px;"><?php echo $erro; ?></p>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                    <p style="color: #10b981; font-size: 0.8rem; margin-top: 10px;"><?php echo $sucesso; ?></p>
                <?php endif; ?>

                <div class="tab-system">
                    <button id="tab-login" class="tab-btn active" onclick="switchTab('login')">Entrar</button>
                    <button id="tab-register" class="tab-btn" onclick="switchTab('register')">Cadastrar</button>
                </div>
            </div>

            <form id="loginForm" class="auth-form" method="POST">
                <input type="hidden" name="acao" value="login">
                <div class="input-group">
                    <label>E-mail</label>
                    <div class="input-wrapper">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" placeholder="seu@email.com" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Senha</label>
                    <div class="input-wrapper">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>
                <button type="submit" class="btn-login">Acessar Sistema</button>
            </form>

            <form id="registerForm" class="auth-form" method="POST" style="display: none;">
                <input type="hidden" name="acao" value="cadastrar">
                <div class="input-group">
                    <label>Nome Completo</label>
                    <div class="input-wrapper">
                        <i class="fa fa-user"></i>
                        <input type="text" name="nome" placeholder="Como quer ser chamado?" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>E-mail</label>
                    <div class="input-wrapper">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" placeholder="seu@email.com" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Nova Senha</label>
                    <div class="input-wrapper">
                        <i class="fa fa-key"></i>
                        <input type="password" name="senha" placeholder="Crie uma senha" required>
                    </div>
                </div>
                <button type="submit" class="btn-login" style="background-color: #10b981;">Criar Conta</button>
            </form>
        </div>
    </div>

    <script>
        // Funcionalidade de Troca de Abas
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

        // Funcionalidade de Alternância de Tema
        const btn = document.getElementById('theme-toggle');
        const html = document.documentElement;

        const aplicarTema = (tema) => {
            html.setAttribute('data-theme', tema);
            localStorage.setItem('theme', tema);
            if (btn) {
                btn.innerHTML = tema === 'light' ? '☀️ Modo Claro' : '🌙 Modo Escuro';
            }
        };

        // Inicia com o tema salvo ou padrão escuro
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
