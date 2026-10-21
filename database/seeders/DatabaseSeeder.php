<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CaraguatatubaSchoolsSeeder::class,
            CategorySeeder::class,
            MaterialSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
