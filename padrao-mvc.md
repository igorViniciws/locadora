# Padrão MVC no Sistema de Locadora de Veículos com MySQL

Este documento explica o que é o padrão de projeto MVC (Model-View-Controller) e como ele é aplicado no sistema de locadora de veículos desenvolvido em PHP, com autenticação de usuários, gerenciamento de veículos (carros e motos), interface baseada em Bootstrap e persistência em MySQL.

## 1. O que é o Padrão MVC?

O **MVC (Model-View-Controller)** é um padrão de arquitetura de software utilizado para organizar o código de aplicações, separando as responsabilidades em três componentes principais:

### 1.1. Model (Modelo)
- **Função**: Representa os dados, a lógica de negócios e as regras da aplicação. É responsável por gerenciar o estado dos dados, interagir com o banco de dados ou fontes de dados, e realizar operações como validações e cálculos.
- **Características**:
  - Não depende da interface de usuário.
  - Notifica a View sobre mudanças nos dados.
  - Mantém a lógica de manipulação dos dados centralizada.

### 1.2. View (Visão)
- **Função**: Responsável pela apresentação dos dados ao usuário, ou seja, a interface de usuário (UI). Exibe os dados fornecidos pelo Model e envia interações do usuário para o Controller.
- **Características**:
  - É tipicamente composta por HTML, CSS e JavaScript (ou templates, como no caso de PHP).
  - Não contém lógica de negócios, apenas formatação e exibição.

### 1.3. Controller (Controlador)
- **Função**: Atua como um intermediário entre o Model e a View. Recebe entradas do usuário (via View), manipula o Model para atualizar os dados ou realizar ações, e seleciona a View apropriada para renderizar os resultados.
- **Características**:
  - Processa solicitações (requests), geralmente via HTTP (GET/POST).
  - Coordena a interação entre Model e View, mantendo a separação entre eles.

### 1.4. Benefícios do MVC
- **Separação de preocupações**: Facilita a manutenção, teste e escalabilidade do código.
- **Reutilização**: Modelos e Views podem ser reutilizados em diferentes partes da aplicação.
- **Flexibilidade**: Permite alterações na interface sem afetar a lógica de negócios, e vice-versa.

## 2. Como o Padrão MVC Funciona no Sistema

O sistema de locadora de veículos é estruturado seguindo o padrão MVC, embora de forma simplificada, devido ao uso de PHP puro e sem frameworks MVC completos (como Laravel ou Symfony). Abaixo, detalho como cada componente é implementado e interage no projeto.

### 2.1. Model (Modelo)
- **Localização**: Classes no diretório `models/` e `services/`.
- **Implementação**:
  - **Classes `Veiculo`, `Carro` e `Moto` (em `models/`)**:
    - Representam os dados e a lógica dos veículos (carros e motos).
    - Contêm propriedades como `modelo`, `placa`, `disponivel` e `id`, além de métodos como `calcularAluguel()`, `alugar()`, `devolver()` e `isDisponivel()`.
    - A interface `Locavel` agora está incorporada no arquivo `Veiculo.php` para simplificar a estrutura.
  - **Classe `Services\Locadora` (em `services/`)**:
    - Gerencia a coleção de veículos, interagindo com o banco de dados MySQL para persistência.
    - Implementa métodos como `adicionarVeiculo()`, `alugarVeiculo()`, `devolverVeiculo()`, `deletarVeiculo()` e `calcularPrevisaoAluguel()`.
    - Utiliza PDO para operações seguras no banco de dados.
  - **Classe `Services\Auth` (em `services/`)**:
    - Gerencia os usuários, interagindo com o banco de dados MySQL para autenticação e autorização.
    - Implementa métodos como `login()`, `logout()`, `verificarLogin()`, `isAdmin()`, `getUsuario()` e `temPermissao()`.
    - **Sistema de Permissões**: Implementa uma matriz de permissões que define o que cada perfil pode fazer.
- **Exemplo de Interação**:
  - O `Locadora` carrega e salva veículos no banco de dados usando consultas preparadas PDO.
  - O `Auth` gerencia autenticação e permissões, validando usuários na tabela `usuarios`.

### 2.2. View (Visão)
- **Localização**: Arquivos em `views/` e `public/` (templates HTML).
- **Implementação**:
  - **Arquivo `views/template.php`**:
    - Responsável pela interface principal, exibindo a lista de veículos, formulários para adicionar veículos, calcular previsão de aluguel, e botões para ações (alugar, devolver, deletar).
    - Utiliza Bootstrap para estilização e Bootstrap Icons para ícones (ex.: ícone de usuário e seta no botão "Sair").
    - Recebe dados do Controller (`index.php`) via variáveis como `$locadora`, `$mensagem` e `$usuario`.
    - **Interface Adaptativa**: Exibe elementos com base nas permissões específicas do usuário.
  - **Arquivo `public/login.php`**:
    - Exibe o formulário de login, estilizado com Bootstrap, e mensagens de erro, se houver.
    - Inclui dicas sobre os usuários disponíveis (admin/admin123 e usuario/user123).
- **Exemplo de Interação**:
  - `template.php` exibe uma tabela com veículos usando `$locadora->listarVeiculos()` e condiciona a exibição de ações com base nas permissões específicas (`Auth::temPermissao()`).
  - `login.php` mostra um formulário para autenticação, incluindo dicas de login, sem lógica de negócios.

### 2.3. Controller (Controlador)
- **Localização**: Arquivo `index.php` (na raiz) e parcialmente em `public/login.php`.
- **Implementação**:
  - **Arquivo `index.php`**:
    - Recebe solicitações do usuário (via GET/POST) e coordena as ações entre Model e View.
    - Instancia `Services\Locadora` e `Services\Auth` para manipular dados.
    - Processa ações como:
      - Adicionar veículo (`adicionar`).
      - Alugar veículo (`alugar` com dias).
      - Devolver veículo (`devolver`).
      - Deletar veículo (`deletar`).
      - Calcular previsão de aluguel (`calcular`).
    - **Verificação de Permissões**: Usa `Auth::temPermissao()` para verificar permissões específicas para cada ação.
    - Renderiza `views/template.php`, passando variáveis como `$locadora`, `$mensagem` e `$usuario`.
  - **Arquivo `public/login.php`**:
    - Recebe o formulário de login (POST) e usa `Services\Auth` para autenticação.
    - Redireciona para `index.php` após login bem-sucedido ou exibe mensagem de erro.
- **Exemplo de Interação**:
  - Quando um usuário clica em "Alugar" na tabela, `index.php` verifica se ele tem permissão com `Auth::temPermissao('alugar')`, depois chama `Locadora::alugarVeiculo()`, atualiza o banco de dados, e passa a mensagem para `template.php` exibir.

## 3. Aplicação do MVC no Projeto

### 3.1. Separação de Responsabilidades
- **Model**: Contém toda a lógica de negócios, persistência dos dados em MySQL e sistema de permissões (classes em `models/` e `services/`).
  - Ex.: `Carro::calcularAluguel()` calcula o custo com base na diária, `Locadora::adicionarVeiculo()` persiste no banco de dados, e `Auth::temPermissao()` gerencia as permissões.
- **View**: Responsável apenas pela apresentação (arquivos em `views/` e `public/`).
  - Ex.: `template.php` exibe a tabela de veículos e formulários, adaptando-se às permissões do usuário.
- **Controller**: Coordena as interações, processando requisições e invocando Model/View.
  - Ex.: `index.php` processa o clique em "Alugar", verifica permissões, chama `Locadora::alugarVeiculo()`, e renderiza `template.php` com a mensagem.

### 3.2. Integração com Banco de Dados MySQL
- **Configuração**: O arquivo `config/config.php` contém constantes de conexão com o banco de dados e uma função de inicialização que cria o banco e as tabelas, se necessário.
- **Modelo**: As classes de modelo foram estendidas para interagir com o banco de dados:
  - Adicionado o atributo `id` para representar a chave primária no banco.
  - Adicionados métodos de conversão entre objetos e registros do banco.
- **Serviços**: As classes de serviço foram adaptadas para usar PDO:
  - `Locadora` agora usa consultas preparadas para operações CRUD.
  - `Auth` valida usuários contra a tabela `usuarios` no banco.

### 3.3. Fluxo Típico
1. Um usuário acessa `public/login.php` e faz login (Controller).
2. `login.php` usa `Auth` (Model) para verificar credenciais na tabela `usuarios`.
3. Após login, redireciona para `index.php`, que instancia `Locadora` (Model) e carrega o perfil com `Auth`.
4. `index.php` (Controller) processa ações como alugar um veículo, verificando permissões e chamando métodos de `Locadora`.
5. `template.php` (View) exibe os dados (veículos, mensagens) com base nas permissões específicas do usuário, mostrando apenas as ações permitidas.

### 3.4. Sistema de Permissões no MVC
- **Model**: No arquivo `services/Auth.php`, a matriz de permissões define o que cada perfil pode fazer:
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

- **Controller**: No arquivo `index.php`, cada ação verifica permissões específicas:
  ```php
  if (isset($_POST['adicionar'])) {
      if (!Auth::temPermissao('adicionar')) {
          $mensagem = "Você não tem permissão para adicionar veículos.";
          goto renderizar;
      }
      // Processa a adição de veículo
  }
  ```

- **View**: No arquivo `views/template.php`, elementos são exibidos com base nas permissões:
  ```php
  <?php if (Auth::temPermissao('adicionar')): ?>
  <!-- Formulário de adicionar veículo -->
  <?php endif; ?>
  
  <?php if (Auth::temPermissao('alugar') || Auth::temPermissao('devolver') || Auth::temPermissao('deletar')): ?>
  <!-- Coluna de ações na tabela -->
  <?php endif; ?>
  ```

Este sistema de permissões baseado em matriz é uma aplicação avançada do padrão MVC:
- **Model**: Centraliza a lógica de autorização e define o que cada perfil pode fazer.
- **Controller**: Aplica as verificações de permissão antes de processar ações.
- **View**: Adapta a interface com base nas permissões, exibindo apenas o que o usuário tem acesso.

## 4. Vantagens da Aplicação do MVC com MySQL e Sistema de Permissões

### 4.1. Vantagens Gerais do MVC
- **Manutenção Simplificada**: A separação permite modificar a interface (`template.php`) sem afetar a lógica de negócios (`models/` e `services/`).
- **Reutilização**: A lógica de aluguel (`Carro`, `Moto`, `Locadora`) e de permissões (`Auth`) pode ser reutilizada em outras partes do sistema.
- **Escalabilidade**: É fácil adicionar novos modelos, funcionalidades ou perfis, mantendo a organização.
- **Flexibilidade de Permissões**: Novos perfis podem ser adicionados à matriz sem alterar o código principal.
- **Interface Adaptativa**: A view apresenta apenas o que o usuário tem permissão para ver, melhorando a experiência.

### 4.2. Vantagens da Integração com MySQL
- **Robustez de Dados**: O MySQL oferece transações e integridade referencial, garantindo a consistência dos dados.
- **Escalabilidade**: Suporte para volumes maiores de dados e usuários simultâneos.
- **Desempenho**: Consultas otimizadas com índices para recuperação eficiente de dados.
- **Segurança**: Autenticação e autorização no nível do banco de dados, com consultas preparadas para prevenir injeção SQL.
- **Backup e Recuperação**: Facilidade para realizar backups e restaurações de dados.

### 4.3. Melhorias Estruturais
- **Simplificação de Pastas**: A integração da interface `Locavel` no arquivo `Veiculo.php` simplifica a estrutura de diretórios.
- **Index na Raiz**: Facilita o acesso ao sistema sem necessidade de entrar em subdiretórios.
- **Inicialização Automática**: O sistema cria seu próprio banco de dados e tabelas, simplificando a instalação.

## 5. Considerações Técnicas e Melhorias

### 5.1. Técnicas de Segurança
- **Consultas Preparadas**: Todas as operações no banco de dados usam PDO com consultas preparadas para prevenir injeção SQL.
- **Senhas Hasheadas**: As senhas são armazenadas com `password_hash()` e verificadas com `password_verify()`.
- **XSS Prevention**: A interface usa `htmlspecialchars()` para evitar ataques XSS.

### 5.2. Tratamento de Erros
- **Try-Catch**: Operações de banco de dados são envolvidas em blocos try-catch para tratamento adequado de exceções.
- **Mensagens ao Usuário**: Feedback apropriado é fornecido quando ocorrem erros ou permissões são negadas.

### 5.3. Possíveis Expansões
- **Roteamento Avançado**: Implementar um sistema de rotas para melhor organização dos controladores.
- **Validação de Dados**: Adicionar validação mais robusta para entradas do usuário.
- **Logs de Atividade**: Registrar ações importantes no sistema para auditoria.
- **API REST**: Expandir o sistema para oferecer endpoints de API para integração com outras aplicações.

## 6. Conclusão

O sistema de locadora de veículos implementa o padrão MVC de forma eficaz, separando Model (dados, lógica e permissões), View (interface adaptativa) e Controller (coordenação e verificação de permissões). 

A integração com MySQL fortalece a camada de Model, proporcionando persistência robusta e escalável, enquanto a implementação do sistema de permissões baseado em matriz enriquece o padrão MVC, permitindo um controle mais granular sobre o que cada usuário pode fazer e ver.

As melhorias na estrutura do projeto, como mover o arquivo `index.php` para a raiz e incorporar a interface `Locavel` diretamente no arquivo `Veiculo.php`, simplificam a organização sem comprometer os princípios do MVC.

Este sistema demonstra como o MVC pode ser estendido para incorporar persistência robusta em banco de dados e conceitos avançados de autorização, mantendo a separação clara de responsabilidades e facilitando a manutenção e expansão do código.