/**
 * Arquivo de scripts para o sistema de locadora de veículos
 * Versão simples para preparação da fase 2 (back-end)
 */

// Função para atualizar o ano atual no rodapé
document.addEventListener('DOMContentLoaded', function() {
    // Atualizar o ano atual no rodapé
    const anoAtual = document.getElementById('ano-atual');
    if (anoAtual) {
        anoAtual.textContent = new Date().getFullYear();
    }
    
    // Exibir o tipo de usuário na página principal (apenas visual)
    // Este código será substituído pela autenticação real no PHP
    const usernameDisplay = document.getElementById('username-display');
    if (usernameDisplay) {
        const urlParams = new URLSearchParams(window.location.search);
        const perfil = urlParams.get('perfil') || 'admin';
        usernameDisplay.textContent = perfil;
        
        // Configurar visibilidade baseada no perfil (apenas para demonstração)
        if (perfil === 'usuario') {
            document.querySelectorAll('.admin-only').forEach(elem => {
                elem.style.display = 'none';
            });
            
            document.getElementById('secao-adicionar-veiculo').style.display = 'none';
            document.getElementById('secao-calcular').className = 'col-12';
        }
    }
});