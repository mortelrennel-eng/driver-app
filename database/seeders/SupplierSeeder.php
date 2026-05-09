<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = [
            'APOLLO ZONE',
            'MEGA GRANDIS',
            'LUCKY TWO',
            'SHARON HUNG',
            'A. BONIFACIO AUTO',
            'NELSON PROVIDO',
            'SAUYO MACHINE SHOP',
            'Q.C. TOYORAMA MOTOR CORP.',
            'WYL MOTORS',
            'ABC AUTO PARTS',
            'AMONLATHE WORKS',
            'VISCO MOTOR SUPPLY',
            'T.A. FRESCO CORP.',
            'TRACKSPEED',
            'BEST COLT',
            'WEST ELM TREE',
            'AUTOPHIL ZONE SALES',
            'REGASCO GASOLINE',
            'ANDALUCIA'
        ];

        foreach ($suppliers as $name) {
            \App\Models\Supplier::firstOrCreate(['name' => $name]);
        }
    }
}
