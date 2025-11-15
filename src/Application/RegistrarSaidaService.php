<?php
namespace App\Application;

use App\Domain\Interfaces\RegistroEstacionamentoRepositoryInterface;
use DateTimeImmutable;
use Exception;

class RegistrarSaidaService
{
    public function __construct(
        private RegistroEstacionamentoRepositoryInterface $repository,
        private TarifaStrategyFactory $tarifaFactory
    ) {}

    public function executar(string $placa): void
    {
        $placa = strtoupper(trim($placa));
        if (empty($placa)) {
            throw new Exception("Placa é obrigatória.");
        }

        $registro = $this->repository->findByPlacaAtiva($placa);

        if (!$registro) {
            throw new Exception("Veículo com a placa $placa não encontrado ou já finalizado.");
        }

        $registro->registrarSaida(new DateTimeImmutable());
        
        $horas = $registro->calcularHorasPermanencia();
        
        $estrategiaTarifa = $this->tarifaFactory->criar($registro->getTipoVeiculo());
        $valorTotal = $estrategiaTarifa->calcular($horas);
        
        $registro->setValorTotal($valorTotal);

        $this->repository->save($registro);
    }
}