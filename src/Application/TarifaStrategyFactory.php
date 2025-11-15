<?php
namespace App\Application;

use App\Domain\Interfaces\InterfaceCalculoTarifa;
use App\Domain\TarifaCarro;
use App\Domain\TarifaMoto;
use App\Domain\TarifaCaminhao;
use App\Domain\TarifaBicicleta; // <-- ADICIONAR IMPORT
use App\Domain\TipoVeiculo;
use Exception;

class TarifaStrategyFactory
{
    public function criar(TipoVeiculo $tipo): InterfaceCalculoTarifa
    {
        return match ($tipo) {
            TipoVeiculo::CARRO => new TarifaCarro(),
            TipoVeiculo::MOTO => new TarifaMoto(),
            TipoVeiculo::CAMINHAO => new TarifaCaminhao(),
            TipoVeiculo::BICICLETA => new TarifaBicicleta(),
            default => throw new Exception("Estratégia de tarifa não encontrada para o tipo: " . $tipo->value),
        };
    }
}