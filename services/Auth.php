<?php
namespace Services;

/**
 * Classe que gerencia a autenticação de usuários
 */
class Auth {
    /**
     * Conexão com o banco de dados
     * @var \PDO
     */
    private \PDO $db;
    
    /**
     * Construtor da classe Auth
     */
    public function __construct() {
        $this->db = getConnection();
    }
    
    /**
     * Realiza o login do usuário
     * 
     * @param string $username Nome de usuário
     * @param string $password Senha do usuário
     * @return bool Verdadeiro se o login for bem-sucedido
     */
    public function login(string $username, string $password): bool {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['auth'] = [
                'logado' => true,
                'username' => $username,
                'perfil' => $usuario['perfil']
            ];
            return true;
        }
        return false;
    }
    
    /**
     * Encerra a sessão do usuário
     */
    public function logout(): void {
        session_destroy();
    }
    
    /**
     * Verifica se o usuário está logado
     * 
     * @return bool Status do login
     */
    public static function verificarLogin(): bool {
        return isset($_SESSION['auth']) && $_SESSION['auth']['logado'] === true;
    }
    
    /**
     * Verifica se o usuário tem determinado perfil
     * 
     * @param string $perfil Perfil a ser verificado
     * @return bool Verdadeiro se o usuário tem o perfil
     */
    public static function isPerfil(string $perfil): bool {
        return isset($_SESSION['auth']) && $_SESSION['auth']['perfil'] === $perfil;
    }
    
    /**
     * Verifica se o usuário tem perfil de administrador
     * 
     * @return bool Verdadeiro se for administrador
     */
    public static function isAdmin(): bool {
        return self::isPerfil('admin');
    }
    
    /**
     * Obtém as informações do usuário logado
     * 
     * @return array|null Dados do usuário ou null se não estiver logado
     */
    public static function getUsuario(): ?array {
        return $_SESSION['auth'] ?? null;
    }
    
    /**
     * Verifica se o usuário tem permissão para uma ação específica
     * baseado em uma matriz de permissões por perfil
     * 
     * @param string $acao A ação a ser verificada
     * @return bool Verdadeiro se o usuário tem permissão
     */
    public static function temPermissao(string $acao): bool {
        $usuario = self::getUsuario();
        if (!$usuario) {
            return false;
        }
        
        // Matriz de permissões por perfil
        $permissoes = [
            'admin' => [
                'visualizar' => true,
                'adicionar' => true,
                'alugar' => true,
                'devolver' => true,
                'deletar' => true,
                'calcular' => true
            ],
            'usuario' => [
                'visualizar' => true,
                'adicionar' => false,
                'alugar' => false,
                'devolver' => false,
                'deletar' => false,
                'calcular' => true
            ]
            // Pode-se adicionar facilmente novos perfis, como:
            // 'gerente' => [
            //     'visualizar' => true,
            //     'adicionar' => true,
            //     'alugar' => true,
            //     'devolver' => true,
            //     'deletar' => false,
            //     'calcular' => true
            // ]
        ];
        
        // Verifica se o perfil e a ação existem na matriz
        if (!isset($permissoes[$usuario['perfil']]) || !isset($permissoes[$usuario['perfil']][$acao])) {
            return false;
        }
        
        return $permissoes[$usuario['perfil']][$acao];
    }
}