<?php
namespace App\Domain;

use App\Domain\Interfaces\InterfaceCalculoTarifa;

class TarifaCaminhao implements InterfaceCalculoTarifa
{
    private const TARIFA_POR_HORA = 10.0;

    public function calcular(int $horas): float
    {
        return $horas * self::TARIFA_POR_HORA;
    }
}