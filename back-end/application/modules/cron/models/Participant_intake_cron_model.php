<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Participant_intake_cron_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // participant less plan method
    public function get_participant_with_less_schedule()
    {
        // getting the list of participant user whose type is equal to 2
        $this->db->select("upl.id, upl.userId, upl.funding_type, upl.start_date, upl.end_date, pe.email, CONCAT(prt.firstname, ' ', prt.lastname) AS fullName");
        $this->db->join(TBL_PREFIX . "participant_email pe", "(pe.participantId = upl.userId AND pe.primary_email = 1)", "INNER");
        $this->db->join(TBL_PREFIX . "participant prt", "prt.id = upl.userId", "INNER");
        $query = $this->db->get_where(TBL_PREFIX . 'user_plan upl', array(
            "upl.user_type" => 2,
            "upl.start_date <=" => DATE_CURRENT,
            "upl.end_date >=" => DATE_CURRENT,
            "upl.archive" => 0
        ));

        if ($query->num_rows() > 0) {
            // getting funding plan related info of particular participants
            require_once APPPATH . 'Classes/crm/CrmParticipant.php';
            $obParticipant = new CrmParticipantClass\CrmParticipant();
            $response = $query->result_object();
            foreach ($response as $key => $value) {
                $this->db->select("SUM(uplt.fund_remaining) AS fund_remaining, ft.name as plan_type");
                $this->db->join("tbl_funding_type as ft", "ft.id = $value->funding_type", "INNER");
                $sql = $this->db->get_where(TBL_PREFIX . 'user_plan_line_items uplt', array(
                    "uplt.user_planId" => $value->id,
                    "uplt.archive" => 0
                ));
                if ($sql->num_rows() > 0) {
                    $row = $sql->row();
                    $percentage = 10;
                    $total_funds = $row->fund_remaining;
                    $less_funds = ($percentage / 100) * $total_funds;
                    $response[$key]->fund_remaining = $less_funds;
                    if ($row->fund_remaining <= $less_funds) {
                        $obParticipant->setFirstname($value->fullName);
                        $sendMail[0]['email'] = $value->email;
                        $obParticipant->setParticipantEmail($sendMail);
                        $obParticipant->RenewPlanMailParticipant();
                    }
                }
            }
        }
        return $response;
    }

    // participant less plan time method
    public function get_participant_with_less_plan_period()
    {
        // getting the list of participant user whose type is equal to 2
        $this->db->select("upl.id, upl.userId, upl.funding_type, upl.start_date, upl.end_date, pe.email, DATEDIFF(upl.end_date, '" . DATE_CURRENT . "') AS remaining_days, CONCAT(prt.firstname, ' ', prt.lastname) AS fullName");
        $this->db->join(TBL_PREFIX . "participant_email pe", "(pe.participantId = upl.userId AND pe.primary_email = 1)", "INNER");
        $this->db->join(TBL_PREFIX . "participant prt", "prt.id = upl.userId", "INNER");
        $query = $this->db->get_where(TBL_PREFIX . 'user_plan upl', array(
            "upl.user_type" => 2,
            "upl.archive" => 0
        ));

        if ($query->num_rows() > 0) {
            // getting funding plan related info of particular participants
            require_once APPPATH . 'Classes/crm/CrmParticipant.php';
            $obParticipant = new CrmParticipantClass\CrmParticipant();
            $response = $query->result_object();
            foreach ($response as $value) {
                if ($value->remaining_days < 90) {
                    $obParticipant->setFirstname($value->fullName);
                    $sendMail[0]['email'] = $value->email;
                    $obParticipant->setParticipantEmail($sendMail);
                    $obParticipant->LessFundsPlanMailParticipant();
                }
            }
        }
        return $response;
    }
}
