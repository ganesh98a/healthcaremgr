<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Participant_intake_cron extends MX_Controller
{
    use callBackGroundProcess;
    function __construct()
    {
        parent::__construct();
        $this->load->model('Participant_intake_cron_model');
    }

    public function get_participant_with_less_fund_details_cron()
    {
        $parms = [
            'cron_call' => '1',
            'return_type' => '2',
        ];
        $response = $this->call_background_process('participant_with_less_fund_details', ['interval_minute' => 5, 'method_call' => 'get_participant_with_less_fund_details', 'method_params' => $parms]);
        echo json_encode($response);
        exit;
    }

    public function get_participant_with_less_fund_details($cronId, $extraParms = [])
    {
        $this->Participant_intake_cron_model->get_participant_with_less_schedule();
        $this->Basic_model->update_records('cron_status', array('status' => '1'), ['id' => $cronId]);
        return ['status' => true, 'msg' => 'cron run successfully', 'parms' => $extraParms];
    }

    public function get_participant_with_less_period_of_plan_cron()
    {
        $parms = [
            'cron_call' => '1',
            'return_type' => '2'
        ];
        $response = $this->call_background_process('participant_with_less_period_of_plan', ['interval_minute' => 5, 'method_call' => 'get_participant_with_less_period_of_plan', 'method_params' => $parms]);
        echo json_encode($response);
        exit;
    }

    public function get_participant_with_less_period_of_plan($cronId, $extraParms = [])
    {
        $this->Participant_intake_cron_model->get_participant_with_less_plan_period();
        $this->Basic_model->update_records('cron_status', array('status' => '1'), ['id' => $cronId]);
        return ['status' => true, 'msg' => 'cron run successfully', 'parms' => $extraParms];
    }
}
