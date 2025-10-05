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
        Schema::create('event_duties', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Event::class);
            $table->foreignIdFor(\App\Models\Duty::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_duties');
    }
};
