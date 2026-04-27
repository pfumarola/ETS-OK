<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('convocazioni', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 40);
            $table->string('titolo', 255)->nullable();
            $table->dateTime('scheduled_at');
            $table->string('luogo', 255);
            $table->longText('ordine_del_giorno');
            $table->longText('testo_email')->nullable();
            $table->string('stato', 20)->default('bozza');
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'stato']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocazioni');
    }
};
