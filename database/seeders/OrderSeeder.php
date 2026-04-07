<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private User $user;
    private array $products;
    private array $tables;
    
    // Distribuição de status dos pedidos
    private array $statusDistribution = [
        'paid'       => 85, // 85% pagos
        'cancelled'  => 5,  // 5% cancelados
        'pending'    => 5,  // 5% pendentes
        'preparing'  => 3,  // 3% em preparo
        'ready'      => 1,  // 1% prontos
        'delivered'  => 1,  // 1% entregues
    ];

    public function run(): void
    {
        $this->command->info('🍽️  Iniciando seeder de pedidos...');

        // Cache de dados
        $this->user     = User::first();
        $this->products = Product::all()->toArray();
        $this->tables   = Table::all()->toArray();

        // Período: 01/01/2026 até 07/04/2026
        $startDate = Carbon::parse('2026-01-01');
        $endDate   = Carbon::parse('2026-04-07');
        $totalDays = $startDate->diffInDays($endDate) + 1; // 97 dias

        $this->command->info("📅 Período: {$startDate->format('d/m/Y')} até {$endDate->format('d/m/Y')} ({$totalDays} dias)");

        // Gera 200 pedidos distribuídos
        $totalOrders = 200;
        $ordersCreated = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($totalOrders);
        $progressBar->start();

        for ($i = 0; $i < $totalOrders; $i++) {
            // Data aleatória dentro do período
            $randomDays = rand(0, $totalDays - 1);
            $orderDate  = $startDate->copy()->addDays($randomDays);

            // Horário aleatório de funcionamento (11h às 23h)
            $orderDate->setHour(rand(11, 22))
                      ->setMinute(rand(0, 59))
                      ->setSecond(rand(0, 59));

            // Define status baseado na distribuição
            $status = $this->getRandomStatus($orderDate);

            // Cria o pedido
            $this->createOrder($orderDate, $status);

            $ordersCreated++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);
        $this->command->info("✅ {$ordersCreated} pedidos criados com sucesso!");

        // Estatísticas
        $this->showStatistics();
    }

    /**
     * Define status baseado na data e distribuição
     */
    private function getRandomStatus(Carbon $date): string
    {
        // Pedidos de hoje só podem estar em andamento
        if ($date->isToday()) {
            $todayStatuses = ['pending', 'preparing', 'ready', 'delivered'];
            return $todayStatuses[array_rand($todayStatuses)];
        }

        // Pedidos antigos: distribuição normal
        $rand = rand(1, 100);
        $accumulated = 0;

        foreach ($this->statusDistribution as $status => $percentage) {
            $accumulated += $percentage;
            if ($rand <= $accumulated) {
                return $status;
            }
        }

        return 'paid';
    }

    /**
     * Cria um pedido completo com itens
     */
    private function createOrder(Carbon $orderDate, string $status): void
    {
        // Mesa aleatória
        $table = $this->tables[array_rand($this->tables)];

        // Número aleatório de itens (1 a 8 itens por pedido)
        $itemsCount = $this->getRandomItemsCount();

        // Seleciona produtos aleatórios sem repetição
        $selectedProducts = $this->getRandomProducts($itemsCount);

        // Cria o pedido
        $order = Order::create([
            'user_id'      => $this->user->id,
            'table_id'     => $table['id'],
            'status'       => $status,
            'observations' => $this->getRandomObservation(),
            'total'        => 0, // Será calculado depois
            'created_at'   => $orderDate,
            'updated_at'   => $orderDate,
        ]);

        $total = 0;

        // Cria os itens do pedido
        foreach ($selectedProducts as $product) {
            $quantity   = rand(1, 4); // 1 a 4 unidades de cada item
            $unitPrice  = $product['price'];
            $subtotal   = $quantity * $unitPrice;

            $order->items()->create([
                'product_id' => $product['id'],
                'quantity'   => $quantity,
                'unit_price' => $unitPrice,
                'subtotal'   => $subtotal,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            $total += $subtotal;
        }

        // Atualiza o total do pedido
        $order->update(['total' => $total]);
    }

    /**
     * Define quantidade de itens baseado em distribuição realista
     */
    private function getRandomItemsCount(): int
    {
        $rand = rand(1, 100);

        return match(true) {
            $rand <= 15 => 1,  // 15% - 1 item
            $rand <= 45 => 2,  // 30% - 2 itens
            $rand <= 75 => 3,  // 30% - 3 itens
            $rand <= 90 => 4,  // 15% - 4 itens
            $rand <= 97 => 5,  // 7%  - 5 itens
            default     => rand(6, 8), // 3% - 6 a 8 itens
        };
    }

    /**
     * Seleciona produtos aleatórios sem repetição
     */
    private function getRandomProducts(int $count): array
    {
        $shuffled = $this->products;
        shuffle($shuffled);
        
        return array_slice($shuffled, 0, $count);
    }

    /**
     * Gera observações aleatórias (30% dos pedidos têm observações)
     */
    private function getRandomObservation(): ?string
    {
        if (rand(1, 100) > 30) {
            return null;
        }

        $observations = [
            'Sem cebola',
            'Bem passado',
            'Mal passado',
            'Ao ponto',
            'Sem pimenta',
            'Extra molho',
            'Sem queijo',
            'Pouco sal',
            'Sem alho',
            'Molho à parte',
            'Sem tomate',
            'Bebida gelada',
            'Pizza bem assada',
            'Massa fina',
            'Batata extra crocante',
            'Salada sem cebola roxa',
            'Ponto da carne ao ponto para mal',
            'Sobremesa para depois do prato principal',
            'Suco sem açúcar',
            'Café bem forte',
        ];

        // 20% de chance de ter 2 observações combinadas
        if (rand(1, 100) <= 20) {
            shuffle($observations);
            return $observations[0] . ', ' . $observations[1];
        }

        return $observations[array_rand($observations)];
    }

    /**
     * Exibe estatísticas dos pedidos criados
     */
    private function showStatistics(): void
    {
        $this->command->newLine();
        $this->command->info('📊 Estatísticas:');
        $this->command->newLine();

        $stats = Order::selectRaw('
            status,
            COUNT(*) as count,
            SUM(total) as revenue,
            AVG(total) as avg_ticket
        ')
        ->groupBy('status')
        ->orderByDesc('count')
        ->get();

        $table = [];
        foreach ($stats as $stat) {
            $table[] = [
                'Status'        => ucfirst($stat->status),
                'Pedidos'       => $stat->count,
                'Receita'       => 'R$ ' . number_format($stat->revenue, 2, ',', '.'),
                'Ticket Médio'  => 'R$ ' . number_format($stat->avg_ticket, 2, ',', '.'),
            ];
        }

        $this->command->table(
            ['Status', 'Pedidos', 'Receita', 'Ticket Médio'],
            $table
        );

        // Total geral
        $totalRevenue = Order::where('status', 'paid')->sum('total');
        $totalOrders  = Order::count();
        $paidOrders   = Order::where('status', 'paid')->count();

        $this->command->newLine();
        $this->command->info("💰 Receita Total (Pedidos Pagos): R$ " . number_format($totalRevenue, 2, ',', '.'));
        $this->command->info("📦 Total de Pedidos: {$totalOrders}");
        $this->command->info("✅ Pedidos Pagos: {$paidOrders}");
        $this->command->info("🎫 Ticket Médio Geral: R$ " . number_format($totalRevenue / max($paidOrders, 1), 2, ',', '.'));
    }
}
