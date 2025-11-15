<?php
namespace App\Application;

use App\Domain\Interfaces\RegistroEstacionamentoRepositoryInterface;
use App\Domain\TipoVeiculo;

class GerarRelatorioService
{
    public function __construct(
        private RegistroEstacionamentoRepositoryInterface $repository
    ) {}

    public function executar(): array
    {
        $registros = $this->repository->findAll();

        $faturamentoPorTipo = [];
        $totalVeiculosPorTipo = [];

        foreach (TipoVeiculo::cases() as $tipoEnum) {
            $faturamentoPorTipo[$tipoEnum->value] = 0.0;
            $totalVeiculosPorTipo[$tipoEnum->value] = 0;
        }

        $faturamentoTotal = 0.0;
        $veiculosNoPatio = 0;

        foreach ($registros as $registro) {
            $tipo = $registro->getTipoVeiculo()->value;

            if (array_key_exists($tipo, $totalVeiculosPorTipo)) {
                $totalVeiculosPorTipo[$tipo]++;
            }

            if ($registro->isFinalizado()) {
                if (array_key_exists($tipo, $faturamentoPorTipo)) {
                    $faturamentoPorTipo[$tipo] += $registro->getValorTotal();
                }
                $faturamentoTotal += $registro->getValorTotal();
            } else {
                $veiculosNoPatio++;
            }
        }

        return [
            'registros' => $registros,
            'faturamentoPorTipo' => $faturamentoPorTipo,
            'totalVeiculosPorTipo' => $totalVeiculosPorTipo,
            'faturamentoTotal' => $faturamentoTotal,
            'veiculosNoPatio' => $veiculosNoPatio,
        ];
    }
}