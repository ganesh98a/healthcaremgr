<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_template_model extends Basic_Model  {

    function __construct() {
        parent::__construct();
        $this->load->model('common/Common_model');
    }

    
    
    public function get_sms_created_by_sub_query($tbl = "p") {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = " . $tbl . ".created_by", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

   
     /*
     * its used for create sms_template from user input
     * return type json
     *
     */
    function create_sms_template($data,$adminId) {
        if(!empty($this->check_template_name_already_exist($data->{'name'})))
        {
            return ["status" => false, "error" => "Template name already exists"];
         }

        $sms_details = $this->basic_model->get_row('sms_template', array("MAX(show_title_order) AS title_order_id"));
        $title_order_id = 0;
        if($sms_details->title_order_id){
            $title_order_id = $sms_details->title_order_id+1;
        }
        
        $postdata  = array(
         'name' => $data->{'name'},
         'short_description' => $data->{'short_description'}, 
         'content' => $data->{'sms_content'},
         'folder' => $data->{'folder'}?? 'public', 
         'show_title_order' => $title_order_id ?? 0, 
          );

      $postdata["created_at"] = DATE_TIME;
      $postdata["created_by"] =$adminId;
      $id = $this->basic_model->insert_records("sms_template", $postdata,FALSE);
      return ["status" => true, "msg" => "Template has been created successfully"];
    
    }


     /*
     * its used to check  the sms template is already present or not
     * @params
     * $template_name,$template_id of the template
     * return type json
     *
     */


    function check_template_name_already_exist($template_name,$template_id='') {
        $this->db->select("id");
        $this->db->from("tbl_sms_template as s");
        $this->db->where("name", trim($template_name));
        $this->db->where('s.archive', 0);
        if ($template_id > 0) {
            $this->db->where("id != ", $template_id);
        }
        return $this->db->get()->row();
    }


     /*
     * its used for get sms_template  as a list
     * @params
     * $reqData request data like special opration like filter,search, sort
     * return type json
     *
     */
    function get_sms_templates($reqData,$filter_condition='') {
        $createdByNameSubQuery = $this->get_sms_created_by_sub_query('s');
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';
        $this->db->select('*, short_description as description');
        $this->db->select("(" . $createdByNameSubQuery . ") as created_by");
       $this->db->from('tbl_sms_template as s');
       $this->db->where('s.archive', 0);
       if (!empty($filter_condition)) {
        $this->db->having($filter_condition);
        }
        $this->db->order_by('s.id', 'desc');
       $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
       $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
       if ($limit != 0) {
           if ($dt_filtered_total % $limit == 0) {
               $dt_filtered_total = ($dt_filtered_total / $limit);
           } else {
               $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
           }
       }

       $result = $query->result();
       return  array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
      
     
    }
      

    /*
     * its used for updating the sms template by it's id
     * @params
     * $reqData request data will include id of the template
     * and content need to be updated
     * return type json
     *
     */
    function update_sms_template($data,$adminId){
        
        if(!empty($this->check_template_name_already_exist($data->{'name'},$data->{'id'})))
        {
            return ["status" => false, "error" => "Template name already exists"];
         }
        $postdata  = array(
            'name' => $data->{'name'},
            'short_description' => $data->{'short_description'}, 
            'content' => $data->{'sms_content'},
            'folder' => $data->{'folder'}?? 'public', 
             );
       
           
         $postdata["updated_at"] = DATE_TIME;
         $postdata["updated_by"] =$adminId;
         $id = $this->basic_model->update_records("sms_template", 
         $postdata, $where = array(
            'id' =>  $data->{'id'}
        ));
         return ["status" => true, "msg" => "Template has been updated successfully"];
    }
   
    /*
     * its used for get sms_template by it's id
     * @params
     * $reqData request data will include id of the template
     * return type json
     *
     */
    function get_sms_template_by_id($data){

       $createdByNameSubQuery = $this->get_sms_created_by_sub_query('s');
       $this->db->select(['name','content','id','short_description']);
       $this->db->select("(" . $createdByNameSubQuery . ") as created_by");
       $this->db->from('tbl_sms_template as s');
       $this->db->where('s.archive', 0);
       $this->db->where('s.id',$data->{'id'});
       $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
       $result = $query->result();
       return array( 'data' => $result, 'status' => true);
    }


    
      /*
     * its used for deleting the sms template by it's id
     * @params
     * $reqData request data will include id of the template
     * return type json
     *
     */

    function delete_sms_template_by_id($data,$adminId){
        $where = ["id" => $data->{'id'}];
        $update = ["archive" => 1,"updated_at" => DATE_TIME,"updated_by"=>$adminId];
         $this->basic_model->update_records("sms_template", $update, $where);
         return ["status" => true, "msg" => "Archived successfully"];
        
    }

    /**
     * Get SMS template details by id
     * - Used in automatic_sms class file
     */
    function get_template_content_details_by_template_id($templateId) {

      $createdByNameSubQuery = $this->get_sms_created_by_sub_query('st');
      $this->db->select('*');
      $this->db->select("(" . $createdByNameSubQuery . ") as created_by");
      $this->db->from("tbl_sms_template as st");
      $this->db->where("st.id", $templateId);
      $res = $this->db->get()->row_array();
 
      return $res;
    }

    /*
     * get_all_sms_template used in activity section
     *
     */
    function get_active_sms_templates($reqData) {
        # select array
        $select_column = array('st.id as value', 'st.name as label', 'content');
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_sms_template as st');
        $this->db->where('st.archive', 0);
        $this->db->order_by('st.show_title_order', 'ASC');
        $query = $this->db->get();
        $result = $query->result();
        return  array('data' => $result, 'status' => true);
    }

    /**
     * get sms template by name
     */
    function get_sms_template_to_initiate_oa() {
        $this->db->select('*');
        $this->db->from('tbl_sms_template as s');
        $this->db->where(['s.archive' => 0, 'used_to_initiate_oa' => 1]);
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }
}
