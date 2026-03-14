// Menu Hamburguer Funcional - VERSÃO CORRIGIDA
// Adicione isto ao final de dashboard.php, antes de </body>

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const nav = document.querySelector('nav');
    const navContainer = document.querySelector('.nav-container');
    
    if (!nav || !navContainer) {
        console.error('Nav ou navContainer não encontrados');
        return;
    }
    
    // Criar o botão hamburguer
    const hamburger = document.createElement('button');
    hamburger.className = 'hamburger-btn';
    hamburger.setAttribute('aria-label', 'Menu');
    hamburger.setAttribute('aria-expanded', 'false');
    hamburger.type = 'button';
    hamburger.innerHTML = '☰';
    
    // Inserir hamburguer como primeiro filho do nav
    nav.insertBefore(hamburger, nav.firstChild);
    
    // Função: Fechar menu
    const closeMenu = () => {
        navContainer.classList.remove('active');
        hamburger.setAttribute('aria-expanded', 'false');
    };
    
    // Função: Abrir menu
    const openMenu = () => {
        navContainer.classList.add('active');
        hamburger.setAttribute('aria-expanded', 'true');
    };
    
    // Função: Alternar menu
    const toggleMenu = (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        if (navContainer.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    };
    
    // ========== EVENTOS ==========
    
    // Clique no hamburguer
    hamburger.addEventListener('click', toggleMenu);
    
    // Clique em um link do menu
    const links = navContainer.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            // Não fechar se for link com # (âncora)
            if (!e.target.href.includes('#')) {
                closeMenu();
            }
        });
    });
    
    // Clique em botões (Sair, Tema)
    const buttons = navContainer.querySelectorAll('button');
    buttons.forEach(btn => {
        btn.addEventListener('click', closeMenu);
    });
    
    // Clique fora do menu
    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target) && navContainer.classList.contains('active')) {
            closeMenu();
        }
    });
    
    // Redimensionamento da janela
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            // Em telas maiores, mostrar menu normalmente
            navContainer.classList.remove('active');
        }
    });
    
    // Tecla ESC para fechar
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' || e.keyCode === 27) {
            closeMenu();
        }
    });
});