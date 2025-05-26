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