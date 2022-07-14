<?php

use Illuminate\Database\Seeder;

class flaggedApplicantFeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data  = DB::select("SELECT ra.id as applicant_id, rf.flaged_approve_by, rf.created FROM tbl_recruitment_applicant as ra INNER JOIN tbl_recruitment_flag_applicant
        AS rf on ra.id=rf.applicant_id WHERE ra.flagged_status = 2 and rf.flag_status = 2 GROUP BY ra.id");

        foreach($data as $val) {
            if(!empty($val->applicant_id)) {
                $flagged_data  = DB::select("SELECT id from tbl_recruitment_applicant_applied_application WHERE applicant_id= ". $val->applicant_id );
                $created = date('Y-m-d h:i:s', strtotime($val->created));
                foreach($flagged_data as $flg_data) {

                    DB::table('tbl_application_history')->insert([
                        'application_id' => $flg_data->id,
                        'created_by' => $val->flaged_approve_by,
                        'created_at' => $created,
                    ]);

                    DB::table('tbl_application_history_feed')->insert([
                        'history_id' => DB::getPdo()->lastInsertId(),
                        'desc' => 'Applicant and related applications are flagged',
                        'created_by' => $val->flaged_approve_by,
                        'created_at' => $created
                    ]);
                }
            }
        }
    }
}
