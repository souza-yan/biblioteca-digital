<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false);
        });

        DB::table('users')->whereIn('role', ['staff', 'teacher'])->select(['id', 'password'])
            ->chunkById(500, function (Collection $users): void {
                foreach ($users as $user) {
                    if (Hash::check((string) config('accounts.default_password'), $user->password)) {
                        DB::table('users')->where('id', $user->id)->update(['must_change_password' => true]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
