<?php
// --- INICIALIZAÇÃO E PROCESSAMENTO ---

// Habilita a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega o Autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Importa todas as classes necessárias
use App\Infra\SqliteConnection;
use App\Infra\SqliteRegistroEstacionamentoRepository;
use App\Application\TarifaStrategyFactory;
use App\Application\RegistrarEntradaService;
use App\Application\RegistrarSaidaService;
use App\Application\GerarRelatorioService;

$errorMessage = null;
$successMessage = null;
$relatorio = [
    'registros' => [],
    'faturamentoPorTipo' => [],
    'totalVeiculosPorTipo' => [],
    'faturamentoTotal' => 0.0,
    'veiculosNoPatio' => 0,
];

try {
    // "Injeção de Dependência" manual (Poor Man's DI)
    $connection = SqliteConnection::getInstance();
    $repository = new SqliteRegistroEstacionamentoRepository($connection);
    $tarifaFactory = new TarifaStrategyFactory();

    // Cria os serviços, injetando as dependências
    $entradaService = new RegistrarEntradaService($repository);
    $saidaService = new RegistrarSaidaService($repository, $tarifaFactory);
    $relatorioService = new GerarRelatorioService($repository);
    
    // Processamento de Formulários (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'registrar_entrada') {
                $placa = $_POST['placa'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $entradaService->executar($placa, $tipo);
                $successMessage = "Veículo de placa $placa registrado com sucesso!";

            } elseif ($action === 'registrar_saida') {
                $placa = $_POST['placa_saida'] ?? '';
                $saidaService->executar($placa);
                $successMessage = "Saída do veículo $placa registrada com sucesso!";
            }

        } catch (Exception $e) {
            // Captura erros de regras de negócio (ex: "Veículo já está no pátio")
            $errorMessage = $e->getMessage();
        }
    }

    // Carrega dados para a exibição (GET ou após o POST)
    $relatorio = $relatorioService->executar();

} catch (Exception $e) {
    // Captura erros fatais na inicialização
    $errorMessage = "Erro fatal na aplicação: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estacionamento Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-black text-white p-8 font-sans">

    <div class="container mx-auto max-w-7xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-gray-900 to-black rounded-full mb-4 border border-gray-700 shadow-2xl">
                <i class="fas fa-car text-2xl text-gray-300"></i>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent tracking-tight">
                CONTROLE DE ESTACIONAMENTO
            </h1>
            <p class="text-gray-500 mt-2">Sistema Inteligente de Gestão</p>
        </div>

        <!-- Formulários -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <!-- Entrada -->
            <div class="bg-gradient-to-br from-gray-950 to-black p-8 rounded-xl shadow-2xl border border-gray-800 hover:border-gray-700 transition-all duration-300">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-green-900/30 rounded-lg flex items-center justify-center mr-4 border border-green-800">
                        <i class="fas fa-arrow-down text-green-400 text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-white">Registrar Entrada</h2>
                </div>
                <form action="index.php" method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="registrar_entrada">
                    <div>
                        <label for="placa" class="block text-sm font-medium text-gray-400 mb-2 uppercase tracking-wider">
                        <i class="fas fa-id-card mr-2"></i>Placa de Veiculo / Cor da Bicicleta & Dono
                        </label>
                        <input type="text" name="placa" id="placa" placeholder="ABC-1234" 
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-white placeholder-gray-500 transition-all duration-200" 
                               required>
                    </div>
                    <div>
                        <label for="tipo" class="block text-sm font-medium text-gray-400 mb-2 uppercase tracking-wider">
                            <i class="fas fa-motorcycle mr-2"></i>Tipo de Veículo
                        </label>
                        <select name="tipo" id="tipo" 
                                class="w-full px-4 py-3 bg-gray-900 border border-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-white transition-all duration-200" 
                                required>
                            <option value="" class="bg-gray-900">Selecione...</option>
                            <option value="carro" class="bg-gray-900"> Carro (R$ 5/h)</option>
                            <option value="moto" class="bg-gray-900"> Moto (R$ 3/h)</option>
                            <option value="caminhao" class="bg-gray-900"> Caminhão (R$ 10/h)</option>
                            <option value="bicicleta" class="bg-gray-900"> Bicicleta (Grátis)</option>
                        </select>
                    </div>
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-3 px-6 rounded-lg font-semibold uppercase tracking-wide hover:from-green-700 hover:to-green-800 transform hover:scale-[1.02] transition-all duration-200 shadow-lg hover:shadow-green-900/30">
                        <i class="fas fa-check mr-2"></i>Registrar Entrada
                    </button>
                </form>
            </div>

            <!-- Saída -->
            <div class="bg-gradient-to-br from-gray-950 to-black p-8 rounded-xl shadow-2xl border border-gray-800 hover:border-gray-700 transition-all duration-300">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-red-900/30 rounded-lg flex items-center justify-center mr-4 border border-red-800">
                        <i class="fas fa-arrow-up text-red-400 text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-white">Registrar Saída</h2>
                </div>
                <form action="index.php" method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="registrar_saida">
                    <div>
                        <label for="placa_saida" class="block text-sm font-medium text-gray-400 mb-2 uppercase tracking-wider">
                            <i class="fas fa-id-card mr-2"></i>Placa do Veículo (saída)
                        </label>
                        <input type="text" name="placa_saida" id="placa_saida" placeholder="ABC-1234" 
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-white placeholder-gray-500 transition-all duration-200" 
                               required>
                    </div>
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white py-3 px-6 rounded-lg font-semibold uppercase tracking-wide hover:from-red-700 hover:to-red-800 transform hover:scale-[1.02] transition-all duration-200 shadow-lg hover:shadow-red-900/30">
                        <i class="fas fa-calculator mr-2"></i>Registrar Saída e Calcular
                    </button>
                </form>
            </div>
        </div>

        <!-- Relatório -->
        <div class="bg-gradient-to-br from-gray-950 to-black rounded-xl shadow-2xl border border-gray-800 overflow-hidden">
            <!-- Header do Relatório -->
            <div class="bg-gray-900 px-8 py-6 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-900/30 rounded-lg flex items-center justify-center mr-4 border border-blue-800">
                            <i class="fas fa-chart-line text-blue-400 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-semibold text-white">Relatório Geral</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total de Registros</p>
                        <p class="text-2xl font-bold text-white"><?= count($relatorio['registros']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Cards de Faturamento (DINÂMICO - OCP) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6 bg-black/50">
                <div class="bg-gray-900 p-5 rounded-lg border border-gray-800 hover:border-gray-700 transition-all duration-300 w-full">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Faturamento Total</span>
                            <span class="block text-2xl font-bold text-white">R$ <?= number_format($relatorio['faturamentoTotal'], 2, ',', '.') ?></span>
                        </div>
                        <div class="w-10 h-10 bg-green-900/30 rounded-lg flex items-center justify-center border border-green-800">
                            <i class="fas fa-dollar-sign text-green-400"></i>
                        </div>
                    </div>
                </div>
            
                <?php foreach ($relatorio['faturamentoPorTipo'] as $tipo => $faturamento): 
                    if ($tipo === 'bicicleta') {
                        continue;
                    }

                    $icon = match($tipo) {
                        'carro' => ['icon' => 'car', 'color' => 'blue', 'bg' => 'blue-900/30', 'border' => 'border-blue-800', 'text' => 'text-blue-400'],
                        'moto' => ['icon' => 'motorcycle', 'color' => 'purple', 'bg' => 'purple-900/30', 'border' => 'border-purple-800', 'text' => 'text-purple-400'],
                        'caminhao' => ['icon' => 'truck', 'color' => 'orange', 'bg' => 'orange-900/30', 'border' => 'border-orange-800', 'text' => 'text-orange-400'],
                        default => ['icon' => 'car', 'color' => 'gray', 'bg' => 'gray-800/30', 'border' => 'border-gray-700', 'text' => 'text-gray-400'],
                    };
                ?>

                <div class="bg-gray-900 p-5 rounded-lg border border-gray-800 hover:border-gray-700 transition-all duration-300 w-full">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Faturamento <?= ucfirst($tipo) ?></span>
                            <span class="block text-xl font-bold text-<?= $icon['color'] ?>-400">R$ <?= number_format($faturamento, 2, ',', '.') ?></span>
                        </div>
                        <div class="w-10 h-10 bg-<?= $icon['bg'] ?> rounded-lg flex items-center justify-center border <?= $icon['border'] ?>">
                            <i class="fas fa-<?= $icon['icon'] ?> <?= $icon['text'] ?>"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Tabela -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-800">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Placa</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrada</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Saída</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Valor Pago</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php if (empty($relatorio['registros'])): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3 block opacity-50"></i>
                                    Nenhum veículo registrado ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php foreach ($relatorio['registros'] as $registro):
                            $tipoIcon = match($registro->getTipoVeiculo()->value) {
                                'carro' => 'car',
                                'moto' => 'motorcycle',
                                'caminhao' => 'truck',
                                'bicicleta' => 'bicycle',
                                default => 'car',
                            };
                        ?>
                        <tr class="hover:bg-gray-900/50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($registro->isFinalizado()): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-900/30 text-red-400 border border-red-800">
                                        Finalizado
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/30 text-green-400 border border-green-800">
                                        No Pátio
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-lg font-bold text-white">
                                <?= htmlspecialchars($registro->getPlaca()) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400">
                                <i class="fas fa-<?= $tipoIcon ?> w-5 text-center mr-2"></i>
                                <?= ucfirst(htmlspecialchars($registro->getTipoVeiculo()->value)) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400 font-mono">
                                <?= $registro->getDataEntrada()->format('d/m/Y H:i') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400 font-mono">
                                <?= $registro->getDataSaida() ? $registro->getDataSaida()->format('d/m/Y H:i') : '---' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-white text-lg">
                                <?= $registro->getValorTotal() !== null ? 'R$ ' . number_format($registro->getValorTotal(), 2, ',', '.') : '---' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-600 text-sm">
            <p><i class="fas fa-code mr-1"></i> Sistema desenvolvido com padrões SOLID</p>
        </div>
    </div>

    <!-- Script para Alertas -->
    <script>
        <?php if ($successMessage): ?>
            Swal.fire({
                icon: 'success',
                title: 'Operação Concluída!',
                text: '<?= addslashes($successMessage) ?>',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                background: '#1a1a1a',
                color: '#fff',
                iconColor: '#10b981'
            });
        <?php elseif ($errorMessage): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro na Operação!',
                text: '<?= addslashes($errorMessage) ?>',
                background: '#1a1a1a',
                color: '#fff',
                iconColor: '#ef4444',
                confirmButtonColor: '#ef4444'
            });
        <?php endif; ?>
    </script>
</body>
</html>