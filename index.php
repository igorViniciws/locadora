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