<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * class: Contact_model
 * use for query operation of List view controls
 */

//class Master extends MX_Controller
class List_view_controls_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

    /*
     * its use for to insert the list view controls based on related type
     * 
     * @params 
     * type array
     * 
     * return type array
     * return id 
     */
    function  create_update_list_view_controls ($reqData, $adminId) {

        if(!empty($reqData->filter_list_id)){
            $list_control_data = [
                "list_name" => $reqData->list_name ?? null,
                "user_view_by" => $reqData->user_view_by ?? 0,                
                "related_type" => $reqData->related_type,            
                "updated_at" => DATE_TIME,
                "archive" => 0,            
                "updated_by" => $adminId,
            ];


            $filterId = $this->basic_model->update_records('list_view_controls', $list_control_data, $where = array('id' => $reqData->filter_list_id , 'related_type' => $reqData->related_type));           
           
            $where = array('pinned_id' => $reqData->filter_list_id,'admin_id!=' => $adminId, 'related_type'=> $reqData->related_type,'archive'=>0);
            $column = array('id', 'pinned_id');
            // check if the data is pinned or not
            $check_pinned = $this->basic_model->get_record_where('list_view_controls_pinned', $column, $where);
            if($check_pinned){
                $list_control_data = [            
                    "pinned_id" => 0,
                ];
              $this->basic_model->update_records('list_view_controls_pinned', $list_control_data, $where);
            }

            return $filterId;

        }else{
            $list_control_data = [
                "list_name" => $reqData->list_name ?? null,
                "user_view_by" => $reqData->user_view_by ?? 0,
                "related_type" => $reqData->related_type,            
                "created_at" => DATE_TIME,
                "archive" => 0,            
                "created_by" => $adminId,
            ];

            return  $this->basic_model->insert_records("list_view_controls", $list_control_data, false);             
        }           
    }

    /*
     * its use for get list control view by related_type
     * return type array
     */

    function  get_list_view_controls_by_related_type ($reqData, $adminId) {
         $query = sprintf("SELECT lv.list_name as label,lv.id as value FROM tbl_list_view_controls as lv where lv.user_view_by = 2 and lv.created_by!=%d
                union 
                SELECT lvc.list_name as label,lvc.id as value FROM tbl_list_view_controls as lvc
                where lvc.created_by=%d and lvc.related_type='%s'", $adminId,$adminId, $this->db->escape_str($reqData->related_type));
        $query = $this->db->query($query);
        return $query->result();    
    }
    /*
     * its use for get list control view by id     
     * return type array
     */
    function  get_list_view_controls_by_id ($related_type,$filter_list_id, $adminId) {
        $this->db->select(["lv.list_name as label", "lv.id as value","lv.filter_data","lv.filter_logic","lv.filter_operand","lv.created_by","lv.user_view_by"]);
        $this->db->from("tbl_list_view_controls as lv");       
        $this->db->where(['lv.related_type' => $related_type, 'lv.archive'=> 0, 'lv.id' => $filter_list_id]);
        $query = $this->db->get();
        return $query->row();        
    }

    /*
     * its use for to update the list view controls 
     * based on related type for filter data changes     * 
     * @params 
     * type array
     * 
     * return type array
     * return id 
     */
    function  update_filter_by_id ($list_id,$filter_logic,$filter_operand_length,$filter_data, $adminId) {

        $list_control_data = [
            "filter_data" => json_encode($filter_data),
            "filter_logic" => $filter_logic,
            "filter_operand" => $filter_operand_length,    
            "updated_at" => DATE_TIME,
            "updated_by" => $adminId,
        ];

      return  $this->basic_model->update_records('list_view_controls', $list_control_data, ["id" => $list_id]);
    }

     /*
     * its use for archive filter
     * 
     * @params $filter_list_id
     * return type boolean
     * true/false
     */

    function archive_filter_list($filter_list_id) {
        $updated_data = ["archive" => 1];
        return $this->basic_model->update_records("list_view_controls", $updated_data, ["id" => $filter_list_id]);
    }

    /*
     * Update the Pin based on related type 
     * @params 
     * type array
     * 
     * return id 
     */
    function pin_unpin_filter ($reqData, $adminId) {
        $this->db->select(["p.id","p.admin_id", "p.pinned_id"]);
        $this->db->from('tbl_list_view_controls_pinned p');
        $this->db->where([ 'p.archive' => 0, 'p.admin_id' => $adminId, 'p.related_type' => $reqData->related_type]);
        $pinned_data = $this->db->get();   
        $pinned_data = $pinned_data->row();
        if(!empty($pinned_data)){
            $list_control_data = [            
                "pinned_id" => $reqData->pin_list_id,
            ];
            $pinnedId = $this->basic_model->update_records('list_view_controls_pinned', $list_control_data, ["id" => $pinned_data->id,"admin_id" => $adminId, "related_type" => $reqData->related_type]);
        }else{
            $list_control_data = [            
                "pinned_id" => $reqData->pin_list_id,
                "admin_id" => $adminId,
                "related_type" => $reqData->related_type,
            ];
            $pinnedId = $this->basic_model->insert_records('list_view_controls_pinned', $list_control_data, false); 
        }

       return $pinnedId;
    }

    /*
     * Update the default pin based on related type 
     * @params 
     * type array
     * 
     * return id 
     */
    function default_pin_filter ($reqData, $adminId) {
        if(!empty($reqData->related_type)){
            $list_control_data = [            
                "pinned_id" => 0,
            ];
        $pinnedId = $this->basic_model->update_records('list_view_controls_pinned', $list_control_data, ["related_type" => $reqData->related_type, "admin_id" => $adminId]);
        }      

       return $pinnedId;
    }    

    /*
     * its use for get list control view by default pinned
     * return type array
     */

    function  get_list_view_controls_by_default_pinned ($reqData, $adminId) {
        $this->db->select(["lv.id as value", "lv.list_name as label","lv.filter_data","lv.filter_logic","lv.filter_operand"]);
        $this->db->from('tbl_list_view_controls lv');
        $this->db->where([ 'lv.archive' => 0, 'lv.created_by' => $adminId, 'lv.related_type' => $reqData->related_type]);
        $query = $this->db->get();
        return $query->row();  
    }

     /*
     * check if the list has default pinned
     * return type array
     */

    function  check_list_has_default_pinned ($reqData, $adminId) {
        $this->db->select(["p.admin_id", "p.pinned_id"]);
        $this->db->from('tbl_list_view_controls_pinned p');
        $this->db->where([ 'p.archive' => 0, 'p.admin_id' => $adminId, 'p.related_type' => $reqData->related_type]);
        $query = $this->db->get();
        return $query->row();  
    }
    
}
