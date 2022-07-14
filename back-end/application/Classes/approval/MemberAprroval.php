<?php
namespace ClassMemberAprroval;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MemberAprroval
 *
 * @author Corner stone solutions
 */
#require_once APPPATH . 'Classes/approval/ParticipantHelping.php';

class MemberAprroval {

    public $CI;
    public $helpObj;

    function __construct() {
        $this->CI = & get_instance();

        #$this->CI->load->model('aproval/Approval_model');
        $this->helpObj = new \ClassParticipantHelping\ParticipantHelping();
    }

    function update_member_profile($userData) {

        $previous_data = array();

        $column = array('firstname', 'middlename', 'lastname');
        $where = array('id' => $userData['userId']);
        $result = $this->CI->basic_model->get_row('member', $column, $where);
                
        $column = array('phone', 'primary_phone as isprimary');
        $where = array('memberId' => $userData['userId'],'archive'=>0);
        $result_ph = $this->CI->basic_model->get_result('member_phone', $where,$column);

        $column = array('email', 'primary_email as isprimary');
        $where = array('memberId' => $userData['userId'],'archive'=>0);
        $result_mail = $this->CI->basic_model->get_result('member_email', $where,$column);

        /**/
        // for dynamic fields heading
        $approval_data = (object) json_decode($userData['approval_content']);
        $mapping_fields = array('firstname' => 'First Name', 'middlename' => 'Middle Name', 'lastname' => 'Last Name');
        $element_data = array();
        $i = 0;
        
        foreach ($approval_data->profile_ary as $key => $val) {
            if (empty($result->{$key}) && empty($approval_data->{$key})) {
                continue;
            }
           
            #if previous and new values are same then not display in front
            if ($result->{$key} == $approval_data->profile_ary->{$key}) {
                continue;
            }

            $element_data[$i]['description'] = approval_mapping($userData['approval_area'], 'description');
            $element_data[$i]['area'] = "Your details";
            $element_data[$i]['field'] = $mapping_fields[$key];
            $element_data[$i]['previous'] = $result->{$key};
            $element_data[$i]['new'] = $approval_data->profile_ary->{$key};
            $element_data[$i]['key'] = $key;
            $element_data[$i]['value'] = $approval_data->profile_ary->{$key};
            $element_data[$i]['approve'] = false;
            $element_data[$i]['subcomponet'] = 'CommonDisplay';
            $i++;
        }
        
        /**/ 
        #pr([$result_mail,]);
        $element_data[$i]['description'] = approval_mapping($userData['approval_area'], 'description');
        $element_data[$i]['area'] = "Your details";
        $element_data[$i]['field'] ='Email';
        $element_data[$i]['key'] ='email';
        $element_data[$i]['previous'] = $this->make_email_data($result_mail); 
        $element_data[$i]['new'] = $this->make_email_data($approval_data->email_ary);
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'CommonDisplay';
        $i++;
        #pr($element_data);
        $element_data[$i]['description'] = approval_mapping($userData['approval_area'], 'description');
        $element_data[$i]['area'] = "Your details";
        $element_data[$i]['field'] ='Phone';
        $element_data[$i]['key'] = 'phone';
        $element_data[$i]['previous'] = $this->make_phone_data($result_ph); 
        $element_data[$i]['new'] = $this->make_phone_data($approval_data->phone_ary);
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'CommonDisplay';
        $i++;
        #pr($element_data);
        return $element_data;
    }

    function make_phone_data($data)
    {        
        $temp = array();        
        foreach ($data as $val) {            
            if ($val->isprimary == 1) { 
             $temp[] = $val->phone . ' (Primary)';
            } 
            else {
               $temp[] = $val->phone;
            } 
        }
        return $return = implode(', ', $temp);    
    }

    function make_email_data($data)
    {        
        $temp = array();        
        foreach ($data as $val) {            
            if ($val->isprimary == 1) { 
             $temp[] = $val->email . ' (Primary)';
            } 
            else {
               $temp[] = $val->email;
            } 
        }
        return $return = implode(', ', $temp);    
    }

    
   function update_member_places($userData) {

        $placeLabel = $this->CI->Approval_model->get_place();
        $memberOldPlace = $this->CI->Approval_model->get_member_place($userData['userId']);
        $memberNewPlace = (object) json_decode($userData['approval_content']);
        $element_data = array();
        $placeOld = $this->helpObj->make_place_data($memberOldPlace, $placeLabel);
        $placeNew = $this->helpObj->make_place_data($memberNewPlace, $placeLabel);
        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Your Prefers Places";
        $element_data[$i]['field'] = 'Prefers Places';
        $element_data[$i]['previous'] = $placeOld;
        $element_data[$i]['new'] = $placeNew;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'PreferPlaces';
        return $element_data;
    }
     
    function update_member_activity($userData) {
        $placeLabel = $this->CI->Approval_model->get_activity();
        $participantOldActiviy = $this->CI->Approval_model->get_participant_activiy($userData['userId']);

        $participantNewActiviy = (object) json_decode($userData['approval_content']);

        $element_data = array();


        $placeOld = $this->helpObj->make_activiy_data($participantOldActiviy, $placeLabel);
        $placeNew = $this->helpObj->make_activiy_data($participantNewActiviy, $placeLabel);

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Your Prefers Activity";
        $element_data[$i]['field'] = 'Prefers Activity';
        $element_data[$i]['previous'] = $placeOld;
        $element_data[$i]['new'] = $placeNew;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'PreferPlaces';

        return $element_data;
    }

    /*
    function participant_phone($userData) {
        $where = array('participantId' => $userData['userId']);
        $colown = array('phone', 'primary_phone');
        $old_data = $this->CI->basic_model->get_record_where('participant_phone', $colown, $where);

        $new_data = (object) json_decode($userData['approval_content']);

        $element_data = array();

        $old_data = $this->helpObj->make_phone_data($old_data);
        $new_data = $this->helpObj->make_phone_data($new_data);


        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Profile Details";
        $element_data[$i]['field'] = 'Phone';
        $element_data[$i]['previous'] = $old_data;
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'CommonDisplay';

        return $element_data;
    }

    function participant_address($userData) {
        $label = $this->CI->Approval_model->get_state();

        $old_data = $this->CI->Approval_model->get_participant_address($userData['userId']);

        $new_data =  json_decode($userData['approval_content']);

        $element_data = array();

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Profile Details";
        $element_data[$i]['field'] = 'Address';
        $element_data[$i]['previous'] = $old_data;
        $element_data[$i]['new_address'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'AddressUpdate';
        $element_data[$i]['stateLabel'] = $label;

        return $element_data;
    }*/


}
