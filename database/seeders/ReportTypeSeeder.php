<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportType;
use Carbon\Carbon;

class ReportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 48; $i++) {
            $deadline = Carbon::now('Asia/Manila')->addWeeks($i)->endOfWeek()->toDateString();

            ReportType::create([
                'name' => 'Kalinisan',
                'frequency' => 'weekly',
                'deadline' => $deadline,
            ]);
        }
    }
}
