# Funcionamento do Sistema de Locadora de Veículos com PHP, Bootstrap e MySQL

Este documento descreve o funcionamento do sistema de locadora de veículos desenvolvido em PHP, utilizando Bootstrap para a interface, com autenticação de usuários, gerenciamento de veículos (carros e motos) e persistência de dados em MySQL. O foco principal é explicar o funcionamento geral do sistema, com ênfase especial nos perfis de acesso e seu sistema de permissões.

## 1. Visão Geral do Sistema

O sistema de locadora de veículos é uma aplicação web que permite:
- Autenticação de usuários com dois perfis: **admin** (administrador) e **usuário** (usuário comum).
- Gerenciamento de veículos, incluindo cadastro, aluguel, devolução e exclusão.
- Cálculo de previsão de aluguel com base no tipo de veículo (carro ou moto) e número de dias.
- Interface responsiva baseada no framework Bootstrap, com ícones do Bootstrap Icons.

Os dados são armazenados em um banco de dados MySQL com duas tabelas principais:
- `usuarios`: Contém informações de usuários (username, senha criptografada e perfil).
- `veiculos`: Armazena os veículos cadastrados (tipo, modelo, placa e status de disponibilidade).

## 2. Estrutura do Sistema

### 2.1. Arquitetura
O sistema utiliza:
- **PHP**: Para lógica de negócios, autenticação e manipulação de dados.
- **MySQL**: Para persistência de dados em tabelas relacionais.
- **PDO**: Para conexão com o banco de dados de forma segura.
- **Bootstrap**: Para estilização e layout responsivo da interface.
- **Bootstrap Icons**: Para ícones na interface (como o ícone de usuário e seta no botão "Sair").
- **Composer**: Para autoloading de classes (via PSR-4).

A estrutura de pastas é organizada da seguinte forma:
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

### 2.2. Componentes Principais
- **Models**: Classes `Veiculo` (abstrata, contendo a interface `Locavel`), `Carro` e `Moto` para representar os veículos, com cálculo de aluguel baseado em diárias constantes (`DIARIA_CARRO` = R$ 100,00 e `DIARIA_MOTO` = R$ 50,00).
- **Services**: Classes `Auth` (autenticação e gerenciamento de usuários) e `Locadora` (gerenciamento de veículos).
- **Views**: Template principal em `template.php` para renderizar a interface, e `login.php` para autenticação.
- **Controllers**: Lógica em `index.php` (na raiz) para processar requisições e carregar o template.
- **Config**: Configurações de banco de dados e constantes do sistema em `config.php`.

## 3. Banco de Dados MySQL

### 3.1. Estrutura do Banco
O sistema utiliza duas tabelas principais:

#### Tabela `usuarios`:
```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL
) ENGINE=InnoDB;
```

#### Tabela `veiculos`:
```sql
CREATE TABLE IF NOT EXISTS veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(10) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    placa VARCHAR(10) NOT NULL UNIQUE,
    disponivel BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;
```

### 3.2. Conexão com o Banco
O sistema utiliza PDO para conexão segura com o banco de dados:

```php
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
```

### 3.3. Inicialização Automática
O sistema implementa uma função `initDatabase()` que cria o banco e as tabelas automaticamente na primeira execução:

```php
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
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo->exec("USE " . DB_NAME);
        
        // Cria as tabelas
        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (...)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS veiculos (...)");
        
        // Insere dados iniciais se necessário
        // ...
    } catch (PDOException $e) {
        die('Erro ao inicializar o banco de dados: ' . $e->getMessage());
    }
}
```

## 4. Funcionamento Geral

### 4.1. Autenticação
O sistema requer autenticação para acessar a página principal (`index.php`). O login é feito via `public/login.php`, que:
- Utiliza a classe `Services\Auth` para verificar credenciais (username e senha) contra a tabela `usuarios`.
- Armazena informações do usuário logado em `$_SESSION['auth']`, incluindo `username` e `perfil`.
- Suporta dois perfis:
  - **Admin**: Tem permissões completas para todas as operações do sistema.
  - **Usuário**: Tem permissões limitadas, definidas na matriz de permissões.

Os usuários padrão são:
- Admin: Username "admin", senha "admin123"
- Usuário: Username "usuario", senha "user123"

As senhas são criptografadas com `password_hash()` e verificadas com `password_verify()`.

### 4.2. Gerenciamento de Veículos
A classe `Services\Locadora` gerencia veículos (carros e motos) armazenados na tabela `veiculos`. Cada veículo tem:
- **ID**: Identificador único auto-incrementado no banco.
- **Tipo**: "Carro" ou "Moto".
- **Modelo**: Nome do veículo (ex.: "Sandero", "Ninja").
- **Placa**: Identificador único (ex.: "FMA-6680").
- **Disponível**: Status booleano (`true` para disponível, `false` para alugado).

#### Funcionalidades
- **Adicionar Veículo**: Requer permissão 'adicionar', permite adicionar novos veículos via formulário.
- **Alugar Veículo**: Requer permissão 'alugar', permite alugar um veículo disponível, especificando dias.
- **Devolver Veículo**: Requer permissão 'devolver', permite retornar um veículo alugado.
- **Deletar Veículo**: Requer permissão 'deletar', permite remover um veículo da locadora.
- **Calcular Previsão de Aluguel**: Requer permissão 'calcular', disponível para ambos os perfis.

## 5. Sistema de Permissões

### 5.1. Matriz de Permissões
O sistema implementa uma matriz de permissões detalhada que define o que cada perfil pode fazer:

```php
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
```

Cada perfil tem um conjunto específico de permissões booleanas que determinam o acesso a cada funcionalidade:
- **visualizar**: Permite ver os veículos cadastrados.
- **adicionar**: Permite adicionar novos veículos.
- **alugar**: Permite alugar veículos disponíveis.
- **devolver**: Permite devolver veículos alugados.
- **deletar**: Permite remover veículos da locadora.
- **calcular**: Permite calcular previsões de aluguel.

### 5.2. Perfil Admin
- **Acesso**: Usuário com `perfil: "admin"` (username "admin", senha "admin123").
- **Permissões**:
  - Todas as permissões estão habilitadas para este perfil.
  - Pode visualizar, adicionar, alugar, devolver, deletar e calcular.
- **Interface**:
  - Na barra superior, exibe "Bem-vindo, admin" (destacado em negrito/fundo branco).
  - Mostra todas as seções na página principal, incluindo o formulário "Adicionar Novo Veículo" e todos os botões de ação na tabela.

### 5.3. Perfil Usuário
- **Acesso**: Usuário com `perfil: "usuario"` (username "usuario", senha "user123").
- **Permissões**:
  - Permissão apenas para visualizar e calcular previsões.
  - Não pode adicionar, alugar, devolver ou deletar veículos.
- **Interface**:
  - Na barra superior, exibe "Bem-vindo, usuario" (destacado em negrito/fundo branco).
  - Não exibe o formulário "Adicionar Novo Veículo" nem os botões de ações na tabela de veículos.

### 5.4. Controle de Permissões
O sistema utiliza o método `Auth::temPermissao()` para verificações granulares de permissões:

```php
/**
 * Verifica se o usuário tem permissão para uma ação específica
 * baseado em uma matriz de permissões por perfil
 */
public static function temPermissao(string $acao): bool {
    $usuario = self::getUsuario();
    if (!$usuario) {
        return false;
    }
    
    // Matriz de permissões por perfil
    $permissoes = [
        'admin' => [ /* permissões do admin */ ],
        'usuario' => [ /* permissões do usuário */ ]
    ];
    
    // Verifica se o perfil e a ação existem na matriz
    if (!isset($permissoes[$usuario['perfil']]) || !isset($permissoes[$usuario['perfil']][$acao])) {
        return false;
    }
    
    return $permissoes[$usuario['perfil']][$acao];
}
```

Esta abordagem oferece diversas vantagens:
- **Granularidade**: Permissões específicas para cada ação.
- **Flexibilidade**: Facilidade para adicionar novos perfis e permissões.
- **Manutenibilidade**: Centraliza as regras de permissão em um único lugar.
- **Extensibilidade**: Facilita a expansão futura do sistema.

## 6. Interface do Usuário

### 6.1. Login (`public/login.php`)
- Exibe um formulário com campos para username e senha, estilizado com Bootstrap.
- Inclui dicas sobre os usuários disponíveis (admin/admin123 e usuario/user123).
- Após login bem-sucedido, redireciona para `index.php` na raiz.
- Exibe mensagens de erro (ex.: "Usuário ou senha inválidos") em caso de falha.

### 6.2. Página Principal (`index.php` e `views/template.php`)
- **Barra Superior**:
  - Mostra "Sistema de Locadora de Veículos" à esquerda.
  - Exibe "Bem-vindo, [username]" (com ícone de usuário e username destacado) e botão "Sair" (com ícone de seta) à direita, em uma barra preta com texto branco.
- **Seções**:
  - **Adicionar Novo Veículo**: Formulário visível apenas para usuários com permissão 'adicionar'.
  - **Calcular Previsão de Aluguel**: Formulário disponível para usuários com permissão 'calcular'.
  - **Veículos Cadastrados**: Tabela com colunas Tipo, Modelo, Placa, Status e Ações (visíveis somente com as permissões adequadas).
- **Estilização**: Usa Bootstrap para layout responsivo, com Bootstrap Icons para ícones.
- **Rodapé**: Mostra informações sobre o sistema e o ano atual.

As seções e botões da interface são exibidos condicionalmente com base nas permissões do usuário:

```php
<?php if (Auth::temPermissao('adicionar')): ?>
<!-- Formulário de adicionar veículo -->
<?php endif; ?>

<?php if (Auth::temPermissao('alugar') || Auth::temPermissao('devolver') || Auth::temPermissao('deletar')): ?>
<!-- Coluna de ações na tabela -->
<?php endif; ?>
```

## 7. Fluxo de Funcionamento

### 7.1. Autenticação
1. O usuário acessa `public/login.php`.
2. Insere username e senha, que são validados pela classe `Auth` contra a tabela `usuarios`.
3. Se válido, salva os dados na sessão (`$_SESSION['auth']`) e redireciona para `index.php`.

### 7.2. Navegação e Ações
1. Em `index.php`, o sistema verifica as permissões do usuário para cada ação solicitada.
2. As ações são processadas apenas se o usuário tiver a permissão correspondente:
```php
if (isset($_POST['adicionar'])) {
    if (!Auth::temPermissao('adicionar')) {
        $mensagem = "Você não tem permissão para adicionar veículos.";
        goto renderizar;
    }
    // Processa a adição de veículo
}
```
3. A interface exibe apenas os elementos que o usuário tem permissão para usar.
4. Mensagens de feedback específicas são exibidas quando o acesso é negado.

## 8. Persistência de Dados com MySQL

### 8.1. Vantagens do MySQL
O uso de MySQL como sistema de persistência oferece várias vantagens:
- **Robustez**: Armazenamento seguro com suporte a transações e verificações de integridade.
- **Eficiência**: Busca e recuperação de dados otimizadas com indexação.
- **Escalabilidade**: Capacidade para lidar com crescimento no volume de dados e usuários.
- **Concorrência**: Gerenciamento adequado de acessos simultâneos.
- **Backup**: Facilidade para realizar backups e restaurações de dados.

### 8.2. Principais Operações no Banco
O sistema realiza operações CRUD (Create, Read, Update, Delete) através de consultas preparadas:

#### Inserção de Veículo:
```php
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
```

#### Atualização de Status:
```php
$stmt = $this->db->prepare("
    UPDATE veiculos SET disponivel = ? WHERE id = ?
");
$stmt->execute([$disponivel ? 1 : 0, $veiculo->getId()]);
```

#### Exclusão de Veículo:
```php
$stmt = $this->db->prepare("DELETE FROM veiculos WHERE id = ?");
$stmt->execute([$id]);
```

#### Consulta de Veículos:
```php
$stmt = $this->db->query("SELECT * FROM veiculos");
$veiculosDb = $stmt->fetchAll();
```

### 8.3. Conversão Objeto-Relacional
O sistema implementa uma conversão entre os registros do banco e objetos das classes `Carro` e `Moto`:

```php
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
```

## 9. Considerações Técnicas
- **Segurança**: 
  - Senhas criptografadas com `password_hash()` e verificadas com `password_verify()`.
  - Uso de consultas preparadas para prevenir injeção SQL.
  - Interface usa `htmlspecialchars()` para evitar XSS.
- **Responsividade**: Layout responsivo com Bootstrap, ajustando-se a dispositivos móveis.
- **Manutenção**: Sistema usa autoloading do Composer para gerenciar classes.
- **Robustez**: Tratamento de erros com blocos try-catch para operações de banco de dados.
- **Permissões**: Matriz de permissões que facilita adicionar novos perfis sem alterar o código em múltiplos lugares.

## 10. Exemplo de Uso
- **Login como Admin**:
  - Acesse `login.php`, insira "admin" e "admin123".
  - Veja a página principal com todas as funcionalidades disponíveis.
- **Login como Usuário**:
  - Acesse `login.php`, insira "usuario" e "user123".
  - Veja apenas a lista de veículos e o formulário de previsão, sem botões de ações.

## 11. Benefícios do Sistema Atualizado

### 11.1. Benefícios da Persistência MySQL
1. **Integridade de dados**: Constraints como UNIQUE na placa previnem duplicidade.
2. **Consistência**: Transações garantem operações atômicas e consistentes.
3. **Desempenho**: Índices otimizam a busca e recuperação de dados.
4. **Concorrência**: Suporte a múltiplos acessos simultâneos com controle de bloqueio.
5. **Segurança**: Autenticação e autorização no nível do banco de dados.

### 11.2. Benefícios do Sistema de Permissões
1. **Escalabilidade**: Facilita a adição de novos perfis com conjuntos específicos de permissões.
2. **Manutenibilidade**: Permissões definidas em um único lugar, facilitando alterações.
3. **Clareza**: Código mais legível e autodocumentado, com verificações explícitas.
4. **Segurança**: Cada ação protegida individualmente, reduzindo riscos de acesso não autorizado.
5. **Feedback ao usuário**: Mensagens específicas quando uma permissão é negada.

### 11.3. Melhorias na Organização
1. **Arquivo index.php na raiz**: Facilita o acesso direto à aplicação.
2. **Interface Locavel incorporada**: Simplifica a estrutura de arquivos mantendo o padrão OO.
3. **Dicas de login**: Melhora a experiência de primeiro acesso com informações claras sobre credenciais.
4. **Inicialização automática do banco**: Sistema "plug and play" que cria sua própria estrutura.
