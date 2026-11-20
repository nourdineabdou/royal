<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nom d'utilisateur pour se connecter sans e-mail. Par défaut il vient du nom
 * (« Abdel Rahim » → « abdel.rahim ») ; chacun peut le changer (ex : un numéro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('name');
        });

        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $u) {
            DB::table('users')->where('id', $u->id)->update(['username' => User::uniqueUsernameFrom($u->name, $u->id)]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
