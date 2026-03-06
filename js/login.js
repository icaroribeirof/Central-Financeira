function switchTab(type) {
    const loginForm = document.getElementById('login-form');
    const regForm = document.getElementById('register-form');
    const tabLogin = document.getElementById('tab-login');
    const tabReg = document.getElementById('tab-reg');

    if (type === 'login') {
        loginForm.style.display = 'block';
        regForm.style.display = 'none';
        tabLogin.classList.add('active');
        tabReg.classList.remove('active');
    } else {
        loginForm.style.display = 'none';
        regForm.style.display = 'block';
        tabReg.classList.add('active');
        tabLogin.classList.remove('active');
    }
}

// Evento de Cadastro
document.getElementById('register-form').onsubmit = async (e) => {
    e.preventDefault();
    const dados = {
        nome: document.getElementById('reg-nome').value,
        email: document.getElementById('reg-email').value,
        senha: document.getElementById('reg-senha').value
    };

    const resp = await fetch('api/auth.php?acao=cadastrar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    });
    const res = await resp.json();
    if (res.sucesso) {
        alert('Cadastro realizado com sucesso! Faça login.');
        switchTab('login');
    } else {
        alert(res.erro);
    }
};

// Evento de Login
document.getElementById('login-form').onsubmit = async (e) => {
    e.preventDefault();
    const dados = {
        email: document.getElementById('login-email').value,
        senha: document.getElementById('login-senha').value
    };

    const resp = await fetch('api/auth.php?acao=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    });
    const res = await resp.json();
    if (res.sucesso) {
        window.location.href = 'dashboard.php';
    } else {
        alert(res.erro);
    }
};