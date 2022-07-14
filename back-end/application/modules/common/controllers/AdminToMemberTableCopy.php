<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * controller name: AdminToMemberTableCopy
 */

//class Master extends MX_Controller
class AdminToMemberTableCopy extends MX_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model('Common_model');
    }

    public function index() {
        
    }

    function copy_admin_tot_member() {
        $ress = $this->basic_model->get_record_where('admin', $column = '*', $where = '');

        if (!empty($ress)) {
            foreach ($ress as $val) {
                $adminId = $val->id;
                $member = array(
                    "companyId" => "1",
                    "firstname" => $val->firstname,
                    "lastname" => $val->lastname,
                    "middlename" => "",
                    "preferredname" => "",
                    "pin" => $val->pin,
                    "profile_image" => "",
                    "user_type" => "0",
                    "deviceId" => "",
                    "status" => $val->status,
                    "prefer_contact" => "",
                    "dob" => "1995-10-03",
                    "gender" => $val->gender,
                    "push_notification_enable" => "1",
                    "enable_app_access" => "1",
                    "dwes_confirm" => "1",
                    "archive" => $val->archive,
                    "created" => $val->created,
                    "loginattempt" => "0",
                    "department" => "1",
                    "username" => $val->username,
                    "password" => $val->password,
                );


                $memberId = $this->basic_model->insert_records('member', $member, $multiple = FALSE);

                $phone = $this->basic_model->get_record_where('admin_phone', $column = '*', $where = ['adminId' => $adminId]);

                $user_phone = [];
                if (!empty($phone)) {
                    foreach ($phone as $val) {
                        $user_phone[] = array(
                            'memberId' => $memberId,
                            'phone' => $val->phone,
                            'primary_phone' => $val->primary_phone,
                            'archive' => $val->archive, 
                            'created' => DATE_TIME
                        );
                    }

                    $this->basic_model->insert_records('member_phone', $user_phone, $multiple = TRUE);
                }



                $email = $this->basic_model->get_record_where('admin_email', $column = '*', $where = ['adminId' => $adminId]);

                $user_email = [];
                if (!empty($email)) {
                    foreach ($email as $val) {
                        $user_email[] = array(
                            'memberId' => $memberId,
                            'email' => $val->email,
                            'primary_email' => $val->primary_email,
                            'archive' => $val->archive, 
                            'created' => DATE_TIME
                        );
                    }

                    $this->basic_model->insert_records('member_email', $user_email, $multiple = TRUE);
                }


                $admin_role = $this->basic_model->get_record_where('admin_role', $column = '*', $where = ['adminId' => $adminId]);

                $user_role = [];
                if (!empty($admin_role)) {
                    foreach ($admin_role as $val) {
                        $this->basic_model->delete_records('admin_role', ['id' => $val->id]);

                        $user_role[] = array(
                            'roleId' => $val->roleId,
                            'adminId' => $memberId,
                        );
                    }


                    $this->basic_model->insert_records('admin_role', $user_role, $multiple = TRUE);
                }
            }
        }
    }

}
