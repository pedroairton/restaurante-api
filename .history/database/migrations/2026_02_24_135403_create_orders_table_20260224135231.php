<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained('users')
            ->onDelete('restrict');

            $table->foreignId('table_id')
            ->constrained('tables')
            ->onDelete('restrict');

            $table->enum('status', [
                'pending',    // Pedido criado, aguardando preparo
                'preparing',  // Em preparo na cozinha
                'ready',      // Pronto para servir
                'delivered',  // Entregue na mesa
                'paid',       // Pago/Finalizado
                'cancelled',  // Cancelado
            ])->default('pending');

            $table->text('observations')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
