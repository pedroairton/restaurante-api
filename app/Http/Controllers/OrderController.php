<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['table', 'items.product', 'user'])
            ->orderByDesc('created_at');

        // filtro p/ status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // filtro p/ mesa
        if ($request->has('table_id')) {
            $query->where('table_id', $request->table_id);
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'table_id' => $validated['table_id'],
                'observations' => $validated['observations'] ?? null,
                'status' => 'pending',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $subtotal = $product->price * $item['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total' => $total]);

            Table::where('id', $validated['table_id'])
                ->update(['status' => 'occupied']);

            return response()->json(
                $order->load(['table', 'items.product', 'user']),
                201
            );
        });
    }
    public function show(Order $order)
    {
        return response()->json($order->load(['table', 'items.product', 'user']));
    }
    public function update(StoreOrderRequest $request, Order $order)
    {
        // Só permite editar pedidos pendentes ou em preparo
        if (!in_array($order->status, ['pending', 'preparing'])) {
            return response()->json([
                'message' => 'Só é possível editar pedidos pendentes ou em preparo.',
            ], 422);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $order) {

            // 1. Remove todos os itens antigos
            $order->items()->delete();

            // 2. Cria os novos itens
            $total = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // 3. Atualiza o pedido
            $order->update([
                'table_id' => $validated['table_id'],
                'observations' => $validated['observations'] ?? null,
                'total' => $total,
            ]);

            // 4. Gerencia status das mesas se a mesa mudou
            if ($order->wasChanged('table_id')) {
                $oldTableId = $order->getOriginal('table_id');

                // Libera mesa antiga se não tem outros pedidos ativos
                $hasActiveOrders = Order::where('table_id', $oldTableId)
                    ->whereNotIn('status', ['paid', 'cancelled'])
                    ->where('id', '!=', $order->id)
                    ->exists();

                if (!$hasActiveOrders) {
                    Table::where('id', $oldTableId)->update(['status' => 'available']);
                }

                // Ocupa nova mesa
                Table::where('id', $validated['table_id'])->update(['status' => 'occupied']);
            }

            return response()->json([
                'message' => 'Pedido atualizado com sucesso.',
                'order' => $order->load(['table', 'items.product', 'user']),
            ]);
        });
    }
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $newStatus = $request->validated()['status'];

        $order->update(['status' => $newStatus]);

        if (in_array($newStatus, ['paid', 'cancelled'])) {
            $activeOrdersOnTable = Order::where('table_id', $order->table_id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->where('id', '!=', $order->id)
                ->exists();

            if (!$activeOrdersOnTable) {
                Table::where('id', $order->table_id)
                    ->update(['status' => 'available']);
            }
        }

        return response()->json([
            'message' => 'Status alterado com sucesso',
            'order' => $order->load(['table', 'items.product']),
        ]);
    }

    public function destroy(Order $order)
    {
        if ($order->status === 'paid') {
            return response()->json([
                'message' => 'Não é possível excluir um pedido pago'
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        // liberar mesa se não houver outros pedidos ativos
        $activeOrdersOnTable = Order::where('table_id', $order->table_id)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->where('id', '!=', $order->id)
            ->exists();

        if (!$activeOrdersOnTable) {
            Table::where('id', $order->table_id)
                ->update(['status' => 'available']);
        }

        return response()->json([
            'message' => 'Pedido cancelado com sucesso',
        ]);
    }
}
