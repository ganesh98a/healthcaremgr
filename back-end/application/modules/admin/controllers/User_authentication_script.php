<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class User_authentication_script extends MX_Controller {

    use formCustomValidation;

    function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Basic_model');
        $this->form_validation->CI = & $this;
        $this->load->helper('i_pad');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    public function add_username_password_for_member(){
        
        $reqData=(array) api_request_handler();
        $limit = $reqData['pageSize'] ?? 9999;
        $page = $reqData['page'] ?? 0;       
        $orderBy = 'm.id';
        $direction = 'ASC';

        // get entity type value
        $select_column = array('m.id','m.username','p.password','m.member_password_token', 'm.created_by','m.created','m.status');

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join('tbl_person p', 'm.person_id = p.id', 'inner');
        $this->db->join('tbl_person_email pe', 'pe.person_id = p.id', 'left');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"', 'inner');

        $this->db->group_by('m.id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where("m.archive", "0");

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());        
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        foreach ($result as  $value) {
            $check_user_exists = [];
                $check_user_exists = $this->basic_model->get_row('users', ['*'], ['username' => $value->username, 'user_type' => 2]);               
               
                $created_at = $value->created ? date('Y-m-d h:i:s', strtotime($value->created)) : NULL;
                    if(empty($check_user_exists)){
                        $create_member = array(
                            "username" => $value->username,
                            "password" => $value->password,
                            'user_type' => 2,
                            "created_at" => $created_at,
                            "created_by" => $value->created_by,
                            "password_token" => $value->member_password_token,
                        );                        
                        $created_user_id = $this->basic_model->insert_records('users', $create_member, false);
                        // // fetch uuid in member table
                        $this->basic_model->update_records('member', ['uuid' => $created_user_id], ["id" => $value->id]);                        
                    }
                
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
       
        return $this->output->set_output(json_encode(['updated_records' => $return])); 
       
    }

    public function add_username_password_for_applicant(){
        $reqData=(array) api_request_handler();
        $limit = $reqData['pageSize'] ?? 9999;
        $page = $reqData['page'] ?? 0;  
        $orderBy = 'ra.id';
        $direction = 'ASC';

        

        // get entity type value
        $select_column = array('ra.id','p.id as person_id','p.username','p.password','p.status','p.archive','ra.password_token','ra.created');

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->from(TBL_PREFIX . 'recruitment_applicant as ra');
        $this->db->join('tbl_person p', 'p.id = ra.person_id', 'inner');

        $this->db->group_by('ra.id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where(["ra.archive"=> "0", "p.archive"=> "0", "p.password !="=>'' ]);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        foreach ($result as  $value) {
            $check_user_exists = [];
                $check_user_exists = $this->basic_model->get_row('users', ['*'], ['username' => $value->username, 'user_type' => 2]); 
                    if(empty($check_user_exists)){
                        $created_at = $value->created ? date('Y-m-d h:i:s', strtotime($value->created)) : NULL;
                        $create_applicant = array(
                            "username" => $value->username,
                            "password" => NULL,
                            'user_type' => 2,
                            "created_at" => $created_at,
                            "password_token" => $value->password_token,
                            "status"=> $value->status,
                            "archive"=> $value->archive,
                        ); 

                        $created_user_id = $this->basic_model->insert_records('users', $create_applicant, false);
                        if(!empty($created_user_id)){
                            $this->basic_model->update_records('recruitment_applicant', ['uuid' => $created_user_id], ["id" => $value->id]);
                            $this->basic_model->update_records('person', ['uuid' => $created_user_id], ["id" => $value->person_id]);
                        }
                        
                    }else{
                        $this->basic_model->update_records('recruitment_applicant', ['uuid' => $check_user_exists->id], ["id" => $value->id]);
                    }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
       
        return $this->output->set_output(json_encode(['updated_records' => $return])); 
    }

    public function remove_foreignkey_created_updated_by_from_tables(){      
        
        $list_of_table = [
        "tbl_crm_risk_assessment",
        "tbl_sales_relation",
        "tbl_need_assessment",
        "tbl_crm_risk_assessment_risk_matrix",
        "tbl_sales_attachment",
        "tbl_sales_attachment_meta",
        "tbl_crm_risk_assessment_court_actions",
        "tbl_ra_behavioursupport_matrix",
        "tbl_ra_behavioursupport",
        "tbl_sa_payments",
        "tbl_member_role" ,
        "tbl_participants_master",
        "tbl_member_ref_data",
        "tbl_member",
        "tbl_goals_master",
        "tbl_document_role",
        "tbl_locations_master",
        "tbl_location_address",
        "tbl_member_skill",
        "tbl_member_unavailability", 
        "tbl_list_view_controls",
        "tbl_crm_participant_schedule_task",
        "tbl_shift",
        "tbl_member_role_mapping", 
        "tbl_shift_member", 
        "tbl_organisation_member", 
        "tbl_participant_member", 
        "tbl_document_category", 
        "tbl_document_type_related",
        "tbl_shift_break",
        "tbl_shift_skills",
        "tbl_admin_process_event", 
        "tbl_opportunity_history_comment", 
        "tbl_opportunity_history_feed" ,
        "tbl_lead_history_feed", 
        "tbl_lead_history_comment", 
        "tbl_service_agreement_history_feed", 
        "tbl_service_agreement_history_comment", 
        "tbl_module_object",
        "tbl_access_role" ,
        "tbl_access_role_object", 
        "tbl_finance_pay_rate" ,
        "tbl_finance_timesheet" ,
        "tbl_finance_timesheet_line_item" ,
        "tbl_recruitment_form_applicant" ,
        "tbl_admin_bulk_import" ,
        "tbl_application_history_comment", 
        "tbl_application_history_feed", 
        "tbl_access_lock" ,
        "tbl_recruitment_interview",
        "tbl_recruitment_interview_applicant", 
        "tbl_recruitment_interview_history_comment" ,
        "tbl_recruitment_interview_history_feed", 
        "tbl_finance_invoice" ,
        "tbl_finance_invoice_line_item", 
        "tbl_document_attachment_email" ,
        "tbl_finance_timesheet_query", 
        "tbl_admin_api_log" ,
        "tbl_finance_invoice_shift" ,
        "tbl_form_applicant_history_comment" ,
        "tbl_form_applicant_history_feed", 
        "tbl_shift_ndis_line_item" ,
        "tbl_opportunity_staff_saftey_checklist" ,
        "tbl_fms_feedback_history_comment",
        "tbl_fms_feedback_history_feed", 
        ];
        
        $result_created = [];
        $result_updated = [];
     
        foreach ($list_of_table as $val) {
            $alter_created_by_query = "ALTER TABLE ".$val." DROP CONSTRAINT  ".$val."_created_by_foreign;";
            $alter_updated_by_query = "ALTER TABLE ".$val." DROP CONSTRAINT  ".$val."_updated_by_foreign;";           

            $result_created[] = $this->db->query($alter_created_by_query);
            $result_updated[] = $this->db->query($alter_updated_by_query);

            $this->db->query('ALTER TABLE '.$val.' ADD FOREIGN KEY (created_by) REFERENCES tbl_users(id)');
            $this->db->query('ALTER TABLE '.$val.' ADD FOREIGN KEY (updated_by) REFERENCES tbl_users(id)');
        }
        
        return ['result_created'=>$result_created, 'result_updated'=>$result_updated];
        
    }


    public function remove_foreignkey_created_from_tables(){      
        
        $list_of_table = [
            "tbl_leads",
            "tbl_opportunity",
            "tbl_person",
            "tbl_sales_activity",
            "tbl_opportunity_history",
            "tbl_service_agreement_history",
            "tbl_lead_history",
            "tbl_opportunity_item_history",
            "tbl_application_history",
            "tbl_holidays",
            "tbl_recruitment_interview_history",
            "tbl_form_applicant_history",
            "tbl_fms_feedback_history",
            "tbl_shift_sms_log"
        ];
        
        $result_created = [];
        $result_updated = [];
        foreach ($list_of_table as $val) {
            $alter_created_by_query = "ALTER TABLE ".$val." DROP CONSTRAINT  ".$val."_created_by_foreign;";
            $result_created[] = $this->db->query($alter_created_by_query);
            $this->db->query('ALTER TABLE '.$val.' ADD FOREIGN KEY (created_by) REFERENCES tbl_users(id)');
        }
        
        return ['result_created'=>$result_created, 'result_updated'=>$result_updated];
     
    }

    public function remove_foreignkey_updated_from_tables(){      
        
        $list_of_table = [
            "tbl_organisation",
            // "tbl_recruitment_task",
            "tbl_roster",
        ];
        
        $result_updated = [];
        foreach ($list_of_table as $val) {
            $alter_updated_by_query = "ALTER TABLE ".$val." DROP CONSTRAINT  ".$val."_updated_by_foreign;";

            $result_updated[] = $this->db->query($alter_updated_by_query);
        }
        
        return ['result_updated'=>$result_updated];     
    }


    public function remove_foreignkey_by_tables(){     
        
        $reqData=(array) api_request_handler();
        
        $list_of_table = $reqData['list_of_table'];
        $drop_by = $reqData['drop_by'];
        
        $result_updated = [];
        if(!empty($list_of_table)){
            foreach ($list_of_table as $val) {
                $alter_updated_by_query = "ALTER TABLE ".$val." DROP CONSTRAINT  ".$val."_".$drop_by."_foreign;";

                $result_updated[] = $this->db->query($alter_updated_by_query);
                $this->db->query('ALTER TABLE '.$val.' ADD FOREIGN KEY ('.$drop_by.') REFERENCES tbl_users(id)');
            }
        }
       
        
        return ['result_updated'=>$result_updated];     
    }

 }