<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BasalMetabolicRate;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        User::create([
            'name' => 'Gabriel',
            'email' => 'gab-oliveira@hotmail.com',
            'password' => Hash::make('123456789'),
        ]);

        BasalMetabolicRate::create([
            'user_id' => 1,
            'user_name' => 'Gabriel',
            'gender' => 'Masculino',
            'age' => 25,
            'weight' => 63,
            'stature' => 1.68,
            'activity_rate_factor' => 1.38,
            'objective' => 'Aumentar peso lentamente',
            'type_of_diet' => 'Padrão',
            'imc' => 22.30,
            'water' => 2205,
            'basal_metabolic_rate' => 0,
            'daily_calories' => 2427,
            'daily_protein' => 116.60,
            'daily_carbohydrate' => 291.70,
            'daily_fat' => 78.80,
            'daily_protein_kcal' => 504,
            'daily_carbohydrate_kcal' => 1194.60,
            'daily_fat_kcal' => 728
        ]);

    }
}
