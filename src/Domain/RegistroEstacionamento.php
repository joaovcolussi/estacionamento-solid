<?php
namespace App\Domain;

use DateTimeImmutable;
use Exception;

class RegistroEstacionamento
{
    public function __construct(
        private string $placa,
        private TipoVeiculo $tipoVeiculo,
        private DateTimeImmutable $dataEntrada,
        private ?DateTimeImmutable $dataSaida = null,
        private ?float $valorTotal = null,
        private ?int $id = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getPlaca(): string { return $this->placa; }
    public function getTipoVeiculo(): TipoVeiculo { return $this->tipoVeiculo; }
    public function getDataEntrada(): DateTimeImmutable { return $this->dataEntrada; }
    public function getDataSaida(): ?DateTimeImmutable { return $this->dataSaida; }
    public function getValorTotal(): ?float { return $this->valorTotal; }

    public function isFinalizado(): bool
    {
        return $this->dataSaida !== null;
    }

    public function registrarSaida(DateTimeImmutable $dataSaida): void
    {
        if ($this->isFinalizado()) {
            throw new Exception("Este registro já foi finalizado.");
        }
        if ($dataSaida < $this->dataEntrada) {
            throw new Exception("Data de saída não pode ser anterior à data de entrada.");
        }
        $this->dataSaida = $dataSaida;
    }

    public function setValorTotal(float $valor): void
    {
        $this->valorTotal = $valor;
    }

    public function calcularHorasPermanencia(): int
    {
        if (!$this->isFinalizado()) {
            return 0; 
        }

        $intervalo = $this->dataEntrada->diff($this->dataSaida);
        
        $minutosTotais = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

        if ($minutosTotais == 0) {
            return 1;
        }

        return (int)ceil($minutosTotais / 60);
    }
}