# Controle de Estacionamento Inteligente (PHP + SOLID + SQLite)

Sistema desenvolvido em **PHP 8**, utilizando **SQLite**, **Composer (PSR-4)** e uma arquitetura em camadas seguindo princípios **SOLID**, **Clean Code**, **DRY** e **KISS**.

O objetivo é registrar entradas e saídas de veículos, calcular tarifas automaticamente e gerar relatórios completos sobre a utilização do estacionamento.

---

##  Integrantes do Grupo

1. Alexandre José Gomes | 1986088
2. João Victor Colussi | 2003753
3. Vinicius Press | 2003646


---

##  Arquitetura do Projeto

```
estacionamento-solid/
├── public/
│   └── index.php
├── src/
│   ├── Application/
│   │   ├── GerarRelatorioService.php
│   │   ├── RegistrarEntradaService.php
│   │   ├── RegistrarSaidaService.php
│   │   └── TarifaStrategyFactory.php
│   ├── Domain/
│   │   ├── Interfaces/
│   │   │   ├── InterfaceCalculoTarifa.php
│   │   │   └── RegistroEstacionamentoRepositoryInterface.php
│   │   ├── RegistroEstacionamento.php
│   │   ├── TipoVeiculo.php
│   │   ├── TarifaCarro.php
│   │   ├── TarifaMoto.php
│   │   ├── TarifaCaminhao.php
│   │   └── TarifaBicicleta.php
│   └── Infra/
│       ├── database/
│       ├── SqliteConnection.php
│       └── SqliteRegistroEstacionamentoRepository.php
├── init_db.php
├── composer.json
└── vendor/
```

---

##  Tipos de Veículos e Tarifas

| Veículo     | Tarifa por hora |
|-------------|------------------|
| Carro       | R$ 5             |
| Moto        | R$ 3             |
| Caminhão    | R$ 10            |
| Bicicleta   | **R$ 0 (Grátis)** |

Regras:
- Tempo arredondado **para cima** (`ceil()`)
- Cálculo por meio do **Strategy Pattern**

---

## ▶️ Como Executar

### 1️⃣ Instalar dependências
```
composer install
```

### 2️⃣ Criar banco SQLite
```
php init_db.php
```

Será criado:
```
/src/Infra/database/database.sqlite
```

### 3️⃣ Rodar via XAMPP
Mova o projeto para:
```
C:/xampp/htdocs/estacionamento-solid
```

Acesse:
```
http://localhost/estacionamento-solid/public
```

---

## 🔄 Fluxo de Funcionamento

### ✔ Registrar Entrada  
Armazena tipo de veículo e horário inicial.

### ✔ Registrar Saída  
Calcula horas (ceil), aplica tarifa e grava valor final.

### ✔ Gerar Relatório  
Total por tipo, faturamento por tipo e total geral.

---

## 🧱 SOLID

- **SRP:** classes com uma responsabilidade  
- **OCP:** fácil adicionar novos tipos de veículo  
- **LSP:** todas estratégias substituem a interface base  
- **ISP:** interfaces pequenas e específicas  
- **DIP:** serviços dependem de interfaces  

---

## 📄 Licença
Projeto acadêmico para fins educacionais.
