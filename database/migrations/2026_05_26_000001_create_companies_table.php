<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Raison sociale
            $table->enum('legal_form', ['SA', 'SARL', 'SARL-U', 'GIE', 'Autre'])->nullable();
            $table->string('nif')->unique()->nullable();     // Numéro d'Identification Fiscale
            $table->string('rc')->nullable();                // Registre du Commerce
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();              // Chemin du fichier logo
            $table->date('fiscal_year_start')->nullable();
            $table->date('fiscal_year_end')->nullable();
            $table->enum('tax_regime', ['reel_normal', 'reel_intermediaire', 'forfait'])->nullable();
            $table->string('bank_name')->nullable();
            $table->string('rib')->nullable();
            $table->string('currency', 3)->default('MRU');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
