<?php

defined('BASEPATH') or exit('No direct script access allowed');

class All_module_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        // added dummy line 1
        parent::__construct();
    }

    //  get all module list
    public function get_all_module_who_enable_disable($reqData) {
        $filtered = $reqData->filtered?? null;
        $search = '';
        if (!empty($filtered)) {
            $search = $filtered->search;
        }
        $column = ["id","title", "status"];
        $where = ["its_enable_disable_module" => 1, "archive" => 0];

        $this->db->select($column);
        $this->db->from('tbl_module_title as mt');
        $this->db->where($where);
        if (!empty($search)) {
            $this->db->like('title', $search);
        }
        $query = $this->db->get();
       
        return $query->result();
    }

    // save updated status modules
    public function save_enable_disable_module_status($moduleList){
        if(!empty($moduleList)){
            foreach($moduleList as $val){
                $update_list[] = ["id" => $val->id, "status" => $val->status];
            }

            // update betch operation
            if(!empty($update_list)){
                $this->basic_model->insert_update_batch('update', 'module_title', $update_list, "id");
            }

            return true;
        }
    }

}
