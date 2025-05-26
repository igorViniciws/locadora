# Programação Orientada a Objetos (POO) no Sistema de Locadora de Veículos com MySQL

Este documento explica o que é a Programação Orientada a Objetos (POO) e como ela funciona, detalhando seus quatro pilares fundamentais (Encapsulamento, Abstração, Herança e Polimorfismo). Também descreve como a POO é aplicada no sistema de locadora de veículos desenvolvido em PHP, com autenticação de usuários, gerenciamento de veículos (carros e motos), interface baseada em Bootstrap e persistência em MySQL.

## 1. O que é Programação Orientada a Objetos (POO)?

A **Programação Orientada a Objetos (POO)** é um paradigma de programação que organiza o código em torno de "objetos", que combinam dados (atributos) e comportamentos (métodos). Em vez de escrever código procedural (baseado em funções e sequências), a POO modela o software como entidades do mundo real, facilitando a reutilização, manutenção e escalabilidade.

### 1.1. Características Principais da POO
- **Objetos**: Instâncias de classes que representam entidades com dados e comportamentos.
- **Classes**: Modelos ou "blueprints" que definem as propriedades e métodos dos objetos.
- **Pilares da POO**: Conceitos fundamentais que guiam o design orientado a objetos:
  1. **Encapsulamento**: Ocultar detalhes de implementação e expor apenas o necessário.
  2. **Abstração**: Simplificar complexidade, focando nos aspectos essenciais.
  3. **Herança**: Permitir que uma classe herde atributos e métodos de outra.
  4. **Polimorfismo**: Capacidade de objetos de diferentes classes responderem ao mesmo método de maneiras distintas.

## 2. Os Quatro Pilares da POO

### 2.1. Encapsulamento
- **Definição**: Protege os dados internos de uma classe, restringindo o acesso direto a atributos e expondo apenas métodos públicos (interfaces) para manipulá-los. Geralmente, usa modificadores de acesso como `public`, `protected` e `private`.
- **Benefícios**: Segurança, redução de erros e maior controle sobre o estado do objeto.
- **Exemplo no Mundo Real**: Um carro tem dados internos (motor, transmissão) que só podem ser acessados ou modificados por meio de controles (ex.: acelerar, frear).

### 2.2. Abstração
- **Definição**: Esconde detalhes complexos e expõe apenas as funcionalidades essenciais de um objeto. Classes abstratas ou interfaces são usadas para definir o que um objeto faz, não como faz.
- **Benefícios**: Simplifica o uso, focando na interface e não na implementação.
- **Exemplo no Mundo Real**: Você usa um controle remoto sem saber como o sinal é transmitido para a TV.

### 2.3. Herança
- **Definição**: Permite que uma classe herde atributos e métodos de outra classe, promovendo reutilização de código. A classe pai (superclasse) é extendida pela classe filha (subclasse).
- **Benefícios**: Reduz redundância e facilita a extensibilidade.
- **Exemplo no Mundo Real**: Um carro e uma moto herdam características de um "veículo" (rodas, motor), mas têm comportamentos específicos.

### 2.4. Polimorfismo
- **Definição**: Permite que objetos de diferentes classes sejam tratados como instâncias de uma classe pai ou interface comum, respondendo de maneiras distintas ao mesmo método.
- **Benefícios**: Flexibilidade e código mais genérico.
- **Exemplo no Mundo Real**: Um controle remoto pode ligar diferentes dispositivos (TV, som), mas o mesmo botão "ligar" tem comportamento específico para cada dispositivo.

## 3. Como a POO é Aplicada no Sistema

O sistema de locadora de veículos utiliza POO extensivamente, estruturando o código em classes e objetos para gerenciar usuários, veículos e operações. Abaixo, detalho como cada pilar da POO é implementado.

### 3.1. Encapsulamento
- **Implementação**:
  - Classes como `Models\Veiculo`, `Models\Carro`, `Models\Moto`, `Services\Locadora` e `Services\Auth` usam modificadores de acesso (`protected`, `private`) para ocultar dados internos.
  - Atributos como `$modelo`, `$placa`, `$disponivel` e `$id` em `Veiculo` são `protected`, acessíveis apenas por métodos públicos como `getModelo()`, `getPlaca()`, `getId()` e `setDisponivel()`.
  - Exemplo em `Veiculo.php`:
    ```php
    protected string $modelo;
    protected string $placa;
    protected bool $disponivel;
    protected ?int $id = null;

    public function getModelo(): string {
        return $this->modelo;
    }
    
    public function getId(): ?int {
        return $this->id;
    }
    ```
  - Em `Auth.php`, a propriedade `$db` é `private`, e os métodos públicos como `login()` encapsulam o acesso ao banco de dados para autenticação.
  - O sistema de permissões em `Auth.php` encapsula a matriz de permissões dentro do método `temPermissao()`, protegendo a lógica de controle de acesso:
    ```php
    public static function temPermissao(string $acao): bool {
        $usuario = self::getUsuario();
        if (!$usuario) {
            return false;
        }
        
        // Matriz de permissões encapsulada
        $permissoes = [
            'admin' => [ /* permissões */ ],
            'usuario' => [ /* permissões */ ]
        ];
        
        return $permissoes[$usuario['perfil']][$acao] ?? false;
    }
    ```
- **Benefício**: Garante que os dados sejam manipulados apenas por métodos controlados, evitando alterações diretas e erros. No caso do sistema de permissões, o encapsulamento permite modificar a matriz de permissões sem afetar o restante do código.

### 3.2. Abstração
- **Implementação**:
  - A interface `Locavel` (agora incorporada dentro de `Veiculo.php`) define métodos abstratos (`alugar()`, `devolver()`, `isDisponivel()`) que `Carro` e `Moto` devem implementar, escondendo detalhes de como cada veículo realiza essas ações.
  - A classe abstrata `Models\Veiculo` fornece uma base comum para `Carro` e `Moto`, definindo o comportamento geral e declarando o método abstrato `calcularAluguel()` para ser implementado pelas subclasses.
  - Exemplo da interface `Locavel` (agora em `Veiculo.php`):
    ```php
    interface Locavel {
        public function alugar(): string;
        public function devolver(): string;
        public function isDisponivel(): bool;
    }
    ```
  - Exemplo do método abstrato em `Veiculo.php`:
    ```php
    abstract class Veiculo {
        // ...
        abstract public function calcularAluguel(int $dias): float;
    }
    ```
  - O sistema de permissões usa abstração ao definir permissões por "ação" (como 'adicionar', 'alugar', 'deletar'), ocultando a complexidade de como essas permissões são verificadas e aplicadas.
  - A camada de acesso ao banco de dados em `Locadora.php` e `Auth.php` abstrai as operações SQL, expondo apenas métodos de alto nível para manipular veículos e usuários.
- **Benefício**: Permite tratar `Carro` e `Moto` como objetos `Locavel` ou `Veiculo` genericamente, sem se preocupar com implementações específicas. Para o sistema de permissões e acesso ao banco, a abstração simplifica a integração entre componentes.

### 3.3. Herança
- **Implementação**:
  - `Models\Carro` e `Models\Moto` herdam da classe abstrata `Models\Veiculo`, reutilizando atributos e métodos, incluindo o novo atributo `$id` para integração com o banco de dados.
  - Ambas as classes também implementam a interface `Locavel` (agora parte do arquivo `Veiculo.php`), herdando o contrato de métodos para locação.
  - Exemplo em `Carro.php`:
    ```php
    class Carro extends Veiculo implements Locavel {
        public function calcularAluguel(int $dias): float {
            return $dias * DIARIA_CARRO;
        }
        
        public function alugar(): string {
            if ($this->disponivel) {
                $this->disponivel = false;
                return "Carro '{$this->modelo}' alugado com sucesso!";
            }
            return "Carro '{$this->modelo}' não está disponível.";
        }
        
        // Outros métodos implementados
    }
    ```
- **Benefício**: Reduz duplicação de código, pois `Carro` e `Moto` compartilham a lógica comum de `Veiculo`, mas podem ter comportamentos específicos (ex.: `calcularAluguel()` com diárias diferentes).

### 3.4. Polimorfismo
- **Implementação**:
  - `Carro` e `Moto` são tratados como instâncias de `Veiculo` ou `Locavel`, permitindo que o mesmo método (`alugar()`, `devolver()`, `calcularAluguel()`) tenha comportamentos distintos.
  - Em `Services\Locadora`, o método `listarVeiculos()` retorna um array de `Veiculo`, e `alugarVeiculo()` opera em qualquer veículo que implemente `Locavel`, sem se preocupar com o tipo específico.
  - Exemplo em `Locadora.php`:
    ```php
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
    ```
  - O polimorfismo permite que `Locadora` trabalhe com `Carro` ou `Moto` de forma genérica, enquanto a implementação específica pode incluir operações de banco de dados.
  - No sistema de permissões, o polimorfismo está presente na maneira como o método `temPermissao()` pode verificar diferentes tipos de permissões para diferentes perfis de usuários.
- **Benefício**: Facilita a extensão do sistema (ex.: adicionar novos tipos de veículos ou perfis de usuário) e torna o código mais flexível.

## 4. POO e Persistência em MySQL

### 4.1. Mapeamento Objeto-Relacional
- **Implementação**:
  - A classe `Veiculo` agora inclui um atributo `$id` para representar a chave primária no banco de dados:
    ```php
    protected ?int $id = null;
    
    public function getId(): ?int {
        return $this->id;
    }
    
    public function setId(int $id): void {
        $this->id = $id;
    }
    ```
  - Em `Locadora.php`, o método `carregarVeiculos()` mapeia registros do banco para objetos:
    ```php
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
    ```
  - O método `adicionarVeiculo()` persiste objetos no banco:
    ```php
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
            return false;
        }
    }
    ```
- **Benefício**: Fornece uma camada de abstração entre objetos PHP e o banco de dados relacional, permitindo trabalhar com objetos em memória enquanto persiste os dados de forma robusta.

### 4.2. Encapsulamento de Operações de Banco
- **Implementação**:
  - A conexão com o banco de dados (`$db`) é encapsulada nas classes `Auth` e `Locadora`, tornando-se uma propriedade privada:
    ```php
    private \PDO $db;
    
    public function __construct() {
        $this->db = getConnection();
    }
    ```
  - Operações de banco são encapsuladas em métodos específicos, como `alugarVeiculo()`, `devolverVeiculo()`, e `deletarVeiculo()`.
  - Consultas preparadas são utilizadas para segurança:
    ```php
    $stmt = $this->db->prepare("
        UPDATE veiculos SET disponivel = ? WHERE id = ?
    ");
    $stmt->execute([1, $veiculo->getId()]);
    ```
- **Benefício**: Protege a implementação do banco de dados, permitindo alterações na estrutura do banco sem afetar o restante do código.

### 4.3. Tratamento de Erros com POO
- **Implementação**:
  - Exceções são usadas para lidar com erros de banco de dados:
    ```php
    try {
        // Operação de banco
    } catch (\PDOException $e) {
        // Tratamento de erro
    }
    ```
- **Benefício**: Proporciona um mecanismo robusto para lidar com falhas de banco de dados, seguindo os princípios de POO para tratamento de erros.

## 5. Aplicação da POO no Sistema

### 5.1. Classes e Objetos
- O sistema é estruturado em classes que modelam entidades do mundo real:
  - **Usuários**: Gerenciados por `Services\Auth`, com objetos representando `username`, `password` e `perfil`.
  - **Veículos**: Representados por `Models\Veiculo`, `Models\Carro` e `Models\Moto`, com objetos contendo `modelo`, `placa`, `disponivel` e `id`.
  - **Permissões**: Modeladas como uma matriz associativa em `Services\Auth`, onde cada perfil tem um conjunto específico de permissões para diferentes ações.
- Instâncias dessas classes são criadas e manipuladas em `index.php` e `services/Locadora.php`.

### 5.2. Benefícios da POO no Projeto
- **Reutilização**: Classes como `Veiculo` são reutilizadas por `Carro` e `Moto` via herança.
- **Manutenção**: Encapsulamento e abstração facilitam alterações na lógica (ex.: mudar diárias em `config.php` ou adicionar novas permissões) sem afetar a interface.
- **Extensibilidade**: O polimorfismo permite adicionar novos tipos de veículos (ex.: caminhões) ou perfis de usuário facilmente.
- **Segurança**: O encapsulamento do sistema de permissões e das operações de banco protege o acesso a funcionalidades críticas.
- **Robustez**: A integração com MySQL através de mapeamento objeto-relacional proporciona persistência confiável.

### 5.3. Exemplo Prático
- Quando um usuário tenta alugar um veículo em `index.php`:
  1. `Auth::temPermissao('alugar')` verifica se o usuário tem permissão (encapsulamento e abstração).
  2. Se permitido, `Locadora::alugarVeiculo()` utiliza um objeto `Carro` ou `Moto` (herdado de `Veiculo`).
  3. O método `alugar()` (polimorfismo) é chamado, retornando uma mensagem específica.
  4. O estado (`disponivel`) é encapsulado e atualizado via `setDisponivel()`, e persistido no banco com uma consulta preparada.

### 5.4. POO e o Sistema de Permissões
- O sistema de permissões baseado em matriz demonstra vários princípios da POO:
  - **Encapsulamento**: A matriz de permissões é encapsulada dentro do método `temPermissao()`.
  - **Abstração**: As verificações de permissão simplificam o controle de acesso, ocultando a complexidade da matriz.
  - **Polimorfismo**: O mesmo método `temPermissao()` comporta-se diferentemente com base no perfil do usuário e na ação solicitada.
- Esta implementação permite adicionar facilmente novos perfis com conjuntos específicos de permissões, demonstrando a flexibilidade da POO.

## 6. Aprimoramentos Estruturais da POO

### 6.1. Interface Incorporada
- **Implementação**:
  - A interface `Locavel` foi movida para dentro do arquivo `Veiculo.php`:
    ```php
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
        // ...
    }
    ```
- **Benefício**: Simplifica a estrutura do projeto, mantendo elementos relacionados no mesmo arquivo, sem comprometer os princípios da POO.

### 6.2. Centralização do Controller
- **Implementação**:
  - O arquivo `index.php` foi movido para a raiz do projeto, servindo como ponto central de controle:
    ```php
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

    // Restante do código do controlador
    ```
- **Benefício**: Melhora a organização seguindo práticas de POO para estruturação de projetos, com um ponto de entrada claro.

## 7. Conclusão

O sistema de locadora de veículos aproveita a POO para criar um design modular e robusto, agora com persistência em MySQL. Os quatro pilares da POO—Encapsulamento, Abstração, Herança e Polimorfismo—são aplicados de forma consistente:
- **Encapsulamento**: Protege dados com modificadores de acesso e métodos de acesso, incluindo a matriz de permissões e as operações de banco de dados.
- **Abstração**: Usa interfaces e classes abstratas para simplificar interações com veículos, permissões e banco de dados.
- **Herança**: Permite `Carro` e `Moto` herdarem de `Veiculo`, reduzindo redundância.
- **Polimorfismo**: Trata `Carro` e `Moto` como `Veiculo` ou `Locavel`, e aplica diferentes regras de permissão para diferentes perfis.

A integração com MySQL fortalece o sistema, proporcionando persistência robusta, enquanto as melhorias estruturais (como a incorporação da interface `Locavel` e a centralização do controlador) simplificam a organização sem comprometer os princípios da POO.

Essa abordagem orientada a objetos torna o sistema escalável, fácil de manter e preparado para futuras expansões, como a adição de novos tipos de veículos, perfis de usuário ou funcionalidades, tudo com persistência de dados confiável e estruturada.