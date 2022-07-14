<?php

use function PHPSTORM_META\type;

defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * controller name: UpdateEmailScript
 */

//class Master extends MX_Controller
class UpdateEmailScript extends MX_Controller {

    function __construct() {

        parent::__construct();
        $this->load->helper('i_pad');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }  

    /**
     * removing any access level locks taken by the admin user for optional object and object id
     */
    public function updateEmailToTestMailId() {
        $reqData = $this->input->get();
        if(!empty($reqData) && $reqData['oa']=='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.WyIyMDIxLTAyLTAzIDIyOjAyOjE2MTIiXQ.3xUl_LrTFPGamJ1brj-FiCmyUIv6SChKHMrwo1Ufob4'){
            if(getenv('OCS_ADMIN_URL')=='https://adminapi.clone.healthcaremgr.net/'){
                $this->db->query("UPDATE tbl_member SET username=REPLACE(username, SUBSTRING(username,INSTR(username,'@')+1),'yopmail.com')");
                $this->db->query("UPDATE tbl_member_email SET email=REPLACE(email, SUBSTRING(email,INSTR(email,'@')+1),'yopmail.com')");
                $this->db->query("UPDATE tbl_leads SET email=REPLACE(email, SUBSTRING(email,INSTR(email,'@')+1),'yopmail.com')");
                $this->db->query("UPDATE tbl_organisation_accounts_payable_email SET email=REPLACE(email, SUBSTRING(email,INSTR(email,'@')+1), 'yopmail.com')");
                $this->db->query("UPDATE tbl_recruitment_applicant_email SET email=REPLACE(email, SUBSTRING(email,INSTR(email,'@')+1),'yopmail.com');");
                $this->db->query("UPDATE tbl_person_email SET email=REPLACE(email, SUBSTRING(email,INSTR(email,'@')+1), 'yopmail.com')");
                $this->db->query("UPDATE tbl_users SET username=REPLACE(username, SUBSTRING(username,INSTR(username,'@')+1), 'yopmail.com')");
    
                $result = ['status' => true, 'msg' => "Emails updated with Yopmail Account"];
    
            }else{
                $result = ['status' => false, 'error' => "Invalid Domain"];
            }   
        }else{
            $result = ['status' => false, 'error' => "Authentication failed"];
        }   
        
        echo json_encode($result);
        exit();
    }

   }
