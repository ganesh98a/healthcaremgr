<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentLineItemByCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $res = DB::select("SELECT sc.*, spm.support_purpose_id, socm.support_outcome_domain_id FROM tbl_finance_support_category as sc
        INNER JOIN tbl_finance_support_purpose_mapping as spm ON spm.support_category_id = sc.id AND spm.archive = 0
        LEFT JOIN tbl_finance_support_outcome_mapping as socm ON socm.support_category_id = sc.id AND socm.archive = 0
        WHERE sc.archive = 0 ");
        if($res) {
            foreach($res as $row) {
                $support_category = $row->id;
                $support_purpose = $row->support_purpose_id;
                $support_outcome = $row->support_outcome_domain_id;
                $line_item_name = $row->name;
                $prefix = $row->prefix;
                $prefix = str_pad($prefix, 2, '0', STR_PAD_LEFT);

                $check = DB::select("SELECT id FROM `tbl_finance_line_item` where line_item_number = '".$prefix."'");

                if(!$check){
                    DB::select("INSERT INTO `tbl_finance_line_item` (funding_type, line_item_number, support_category, support_purpose, line_item_name, upper_price_limit, support_outcome_domain) values(1, '".$prefix."', '".$support_category."', $support_purpose, '".$line_item_name."', '0.00', '".$support_outcome."')");

                }

            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
