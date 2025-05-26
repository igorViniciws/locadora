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