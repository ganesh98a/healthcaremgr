<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceAgreementGoalsToGoalsMaster extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $sa_goals = DB::select("select * from tbl_service_agreement_goal WHERE is_migrated != 1");
        if (!empty($sa_goals)) {
            foreach ($sa_goals as $i => $sa_goal) {
                echo "migrating row=> $sa_goal->id \n";
                $person = null;
                $sa_id = $sa_goal->service_agreement_id;
                $sa = DB::select("SELECT * FROM `tbl_service_agreement` WHERE id=$sa_id");
                if (!empty($sa)) {
                    $sa = $sa[0];
                    if (!empty($sa)) {
                        $opp = null;
                        $cur_date = date('Y-m-d H:i:s');
                        $service_type = null;
                        $opp = DB::select("SELECT * FROM `tbl_opportunity` WHERE `id` = $sa->opportunity_id");
                        if (!empty($opp)) {
                            $opp = $opp[0];
                            $service_type = $opp->topic;
                        }

                        $participant_id = $sa->participant_id?? null;
                        
                        if (!empty($sa_goal->goal)) {
                            $sql = "INSERT INTO `tbl_goals_master` (`id`, `participant_master_id`, `service_agreement_id`, `goal`, `objective`, `start_date`, `end_date`, `archive`, `created_by`, `updated_by`, `created`, `updated`, `service_type`) VALUES (NULL, '$participant_id', '$sa->id',  '$sa_goal->goal', '$sa_goal->outcome', '$sa->contract_start_date', '$sa->contract_end_date', '0', '1', '1', '$cur_date', '$cur_date', '$service_type')";
                            $result = DB::statement($sql);
                            if ($result) {
                                $upsql = "UPDATE `tbl_service_agreement_goal` SET is_migrated=1 WHERE `id` = $sa_goal->id";
                                $up = DB::update($upsql);
                                if (!$up) {
                                    echo "sql failed \n";
                                } else {
                                    echo "migrated row=> $sa_goal->id \n";
                                }
                            } else {
                                echo "sql failed \n";
                                DB::rollBack();
                                break;
                            }
                        }
                    }
                }
            }
        }
        DB::commit();
    }
}
