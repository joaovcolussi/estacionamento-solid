<?php
namespace App\Domain;

enum TipoVeiculo: string
{
    case CARRO = 'carro';
    case MOTO = 'moto';
    case CAMINHAO = 'caminhao';
    case BICICLETA = 'bicicleta';
}