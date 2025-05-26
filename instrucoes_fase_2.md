# Roteiro de Implementação - Fase 2: Back-end da Locadora de Veículos

Este roteiro guiará a transição do front-end (Fase 1) para a implementação completa do back-end com PHP e MySQL (Fase 2). Nesta etapa, implementaremos a lógica de negócios, autenticação, sistema de permissões e persistência de dados conforme a arquitetura orientada a objetos.

## Estrutura do Projeto Completo

Antes de começar, vamos entender a estrutura completa do projeto:

```
locadora-veiculos/
├── config/
│   └── config.php             # Configurações e constantes do sistema
├── models/
│   ├── Veiculo.php            # Classe abstrata + interface Locavel
│   ├── Carro.php              # Implementação de Carro
│   └── Moto.php               # Implementação de Moto
├── public/
│   └── login.php              # Página de login (controlador + view)
├── services/
│   ├── Auth.php               # Serviço de autenticação e permissões
│   └── Locadora.php           # Serviço de gerenciamento de veículos
├── views/
│   └── template.php           # Template principal do sistema
├── css/
│   └── styles.css             # Estilos do front-end (da fase 1)
├── js/
│   └── scripts.js             # JavaScript básico (da fase 1)
├── vendor/                    # Pasta gerada pelo Composer
├── composer.json              # Configuração do Composer
├── composer.lock              # Lock-file do Composer
└── index.php                  # Controlador principal
```

## Etapa 1: Configuração do Ambiente

### 1.1 Instalar o Ambiente de Desenvolvimento
- Servidor web (Apache/Nginx) com PHP 7.4+
- MySQL 5.7+ ou MariaDB 10.3+
- Composer para gerenciamento de dependências

### 1.2 Preparar a Estrutura de Pastas
1. Crie a estrutura de diretórios conforme mostrado acima
2. Transfira os arquivos CSS e JS da Fase 1 para as pastas correspondentes

### 1.3 Configurar o Composer
1. Crie um arquivo `composer.json` na raiz do projeto:

```json
{
    "name": "locadora/veiculos",
    "description": "Sistema de Locadora de Veículos com Persistência MySQL",
    "type": "project",
    "autoload": {
        "psr-4": {
            "Models\\": "models/",
            "Services\\": "services/"
        }
    },
    "require": {
        "php": ">=7.4",
        "ext-pdo": "*"
    }
}
```

2. Execute `composer install` na raiz do projeto

## Etapa 2: Implementação do Banco de Dados

### 2.1 Criar o Arquivo de Configuração
Crie o arquivo `config/config.php`:

```php
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
            $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $user_hash = password_hash('user123', PASSWORD_DEFAULT);
            
            $pdo->prepare("INSERT INTO usuarios (username, password, perfil) VALUES 
                (?, ?, ?), (?, ?, ?)")
                ->execute([
                    'admin', $admin_hash, 'admin',
                    'usuario', $user_hash, 'usuario'
                ]);
        }
        
        // Verifica se existem veículos cadastrados
        $stmt = $pdo->query("SELECT COUNT(*) FROM veiculos");
        $count = $stmt->fetchColumn();
        
        // Se não existirem veículos, insere os padrões
        if ($count == 0) {
            $pdo->exec("INSERT INTO veiculos (tipo, modelo, placa, disponivel) VALUES 
                ('Carro', 'Sandero', 'FMA-6680', FALSE),
                ('Moto', 'Ninja', 'FMA-6600', TRUE),
                ('Carro', 'Onix', 'ABC-1234', TRUE),
                ('Moto', 'Honda CB 500', 'DEF-5678', TRUE)");
        }
        
    } catch (PDOException $e) {
        die('Erro ao inicializar o banco de dados: ' . $e->getMessage());
    }
}

// Inicializa o banco de dados quando o arquivo de configuração é carregado
initDatabase();
```

## Etapa 3: Implementação dos Modelos

### 3.1 Criar o Arquivo Veiculo.php
Crie o arquivo `models/Veiculo.php`:

```php
<?php
namespace Models;

/**
 * Interface Locavel incorporada diretamente no arquivo.
 * Define os métodos necessários para um veículo ser locável
 */
interface Locavel {
    public function alugar(): string;
    public function devolver(): string;
    public function isDisponivel(): bool;
}

/**
 * Classe abstrata base para todos os tipos de veículos
 */
abstract class Veiculo {
    protected string $modelo;
    protected string $placa;
    protected bool $disponivel;
    protected ?int $id = null;

    /**
     * Construtor da classe Veiculo
     * 
     * @param string $modelo Nome/modelo do veículo
     * @param string $placa Placa do veículo
     * @param bool $disponivel Status de disponibilidade
     * @param int|null $id ID no banco de dados (opcional)
     */
    public function __construct(string $modelo, string $placa, bool $disponivel = true, ?int $id = null) {
        $this->modelo = $modelo;
        $this->placa = $placa;
        $this->disponivel = $disponivel;
        $this->id = $id;
    }

    /**
     * Calcula o valor do aluguel baseado na quantidade de dias
     * Método abstrato a ser implementado pelas classes filhas
     * 
     * @param int $dias Quantidade de dias de aluguel
     * @return float Valor total do aluguel
     */
    abstract public function calcularAluguel(int $dias): float;

    /**
     * Verifica se o veículo está disponível para aluguel
     * 
     * @return bool Status de disponibilidade
     */
    public function isDisponivel(): bool {
        return $this->disponivel;
    }

    /**
     * Obtém o modelo do veículo
     * 
     * @return string Modelo do veículo
     */
    public function getModelo(): string {
        return $this->modelo;
    }

    /**
     * Obtém a placa do veículo
     * 
     * @return string Placa do veículo
     */
    public function getPlaca(): string {
        return $this->placa;
    }

    /**
     * Obtém o ID do veículo no banco de dados
     * 
     * @return int|null ID do veículo
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * Define o status de disponibilidade do veículo
     * 
     * @param bool $disponivel Status de disponibilidade
     */
    public function setDisponivel(bool $disponivel): void {
        $this->disponivel = $disponivel;
    }

    /**
     * Define o ID do veículo (usado ao recuperar do banco)
     * 
     * @param int $id ID do veículo no banco
     */
    public function setId(int $id): void {
        $this->id = $id;
    }
}
```

### 3.2 Criar o Arquivo Carro.php
Crie o arquivo `models/Carro.php`:

```php
<?php
namespace Models;

/**
 * Classe que representa um Carro no sistema
 * Implementa a interface Locavel definida em Veiculo.php
 */
class Carro extends Veiculo implements Locavel {
    /**
     * Calcula o valor do aluguel para o carro
     * 
     * @param int $dias Quantidade de dias de aluguel
     * @return float Valor total do aluguel
     */
    public function calcularAluguel(int $dias): float {
        return $dias * DIARIA_CARRO;
    }

    /**
     * Método para alugar o carro
     * 
     * @return string Mensagem de resultado da operação
     */
    public function alugar(): string {
        if ($this->disponivel) {
            $this->disponivel = false;
            return "Carro '{$this->modelo}' alugado com sucesso!";
        }
        return "Carro '{$this->modelo}' não está disponível.";
    }

    /**
     * Método para devolver o carro à locadora
     * 
     * @return string Mensagem de resultado da operação
     */
    public function devolver(): string {
        if (!$this->disponivel) {
            $this->disponivel = true;
            return "Carro '{$this->modelo}' devolvido com sucesso!";
        }
        return "Carro '{$this->modelo}' já está na locadora.";
    }
}
```

### 3.3 Criar o Arquivo Moto.php
Crie o arquivo `models/Moto.php`:

```php
<?php
namespace Models;

/**
 * Classe que representa uma Moto no sistema
 * Implementa a interface Locavel definida em Veiculo.php
 */
class Moto extends Veiculo implements Locavel {
    /**
     * Calcula o valor do aluguel para a moto
     * 
     * @param int $dias Quantidade de dias de aluguel
     * @return float Valor total do aluguel
     */
    public function calcularAluguel(int $dias): float {
        return $dias * DIARIA_MOTO;
    }

    /**
     * Método para alugar a moto
     * 
     * @return string Mensagem de resultado da operação
     */
    public function alugar(): string {
        if ($this->disponivel) {
            $this->disponivel = false;
            return "Moto '{$this->modelo}' alugada com sucesso!";
        }
        return "Moto '{$this->modelo}' não está disponível.";
    }

    /**
     * Método para devolver a moto à locadora
     * 
     * @return string Mensagem de resultado da operação
     */
    public function devolver(): string {
        if (!$this->disponivel) {
            $this->disponivel = true;
            return "Moto '{$this->modelo}' devolvida com sucesso!";
        }
        return "Moto '{$this->modelo}' já está na locadora.";
    }
}
```

## Etapa 4: Implementação dos Serviços

### 4.1 Criar o Arquivo Auth.php
Crie o arquivo `services/Auth.php`:

```php
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
        ];
        
        // Verifica se o perfil e a ação existem na matriz
        if (!isset($permissoes[$usuario['perfil']]) || !isset($permissoes[$usuario['perfil']][$acao])) {
            return false;
        }
        
        return $permissoes[$usuario['perfil']][$acao];
    }
}
```

### 4.2 Criar o Arquivo Locadora.php
Crie o arquivo `services/Locadora.php`:

```php
<?php
namespace Services;
use Models\{Veiculo, Carro, Moto};

/**
 * Classe responsável por gerenciar as operações da locadora
 */
class Locadora {
    /**
     * Conexão com o banco de dados
     * @var \PDO
     */
    private \PDO $db;
    
    /**
     * Lista de veículos carregados
     * @var array
     */
    private array $veiculos = [];

    /**
     * Construtor da classe Locadora
     */
    public function __construct() {
        $this->db = getConnection();
        $this->carregarVeiculos();
    }

    /**
     * Carrega os veículos do banco de dados
     */
    private function carregarVeiculos(): void {
        $stmt = $this->db->query("SELECT * FROM veiculos");
        $veiculosDb = $stmt->fetchAll();
        
        $this->veiculos = []; // Limpa a lista antes de carregar
        
        foreach ($veiculosDb as $dado) {
            if ($dado['tipo'] === 'Carro') {
                $veiculo = new Carro(
                    $dado['modelo'], 
                    $dado['placa'], 
                    (bool)$dado['disponivel'],
                    $dado['id']
                );
            } else {
                $veiculo = new Moto(
                    $dado['modelo'], 
                    $dado['placa'], 
                    (bool)$dado['disponivel'],
                    $dado['id']
                );
            }
            $this->veiculos[] = $veiculo;
        }
    }

    /**
     * Adiciona um novo veículo à locadora
     * 
     * @param Veiculo $veiculo Veículo a ser adicionado
     * @return bool Resultado da operação
     */
    public function adicionarVeiculo(Veiculo $veiculo): bool {
        $tipo = ($veiculo instanceof Carro) ? 'Carro' : 'Moto';
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO veiculos (tipo, modelo, placa, disponivel) 
                VALUES (?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $tipo,
                $veiculo->getModelo(),
                $veiculo->getPlaca(),
                $veiculo->isDisponivel() ? 1 : 0
            ]);
            
            if ($result) {
                // Define o ID gerado no objeto
                $veiculo->setId($this->db->lastInsertId());
                $this->veiculos[] = $veiculo;
            }
            
            return $result;
        } catch (\PDOException $e) {
            // Em caso de erro, como placa duplicada
            return false;
        }
    }

    /**
     * Remove um veículo da locadora
     * 
     * @param string $modelo Modelo do veículo
     * @param string $placa Placa do veículo
     * @return string Mensagem de resultado da operação
     */
    public function deletarVeiculo(string $modelo, string $placa): string {
        // Primeiro encontra o veículo na memória para ter o ID
        $id = null;
        foreach ($this->veiculos as $key => $veiculo) {
            if ($veiculo->getModelo() === $modelo && $veiculo->getPlaca() === $placa) {
                $id = $veiculo->getId();
                unset($this->veiculos[$key]);
                $this->veiculos = array_values($this->veiculos); // Reindexar array
                break;
            }
        }
        
        if ($id !== null) {
            $stmt = $this->db->prepare("DELETE FROM veiculos WHERE id = ?");
            if ($stmt->execute([$id])) {
                return "Veículo '{$modelo}' removido com sucesso!";
            }
            return "Erro ao remover veículo do banco de dados.";
        }
        
        return "Veículo não encontrado.";
    }

    /**
     * Aluga um veículo por um número específico de dias
     * 
     * @param string $modelo Modelo do veículo
     * @param int $dias Quantidade de dias do aluguel
     * @return string Mensagem de resultado da operação
     */
    public function alugarVeiculo(string $modelo, int $dias = 1): string {
        foreach ($this->veiculos as $veiculo) {
            if ($veiculo->getModelo() === $modelo && $veiculo->isDisponivel()) {
                $valorAluguel = $veiculo->calcularAluguel($dias);
                $mensagem = $veiculo->alugar();
                
                // Atualiza no banco de dados
                $stmt = $this->db->prepare("
                    UPDATE veiculos SET disponivel = 0 WHERE id = ?
                ");
                $stmt->execute([$veiculo->getId()]);
                
                return $mensagem . " Valor do aluguel: R$ " . number_format($valorAluguel, 2, ',', '.');
            }
        }
        return "Veículo não disponível.";
    }

    /**
     * Devolve um veículo alugado
     * 
     * @param string $modelo Modelo do veículo
     * @return string Mensagem de resultado da operação
     */
    public function devolverVeiculo(string $modelo): string {
        foreach ($this->veiculos as $veiculo) {
            if ($veiculo->getModelo() === $modelo && !$veiculo->isDisponivel()) {
                $mensagem = $veiculo->devolver();
                
                // Atualiza no banco de dados
                $stmt = $this->db->prepare("
                    UPDATE veiculos SET disponivel = 1 WHERE id = ?
                ");
                $stmt->execute([$veiculo->getId()]);
                
                return $mensagem;
            }
        }
        return "Veículo não encontrado ou já está disponível.";
    }

    /**
     * Retorna a lista de todos os veículos
     * 
     * @return array Lista de veículos
     */
    public function listarVeiculos(): array {
        return $this->veiculos;
    }

    /**
     * Calcula uma previsão de valor do aluguel
     * 
     * @param string $tipo Tipo do veículo (Carro ou Moto)
     * @param int $dias Quantidade de dias
     * @return float Valor previsto do aluguel
     */
    public function calcularPrevisaoAluguel(string $tipo, int $dias): float {
        if ($tipo === 'Carro') {
            return (new Carro('', ''))->calcularAluguel($dias);
        }
        return (new Moto('', ''))->calcularAluguel($dias);
    }
}
```

## Etapa 5: Implementação das Páginas e Controladores

### 5.1 Criar o Arquivo de Login
Crie o arquivo `public/login.php`:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_start();

use Services\Auth;

$mensagem = '';
$auth = new Auth();

// Se já estiver logado, redireciona para index
if (Auth::verificarLogin()) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        header('Location: ../index.php');
        exit;
    } else {
        $mensagem = 'Usuário ou senha inválidos';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Locadora</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Estilos customizados -->
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="login-container">
        <div class="card shadow">
            <!-- Cabeçalho do card de login -->
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-lock me-2"></i>Login</h4>
            </div>
            
            <!-- Corpo do card de login -->
            <div class="card-body">
                <!-- Espaço para mensagens de erro -->
                <?php if ($mensagem): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>
                
                <!-- Formulário de login -->
                <form method="post" class="needs-validation" novalidate>
                    <!-- Campo de usuário -->
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-1"></i>Usuário
                        </label>
                        <input type="text" id="username" name="username" class="form-control" required>
                        <div class="invalid-feedback">
                            Por favor, informe o nome de usuário.
                        </div>
                    </div>
                    
                    <!-- Campo de senha -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-key me-1"></i>Senha
                        </label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <div class="invalid-feedback">
                            Por favor, informe a senha.
                        </div>
                    </div>
                    
                    <!-- Botão de envio -->
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                    </button>
                </form>
                
                <!-- Seção com a dica de login -->
                <div class="login-tip mt-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Acesso ao Sistema</h5>
                    <p>Utilize os seguintes dados para acessar o sistema:</p>
                    <p><strong>Administrador:</strong> <code>admin</code> / <code>admin123</code> - Acesso total ao sistema</p>
                    <p><strong>Usuário:</strong> <code>usuario</code> / <code>user123</code> - Acesso limitado</p>
                </div>
            </div>
            
            <!-- Rodapé do card de login -->
            <div class="card-footer text-center">
                <small class="text-muted">Sistema de Locadora de Veículos &copy; <?= date('Y') ?></small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts customizados -->
    <script src="../js/scripts.js"></script>
</body>
</html>
```

### 5.2 Criar o Template Principal
Crie o arquivo `views/template.php`:

```php
<?php
use Services\Auth;

/**
 * Template principal do sistema de locadora de veículos
 * Recebe as variáveis $locadora, $mensagem e $usuario do controller (index.php)
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Locadora de Veículos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Estilos customizados -->
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="container py-4">
    <div class="container py-4">
        <!-- Barra superior com informações do usuário -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1>Sistema de Locadora de Veículos</h1>
                    <div class="d-flex align-items-center gap-3 user-info">
                        <!-- Ícone de usuário usando Bootstrap Icons -->
                        <span class="user-icon">
                            <i class="bi bi-person-circle" style="font-size: 24px;"></i>
                        </span>
                        <!-- Texto "Bem-vindo, [username]" -->
                        <span class="welcome-text">Bem-vindo, <strong><?= htmlspecialchars($usuario['username']) ?></strong></span>
                        <!-- Botão Sair com ícone usando Bootstrap Icons -->
                        <a href="?logout=1" class="btn btn-outline-danger d-flex align-items-center gap-1">
                            <i class="bi bi-box-arrow-right"></i>
                            Sair
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($mensagem): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row same-height-row">
            <?php if (Auth::temPermissao('adicionar')): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Novo Veículo</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Modelo</label>
                                <input type="text" name="modelo" class="form-control" required>
                                <div class="invalid-feedback">Informe um modelo válido.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Placa</label>
                                <input type="text" name="placa" class="form-control" required>
                                <div class="invalid-feedback">Informe uma placa válida.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Carro">Carro</option>
                                    <option value="Moto">Moto</option>
                                </select>
                            </div>
                            <button type="submit" name="adicionar" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg me-1"></i>Adicionar Veículo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-<?= Auth::temPermissao('adicionar') ? 'md-6' : '12' ?>">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-calculator me-2"></i>Calcular Previsão de Aluguel</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Veículo</label>
                                <select name="tipo_calculo" class="form-select" required>
                                    <option value="Carro">Carro</option>
                                    <option value="Moto">Moto</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantidade de Dias</label>
                                <input type="number" name="dias_calculo" class="form-control" value="1" min="1" required>
                            </div>
                            <button type="submit" name="calcular" class="btn btn-info w-100">
                                <i class="bi bi-calculator me-1"></i>Calcular Previsão
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-list-check me-2"></i>Veículos Cadastrados</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Modelo</th>
                                        <th>Placa</th>
                                        <th>Status</th>
                                        <?php if (Auth::temPermissao('alugar') || Auth::temPermissao('devolver') || Auth::temPermissao('deletar')): ?>
                                        <th>Ações</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($locadora->listarVeiculos() as $veiculo): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-<?= $veiculo instanceof \Models\Carro ? 'car-front' : 'bicycle' ?> me-1"></i>
                                            <?= $veiculo instanceof \Models\Carro ? 'Carro' : 'Moto' ?>
                                        </td>
                                        <td><?= htmlspecialchars($veiculo->getModelo()) ?></td>
                                        <td><?= htmlspecialchars($veiculo->getPlaca()) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $veiculo->isDisponivel() ? 'success' : 'warning' ?>">
                                                <?php if ($veiculo->isDisponivel()): ?>
                                                    <i class="bi bi-check-circle me-1"></i>Disponível
                                                <?php else: ?>
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Alugado
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <?php if (Auth::temPermissao('alugar') || Auth::temPermissao('devolver') || Auth::temPermissao('deletar')): ?>
                                        <td>
                                            <div class="action-wrapper">
                                                <form method="post" class="btn-group-actions">
                                                    <input type="hidden" name="modelo" value="<?= htmlspecialchars($veiculo->getModelo()) ?>">
                                                    <input type="hidden" name="placa" value="<?= htmlspecialchars($veiculo->getPlaca()) ?>">
                                                    
                                                    <!-- Botão Deletar (apenas com permissão específica) -->
                                                    <?php if (Auth::temPermissao('deletar')): ?>
                                                    <button type="submit" name="deletar" class="btn btn-danger btn-sm delete-btn">
                                                        <i class="bi bi-trash me-1"></i>Deletar
                                                    </button>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Botões condicionais baseados no status do veículo e permissões -->
                                                    <div class="rent-group">
                                                        <?php if (!$veiculo->isDisponivel()): ?>
                                                            <?php if (Auth::temPermissao('devolver')): ?>
                                                            <!-- Veículo alugado: Botão Devolver -->
                                                            <button type="submit" name="devolver" class="btn btn-warning btn-sm">
                                                                <i class="bi bi-arrow-return-left me-1"></i>Devolver
                                                            </button>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?php if (Auth::temPermissao('alugar')): ?>
                                                            <!-- Veículo disponível: Campo de dias e Botão Alugar -->
                                                            <input type="number" name="dias" class="form-control days-input" value="1" min="1" required>
                                                            <button type="submit" name="alugar" class="btn btn-primary btn-sm">
                                                                <i class="bi bi-key me-1"></i>Alugar
                                                            </button>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($locadora->listarVeiculos()) === 0): ?>
                                    <tr>
                                        <td colspan="<?= (Auth::temPermissao('alugar') || Auth::temPermissao('devolver') || Auth::temPermissao('deletar')) ? '5' : '4' ?>" class="text-center py-3">
                                            <i class="bi bi-exclamation-circle me-2"></i>Nenhum veículo cadastrado.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="mt-5 text-center text-muted">
            <hr>
            <p>Sistema de Locadora de Veículos &copy; <?= date('Y') ?> - Utilizando MySQL para persistência de dados</p>
        </footer>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário usando Bootstrap
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
```

### 5.3 Criar o Controlador Principal
Crie o arquivo `index.php` na raiz do projeto:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

session_start();

use Services\{Locadora, Auth};
use Models\{Carro, Moto};

// Verificar se está logado
if (!Auth::verificarLogin()) {
    header('Location: public/login.php');
    exit;
}

// Processar logout
if (isset($_GET['logout'])) {
    (new Auth())->logout();
    header('Location: public/login.php');
    exit;
}

// Instancia a Locadora
$locadora = new Locadora();
$mensagem = '';
$usuario = Auth::getUsuario();

// Processar requisições
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['adicionar'])) {
        if (!Auth::temPermissao('adicionar')) {
            $mensagem = "Você não tem permissão para adicionar veículos.";
            goto renderizar;
        }
        
        $modelo = $_POST['modelo'];
        $placa = $_POST['placa'];
        $tipo = $_POST['tipo'];

        $veiculo = ($tipo == 'Carro') ? new Carro($modelo, $placa) : new Moto($modelo, $placa);
        if ($locadora->adicionarVeiculo($veiculo)) {
            $mensagem = "Veículo adicionado com sucesso!";
        } else {
            $mensagem = "Erro ao adicionar veículo. Verifique se a placa é única.";
        }
    } elseif (isset($_POST['alugar'])) {
        if (!Auth::temPermissao('alugar')) {
            $mensagem = "Você não tem permissão para alugar veículos.";
            goto renderizar;
        }
        
        $dias = isset($_POST['dias']) ? (int)$_POST['dias'] : 1;
        $mensagem = $locadora->alugarVeiculo($_POST['modelo'], $dias);
    } elseif (isset($_POST['devolver'])) {
        if (!Auth::temPermissao('devolver')) {
            $mensagem = "Você não tem permissão para devolver veículos.";
            goto renderizar;
        }
        
        $mensagem = $locadora->devolverVeiculo($_POST['modelo']);
    } elseif (isset($_POST['deletar'])) {
        if (!Auth::temPermissao('deletar')) {
            $mensagem = "Você não tem permissão para deletar veículos.";
            goto renderizar;
        }
        
        $mensagem = $locadora->deletarVeiculo($_POST['modelo'], $_POST['placa']);
    } elseif (isset($_POST['calcular'])) {
        if (!Auth::temPermissao('calcular')) {
            $mensagem = "Você não tem permissão para calcular previsões.";
            goto renderizar;
        }
        
        $dias = (int)$_POST['dias_calculo'];
        $tipo = $_POST['tipo_calculo'];
        $valor = $locadora->calcularPrevisaoAluguel($tipo, $dias);
        $mensagem = "Previsão de valor para {$dias} dias: R$ " . number_format($valor, 2, ',', '.');
    }
}

renderizar:
// Inclui o template da view
require_once __DIR__ . '/views/template.php';
```

## Etapa 6: Integração e Testes

### 6.1 Estrutura de Arquivos Final
Certifique-se de que a estrutura de arquivos está completa:
```
locadora-veiculos/
├── config/
│   └── config.php
├── models/
│   ├── Veiculo.php
│   ├── Carro.php
│   └── Moto.php
├── public/
│   └── login.php
├── services/
│   ├── Auth.php
│   └── Locadora.php
├── views/
│   └── template.php
├── css/
│   └── styles.css
├── js/
│   └── scripts.js
├── vendor/
├── composer.json
└── index.php
```

### 6.2 Verificação do Banco de Dados
- Verifique se o banco de dados `locadora_db` foi criado corretamente
- Confirme que as tabelas `usuarios` e `veiculos` foram criadas
- Verifique se os dados iniciais foram inseridos

### 6.3 Teste Completo
1. Acesse a página de login (`public/login.php`)
   - Teste login com credenciais inválidas
   - Teste login com usuário admin (`admin`/`admin123`)
   - Teste login com usuário comum (`usuario`/`user123`)

2. Teste a página principal (`index.php`) com perfil admin
   - Adicione novos veículos
   - Alugue veículos disponíveis
   - Devolva veículos alugados
   - Delete veículos
   - Calcule previsões de aluguel

3. Teste a página principal (`index.php`) com perfil usuario
   - Verifique que apenas as permissões corretas estão disponíveis
   - Tente acessar funcionalidades restritas (deve ser bloqueado)
   - Teste a calculadora de previsões

4. Teste a funcionalidade de logout
   - Verifique se a sessão é encerrada corretamente
   - Confirme que foi redirecionado para a página de login

## Etapa 7: Conceitos Importantes para Ensinar

### 7.1 Programação Orientada a Objetos
- **Classes e Objetos**: Explique como `Veiculo`, `Carro` e `Moto` representam entidades reais
- **Herança**: Mostre como `Carro` e `Moto` herdam de `Veiculo`
- **Interfaces**: Explique a interface `Locavel` e seu papel no sistema
- **Abstração**: Discuta os métodos abstratos em `Veiculo`
- **Encapsulamento**: Mostre como os atributos são protegidos e acessados via métodos

### 7.2 Padrão MVC
- **Model**: Classes de modelo (`Veiculo`, `Carro`, `Moto`) e serviços (`Auth`, `Locadora`)
- **View**: Template principal (`template.php`) e página de login (`login.php`)
- **Controller**: Lógica de controle em `index.php`

### 7.3 Sistema de Permissões
- Explique a matriz de permissões em `Auth::temPermissao()`
- Demonstre como as permissões afetam a interface e as ações
- Discuta a segurança em camadas (UI, controlador, serviço)

### 7.4 Persistência com MySQL
- Explique o uso do PDO para conexão segura
- Mostre as operações CRUD nos métodos da classe `Locadora`
- Discuta as vantagens da persistência em banco de dados

## Etapa 8: Expandindo o Projeto (Ideias para Desafios aos Alunos)

1. **Implementar Novos Tipos de Veículos**
   - Adicionar classes para `Caminhao`, `Van`, etc.
   - Adaptar a interface para suportar os novos tipos

2. **Aprimorar o Sistema de Usuários**
   - Adicionar funcionalidade de cadastro de novos usuários
   - Implementar recuperação de senha
   - Criar mais perfis com diferentes níveis de permissão

3. **Adicionar Recursos Avançados**
   - Histórico de aluguéis
   - Relatórios financeiros
   - Sistema de reservas futuras

4. **Melhorar a Interface**
   - Adicionar filtros e pesquisa na tabela de veículos
   - Implementar tema escuro
   - Adicionar gráficos para visualização de dados

5. **Adicionar Validações Avançadas**
   - Validação de placa de veículo com expressão regular
   - Validação de disponibilidade em tempo real
   - Prevenção de conflitos de aluguel

Este roteiro completo fornece todas as instruções necessárias para implementar o back-end do sistema de locadora de veículos, transformando o front-end estático da Fase 1 em um sistema completo e funcional com PHP e MySQL.