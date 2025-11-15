<?php
namespace App\Domain\Interfaces;

use App\Domain\RegistroEstacionamento;

interface RegistroEstacionamentoRepositoryInterface
{
    public function save(RegistroEstacionamento $registro): void;
    public function findById(int $id): ?RegistroEstacionamento;
    public function findByPlacaAtiva(string $placa): ?RegistroEstacionamento;
    public function findAll(): array;
}