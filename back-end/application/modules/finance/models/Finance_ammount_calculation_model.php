<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_ammount_calculation_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function release_archive_shift_fund($shiftIds) {
        $this->db->select(["shiftId", "id"]);
        $this->db->from("tbl_shift_line_item_attached as attached_item");
        $this->db->where_in("attached_item.shiftId", $shiftIds);
    $this->db->where("attached_item.archive", 0);

        $result = $this->db->get()->result_array();

        if (!empty($result)) {
            $attach_line_itemIds = array_column($result, 'id');
            $this->update_line_item_hisory_as_release_status($attach_line_itemIds);
        }

        return $result;
    }

    public function update_line_item_hisory_as_release_status($attach_line_itemIds) {
        $update_data = ['status' => 2];

        $this->db->where_in('line_item_use_id', $attach_line_itemIds);
        $this->db->where('line_item_fund_used_type', 1);

        $this->db->update("tbl_user_plan_line_item_history", $update_data);
        return $this->db->affected_rows();
    }

    public function block_to_used_status_update_shift_fund_by_shift_id($shiftId) {
        $this->db->select(["shiftId", "id"]);
        $this->db->from("tbl_shift_line_item_attached as attached_item");
        $this->db->where("attached_item.shiftId", $shiftId);
        $this->db->where("attached_item.archive", 0);

        $result = $this->db->get()->result_array();

        if (!empty($result)) {
            $attach_line_itemIds = array_column($result, 'id');
            $this->update_shift_line_item_hisory_as_block_to_used_status($attach_line_itemIds);
        }

        return $result;
    }

    public function update_shift_line_item_hisory_as_block_to_used_status($attach_line_itemIds) {
        $update_data = ['status' => 1,'used_action_date'=>DATE_TIME];

        $this->db->where_in('line_item_use_id', $attach_line_itemIds);
        $this->db->where('line_item_fund_used_type', 1);
        $this->db->where('status', 0);
        $this->db->where('archive', 0);

        $this->db->update("tbl_user_plan_line_item_history", $update_data);
        return $this->db->affected_rows();
    }

    public function get_user_other_type_current_plan_line_item_id_by_user_id($userId=0,$extratParm=[]){
        $userType =$extratParm['user_type'] ?? 0;
        $userType = !empty($userType) ? $userType :0;
        $userType = is_array($userType) ? $userType:[$userType];
        $this->db->select(["upli.id", "upli.total_funding"]);
        $this->db->from('tbl_user_plan up');
        $this->db->join("tbl_user_plan_line_items upli","upli.user_planId =up.id AND upli.archive=up.archive");
        $this->db->join("tbl_finance_line_item fli","upli.line_itemId=fli.id");
        $this->db->join("tbl_finance_support_category fsc","fli.support_category=fsc.id and fsc.key_name='other_type'");
        $this->db->where_in("up.user_type",$userType);
        $this->db->where("up.archive",0);
        $this->db->where("up.userId",$userId);
        $this->db->where("'".DATE_CURRENT."' between up.start_date and up.end_date",NULL,FALSE);
        $query = $this->db->get();
        return $query->num_rows()>0 ? $query->row_array() :[];
    }

    public function get_user_other_type_current_plan_line_item_id_by_user_id_for_crm($userId=0,$extratParm=[]){
        $userType =$extratParm['user_type'] ?? 0;
        $userType = !empty($userType) ? $userType :0;
        $userType = is_array($userType) ? $userType:[$userType];
        $this->db->select(["upli.id", "upli.total_funding"]);
        $this->db->from('tbl_user_plan up');
        $this->db->join("tbl_user_plan_line_items upli","upli.user_planId =up.id AND upli.archive=up.archive");
        $this->db->join("tbl_finance_line_item fli","upli.line_itemId=fli.id");
        $this->db->join("tbl_finance_support_category fsc","fli.support_category=fsc.id");
        $this->db->where_in("up.user_type",$userType);
        $this->db->where("up.archive",0);
        $this->db->where("up.userId",$userId);
        $this->db->where("'".DATE_CURRENT."' between up.start_date and up.end_date",NULL,FALSE);
        $this->db->order_by("upli.id", "desc");
        $query = $this->db->get();
        return $query->num_rows()>0 ? $query->row_array() :[];
    }

}
