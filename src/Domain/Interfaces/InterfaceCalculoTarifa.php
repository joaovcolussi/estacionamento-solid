<?php
namespace App\Domain\Interfaces;

interface InterfaceCalculoTarifa
{
    public function calcular(int $horas): float;
}