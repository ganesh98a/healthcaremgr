<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Description of ApproveRequest
 *
 * @author corner stone solutions
 */
class MemberApprovalRequest extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('notification_helper');
        $this->load->helper('approval_helper');
        $this->load->library('Notification');
        $this->load->library('Approval');
        $this->load->model('RequestMember_model');
        $this->notification->setUser_type(2);
    }

    function approve_member_profile() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $profileData = array();             
            $email_ary = array();             
            $phone_ary = array();

            if (!empty($reqData->approval_data)) 
            {
                foreach ($reqData->approval_data as $key => $val) 
                {                 
                    if ($val->approve && $val->key == 'firstname') 
                    {
                        $profileData['firstname'] =  $val->value;
                    }

                    if ($val->approve && $val->key == 'middlename') 
                    {
                        $profileData['middlename'] =  $val->value;
                    }

                    if ($val->approve && $val->key == 'lastname') 
                    {
                      $profileData['lastname'] =  $val->value;      
                  }

                  if ($val->approve && $val->key == 'phone') 
                  {
                    $my_data = json_decode($reqData->approval_content); 
                    $phone_ary = $my_data->phone_ary;
                }

                if ($val->approve && $val->key == 'email') 
                {
                    $my_data = json_decode($reqData->approval_content); 
                    $email_ary = $my_data->email_ary;                        
                }
            }
        }

        if(!empty($email_ary))
        {              
            /* First delete old record and then insert new record */
            $this->basic_model->update_records('member_email', array('archive' => '1', 'updated' => DATE_TIME), array('memberId' => $reqData->userId));

            $member_mail = array();
            foreach ($email_ary as $email) {
                $member_mail = array('memberId' => $reqData->userId ,
                    'email' => $email->email,
                    'archive' => '0',
                    'primary_email' => isset($email->isprimary) && $email->isprimary == 1 ? 1 : 2,
                    'created' => DATE_TIME, 
                    'updated' => DATE_TIME
                );

                if (isset($email->id) && $email->id > 0) {
                    $this->basic_model->update_records('member_email', $member_mail, array('id' => $email->id));
                } else {
                        //$member_mail['primary_email'] = 2;
                    $this->basic_model->insert_records('member_email', $member_mail);
                }
            }
        }

            #pr($phone_ary);
        if(!empty($phone_ary))
        {
            $main_ph_ary = array();
            foreach ($phone_ary as $key => $xx)
            {
                $temp_ph_ary['memberId'] = $reqData->userId;
                $temp_ph_ary['phone'] = $xx->phone;
                $temp_ph_ary['primary_phone'] = isset($reqData->isprimary) && $reqData->isprimary == 1?1:2;
                $main_ph_ary[] = $temp_ph_ary;
            }
            if(!empty($main_ph_ary))
            {
                $this->basic_model->insert_records('member_phone', $main_ph_ary, true);     
            }

            /* First delete old record and then insert new record */
            $this->basic_model->update_records('member_phone', array('archive' => '1'), array('memberId' => $reqData->userId));
            $member_ph = array();
            foreach ($phone_ary as $ph) {
                $member_ph = array('memberId' => $reqData->userId,
                    'phone' => $ph->phone,
                    'archive' => '0',
                    'primary_phone' => isset($ph->isprimary) && $ph->isprimary == 1 ? 1 : 2
                );

                if (isset($ph->id) && $ph->id > 0) {
                    $this->basic_model->update_records('member_phone', $member_ph, array('id' => $ph->id));
                } else {
                    //$member_ph['primary_phone'] = 2;
                    $this->basic_model->insert_records('member_phone', $member_ph);
                }
            }
        }
            #die;
        $this->approval->setId($reqData->id);
        $this->notification->setUserId($reqData->userId);
        $this->notification->setTitle(notification_msgs('update_your_detail', 'title'));

        if (!empty($profileData)) {
            $where = array('id' => $reqData->userId);
            $this->basic_model->update_records('member', $profileData, $where);
            $this->notification->setShortdescription(notification_msgs('update_your_detail', 'approve'));
                // approve request
            $this->approval->approveRequest();
        } else {
            $this->notification->setShortdescription(notification_msgs('update_your_detail', 'deny'));
                // deny request
            $this->approval->denyRequest();
        }
        $this->notification->createNotification();
    }
    echo json_encode(array('status' => true));
}

function member_approve_place() {
    $reqData = request_handler('update_admin', 1, 1);
    $this->approval->setApproved_by($reqData->adminId);
    if (!empty($reqData->data)) {
        $reqData = $reqData->data;
        $approve_status = check_approval_request($reqData);
        if (!empty($approve_status)) {
            $this->RequestMember_model->update_places($reqData);
        }
        echo json_encode(array('status' => true));
    }
}

function member_approve_activity(){
    $reqData = request_handler('update_admin', 1, 1);
    $this->approval->setApproved_by($reqData->adminId);
    if (!empty($reqData->data)) {
        $reqData = $reqData->data;
        $approve_status = check_approval_request($reqData, 'update_preferred_activities');
        if (!empty($approve_status)) {
            $this->RequestMember_model->update_activity($reqData);
        }
        echo json_encode(array('status' => true));
    }
}
}
