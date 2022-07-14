<?php

use Illuminate\Database\Seeder;

class CostCode extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ccs = ['Disability', 'DDSO', 'Day Services', 'Welfare', 'Home Care'];
        foreach ($ccs as $cc) {
            DB::table('tbl_finance_cost_code')->insert([
                'title' => $cc,
                'created_at' => date('Y-m-d h:i:s')
            ]);
        }
    }
}
