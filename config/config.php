<?php
// Arquivo de configuração com constantes do sistema

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Altere para seu usuário do MySQL
define('DB_PASS', 'Senai@118');    // Altere para sua senha do MySQL
define('DB_NAME', 'locadora_db');  // Nome do banco de dados

// Constantes de diárias
define('DIARIA_CARRO', 100.00);
define('DIARIA_MOTO', 50.00);

// Definir senhas padrão para facilitar debug - use apenas em desenvolvimento
define('ADMIN_PASSWORD', 'admin123');
define('USER_PASSWORD', 'user123');

/**
 * Função para criar a conexão com o banco de dados
 * @return PDO Objeto de conexão PDO
 */
function getConnection() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // Em produção, evite mostrar detalhes do erro
        die('Erro de conexão com o banco de dados: ' . $e->getMessage());
    }
}

/**
 * Função para inicializar o banco de dados se não existir
 */
function initDatabase() {
    try {
        // Primeiro cria o banco se não existir
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Cria o banco de dados se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        
        // Cria a tabela de usuários se não existir
        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            perfil VARCHAR(20) NOT NULL
        ) ENGINE=InnoDB");
        
        // Cria a tabela de veículos se não existir
        $pdo->exec("CREATE TABLE IF NOT EXISTS veiculos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo VARCHAR(10) NOT NULL,
            modelo VARCHAR(100) NOT NULL,
            placa VARCHAR(10) NOT NULL UNIQUE,
            disponivel BOOLEAN NOT NULL DEFAULT TRUE
        ) ENGINE=InnoDB");
        
        // Verifica se existem usuários cadastrados
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
        $count = $stmt->fetchColumn();
        
        // Se não existirem usuários, insere os padrões
        if ($count == 0) {
            // Pré-cria os hashes para garantir consistência
            $admin_hash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
            $user_hash = password_hash(USER_PASSWORD, PASSWORD_DEFAULT);
            
            // Log para debug - remova em produção
            error_log("Criando usuários padrão: admin/usuario");
            
            $pdo->prepare("INSERT INTO usuarios (username, password, perfil) VALUES 
                (?, ?, ?), (?, ?, ?)")
                ->execute([
                    'admin', $admin_hash, 'admin',
                    'usuario', $user_hash, 'usuario'
                ]);
            
            // Também insere em uma tabela temporária para verificação
            // Esta tabela serve apenas para depuração e pode ser removida em produção
            $pdo->exec("CREATE TABLE IF NOT EXISTS senha_debug (
                username VARCHAR(50) NOT NULL,
                senha_texto VARCHAR(50) NOT NULL,
                hash VARCHAR(255) NOT NULL
            )");
            
            $pdo->prepare("INSERT INTO senha_debug (username, senha_texto, hash) VALUES 
                (?, ?, ?), (?, ?, ?)")
                ->execute([
                    'admin', ADMIN_PASSWORD, $admin_hash,
                    'usuario', USER_PASSWORD, $user_hash
                ]);
        }
        
        // Verifica se existem veículos cadastrados
        $stmt = $pdo->query("SELECT COUNT(*) FROM veiculos");
        $count = $stmt->fetchColumn();
        
        // Se não existirem veículos, insere os padrões
        if ($count == 0) {
            $pdo->exec("INSERT INTO veiculos (tipo, modelo, placa, disponivel) VALUES 
                ('Carro', 'Sandero', 'FMA-6680', FALSE),
                ('Moto', 'Ninja', 'FMA-6600', TRUE)");
        }
        
    } catch (PDOException $e) {
        die('Erro ao inicializar o banco de dados: ' . $e->getMessage());
    }
}

// Inicializa o banco de dados quando o arquivo de configuração é carregado
initDatabase();