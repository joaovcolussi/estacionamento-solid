<?php
namespace App\Domain;

use App\Domain\Interfaces\InterfaceCalculoTarifa;

class TarifaBicicleta implements InterfaceCalculoTarifa
{
    /**
     * 
     *
     * @param int $horas
     * @return float
     */
    public function calcular(int $horas): float
    {
        return 0.0;
    }
}