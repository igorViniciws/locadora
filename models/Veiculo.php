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