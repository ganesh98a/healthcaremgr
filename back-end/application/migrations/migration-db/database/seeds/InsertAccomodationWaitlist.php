<?php

use Illuminate\Database\Seeder;

class InsertAccomodationWaitlist extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $obj = array(
                "id"=>"5",
                "name"=>"Accomodation Waitlist",
                "key_name"=>"accomodation",
                "order_ref"=>"80",
                "archive"=>"0"
        );

        DB::table('tbl_lead_status')->updateOrInsert(['id' => $obj['id']], $obj);

    }
}
