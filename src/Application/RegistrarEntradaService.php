<?php
namespace App\Application;

use App\Domain\Interfaces\RegistroEstacionamentoRepositoryInterface;
use App\Domain\RegistroEstacionamento;
use App\Domain\TipoVeiculo;
use DateTimeImmutable;
use Exception;

class RegistrarEntradaService
{
    public function __construct(
        private RegistroEstacionamentoRepositoryInterface $repository
    ) {}

    public function executar(string $placa, string $tipo): void
    {
        $placa = strtoupper(trim($placa));
        if (empty($placa) || empty($tipo)) {
            throw new Exception("Placa e tipo são obrigatórios.");
        }

        $registroAtivo = $this->repository->findByPlacaAtiva($placa);
        if ($registroAtivo) {
            throw new Exception("Veículo com a placa $placa já está no estacionamento.");
        }

        $tipoVeiculo = TipoVeiculo::from($tipo);

        $registro = new RegistroEstacionamento(
            placa: $placa,
            tipoVeiculo: $tipoVeiculo,
            dataEntrada: new DateTimeImmutable()
        );

        $this->repository->save($registro);
    }
}