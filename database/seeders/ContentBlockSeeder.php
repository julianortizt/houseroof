<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PopulateContentBlocksSeeder::class);
    }
}
