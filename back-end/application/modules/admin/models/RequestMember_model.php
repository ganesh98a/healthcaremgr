<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class RequestMember_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    function update_places($reqData) {
        $memberPlaceData = json_decode($reqData->approval_content);
        $memberId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();
        $this->basic_model->delete_records('member_place', $where = array('memberId' => $memberId));

        if (!empty($memberPlaceData)) {
            $this->basic_model->insert_records('member_place', $memberPlaceData, $multiple = true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }
    
    function update_activity($reqData) {
        $memberActiviyData = json_decode($reqData->approval_content);
        $memberId = $reqData->userId;

        $this->basic_model->delete_records('member_activity', $where = array('memberId' => $memberId));

        if (!empty($memberActiviyData)) {
            $this->basic_model->insert_records('member_activity', $memberActiviyData, $multiple = true);
        }

        return true;
    }


    /*function update_address($reqData) {
        $participant_address = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();
        $this->db->delete(TBL_PREFIX . 'participant_address', $where = array('participantId' => $participantId));

        if (!empty($participant_address)) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_address', $participant_address, true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }*/

   /* function update_phone($reqData) {
        $participant_phone = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();

        $this->db->delete(TBL_PREFIX . 'participant_phone', $where = array('participantId' => $participantId));

        if (!empty($participant_phone)) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_phone', $participant_phone, true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }*/
}
