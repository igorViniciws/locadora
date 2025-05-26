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