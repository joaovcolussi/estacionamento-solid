<?php
namespace App\Infra;

use App\Domain\RegistroEstacionamento;
use App\Domain\Interfaces\RegistroEstacionamentoRepositoryInterface;
use App\Domain\TipoVeiculo;
use DateTimeImmutable;
use PDO;

class SqliteRegistroEstacionamentoRepository implements RegistroEstacionamentoRepositoryInterface
{
    private PDO $pdo;

    public function __construct(SqliteConnection $connection)
    {
        $this->pdo = $connection->getConnection();
    }

    public function save(RegistroEstacionamento $registro): void
    {
        if ($registro->getId()) {
            $this->update($registro);
        } else {
            $this->insert($registro);
        }
    }

    private function insert(RegistroEstacionamento $registro): void
    {
        $sql = "INSERT INTO registros (placa, tipo_veiculo, data_entrada) VALUES (:placa, :tipo, :entrada)";
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':placa' => $registro->getPlaca(),
            ':tipo' => $registro->getTipoVeiculo()->value,
            ':entrada' => $registro->getDataEntrada()->format('Y-m-d H:i:s')
        ]);
    }

    private function update(RegistroEstacionamento $registro): void
    {
        $sql = "UPDATE registros SET 
                    placa = :placa, 
                    tipo_veiculo = :tipo, 
                    data_entrada = :entrada, 
                    data_saida = :saida, 
                    valor_total = :valor 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $registro->getId(),
            ':placa' => $registro->getPlaca(),
            ':tipo' => $registro->getTipoVeiculo()->value,
            ':entrada' => $registro->getDataEntrada()->format('Y-m-d H:i:s'),
            ':saida' => $registro->getDataSaida() ? $registro->getDataSaida()->format('Y-m-d H:i:s') : null,
            ':valor' => $registro->getValorTotal()
        ]);
    }

    public function findById(int $id): ?RegistroEstacionamento
    {
        $stmt = $this->pdo->prepare("SELECT * FROM registros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findByPlacaAtiva(string $placa): ?RegistroEstacionamento
    {
        $stmt = $this->pdo->prepare("SELECT * FROM registros WHERE placa = :placa AND data_saida IS NULL LIMIT 1");
        $stmt->execute([':placa' => $placa]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM registros ORDER BY data_entrada DESC");
        $registros = [];
        foreach ($stmt->fetchAll() as $row) {
            $registros[] = $this->mapRowToEntity($row);
        }
        return $registros;
    }
    
    private function mapRowToEntity(array $row): RegistroEstacionamento
    {
        return new RegistroEstacionamento(
            id: $row['id'],
            placa: $row['placa'],
            tipoVeiculo: TipoVeiculo::from($row['tipo_veiculo']),
            dataEntrada: new DateTimeImmutable($row['data_entrada']),
            dataSaida: $row['data_saida'] ? new DateTimeImmutable($row['data_saida']) : null,
            valorTotal: $row['valor_total']
        );
    }
}