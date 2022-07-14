<?php

namespace ClassParticipantAprroval;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParticipantAprroval
 *
 * @author Corner stone solutions
 */
require_once APPPATH . 'Classes/approval/ParticipantHelping.php';

class ParticipantAprroval {

    public $CI;
    public $helpObj;

    function __construct() {
        $this->CI = & get_instance();

        $this->CI->load->model('aproval/Approval_model');
        $this->helpObj = new \ClassParticipantHelping\ParticipantHelping();
    }

    function participant_profile($userData) {
        $colown = array('firstname', 'middlename', 'lastname', 'dob', 'gender', 'prefer_contact', 'preferredname', 'crn_num', 'ndis_num', 'medicare_num');
        $where = array('id' => $userData['userId']);

        $result = $this->CI->basic_model->get_row('participant', $colown, $where);

        $approval_data = (object) json_decode($userData['approval_content']);

        $element_data = array();

        // for dynamic fields heading
        $mapping_fields = array('firstname' => 'First Name', 'middlename' => 'Middle Name', 'lastname' => 'Last Name', 'dob' => 'Date of Birth', 'gender' => 'Gender', 'prefer_contact' => 'Prefer Contact', 'preferredname' => 'Preferred Name', 'crn_num' => 'Crn Number', 'ndis_num' => 'Ndis Number', 'medicare_num' => 'Medicare Num');

        $i = 0;
        foreach ($approval_data as $key => $val) {
            if (empty($result->{$key}) && empty($approval_data->{$key})) {
                continue;
            }
            // if both value is same then continue
            if ($result->{$key} == $approval_data->{$key}) {
                continue;
            }

            $element_data[$i]['description'] = approval_mapping($userData['approval_area'], 'description');
            $element_data[$i]['area'] = "Your details";
            $element_data[$i]['field'] = $mapping_fields[$key];
            $element_data[$i]['previous'] = $result->{$key};
            $element_data[$i]['new'] = $approval_data->{$key};
            $element_data[$i]['key'] = $key;
            $element_data[$i]['value'] = $approval_data->{$key};
            $element_data[$i]['approve'] = false;
            $element_data[$i]['subcomponet'] = 'CommonDisplay';

            // set different subcomponent
            if ($key == 'prefer_contact') {
                $element_data[$i]['subcomponet'] = 'PreferContact';
            }

            // change view value
            if ($key == 'dob') {
                $element_data[$i]['previous'] = DateFormate($result->{$key}, 'd-m-Y');
                $element_data[$i]['new'] = DateFormate($approval_data->{$key}, 'd-m-Y');
            } elseif ($key == 'gender') {
                $element_data[$i]['previous'] = ($result->{$key} == 1) ? 'Male' : "Female";
                $element_data[$i]['new'] = ($approval_data->{$key} == 1) ? 'Male' : "Female";
            }

            $i++;
        }

        return $element_data;
    }

    function participant_place($userData) {
        $placeLabel = $this->CI->Approval_model->get_place();
        $participantOldPlace = $this->CI->Approval_model->get_participant_place($userData['userId']);

        $participantNewPlace = (object) json_decode($userData['approval_content']);

        $element_data = array();


        $placeOld = $this->helpObj->make_place_data($participantOldPlace, $placeLabel);
        $placeNew = $this->helpObj->make_place_data($participantNewPlace, $placeLabel);
        
        if ($placeOld == $placeNew) {
            return [];
        }

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

    function participant_activity($userData) {
        $placeLabel = $this->CI->Approval_model->get_activity();
        $participantOldActiviy = $this->CI->Approval_model->get_participant_activiy($userData['userId']);

        $participantNewActiviy = (object) json_decode($userData['approval_content']);

        $element_data = array();


        $placeOld = $this->helpObj->make_activiy_data($participantOldActiviy, $placeLabel);
        $placeNew = $this->helpObj->make_activiy_data($participantNewActiviy, $placeLabel);

        if ($placeOld == $placeNew) {
            return [];
        }

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

    function participant_care_requirement($userData) {
        $Label = $this->CI->Approval_model->get_genral_requirement();

        $where = array('participantId' => $userData['userId']);
        $colown = array('preferred_language', 'linguistic_interpreter', 'hearing_interpreter');
        $about_care_old = $this->CI->basic_model->get_row('participant_care_requirement', $colown, $where);

        $language_list = $this->CI->basic_model->get_record_where('language', array('id', 'name'), array('archive' => 0));
        $language_list = array_column($language_list, 'name', 'id');

        $old_data = array();

        $old_data['AssistanceUpdate'] = $this->CI->Approval_model->get_participant_asistance($userData['userId'], 'assistance');
        $old_data['SupportRequiredUpdate'] = $this->CI->Approval_model->get_participant_support_required($userData['userId']);
        $old_data['OCServicesUpdate'] = $this->CI->Approval_model->get_participant_oc_service($userData['userId']);
//        $old_data['MobilityUpdate'] = $this->CI->Approval_model->get_participant_asistance($userData['userId'], 'mobality');

        $new_data = (array) json_decode($userData['approval_content']);

        $element_data = array();
        $x = $this->helpObj->asistance($old_data['AssistanceUpdate'], $new_data['AssistanceUpdate'], $Label);
        if ($x)
            $element_data[] = $x;

        $x = $this->helpObj->support_required($old_data['SupportRequiredUpdate'], $new_data['SupportRequiredUpdate'], $Label);
        if ($x)
            $element_data[] = $x;

        $x = $this->helpObj->oc_service($old_data['OCServicesUpdate'], $new_data['OCServicesUpdate'], $Label);
        if ($x)
            $element_data[] = $x;


//        $x = $this->helpObj->mobility($old_data['MobilityUpdate'], $new_data['MobilityUpdate'], $Label);
//        if ($x)
//            $element_data[] = $x;

        $x = $this->helpObj->preferred_language($language_list, $about_care_old->preferred_language, $new_data['ParticipantCare']->preferred_language);
        if ($x)
            $element_data[] = $x;

        $x = $this->helpObj->linguistic_interpreter($about_care_old->linguistic_interpreter, $new_data['ParticipantCare']->linguistic_interpreter);
        if ($x)
            $element_data[] = $x;

        $x = $this->helpObj->hearing_interpreter($about_care_old->hearing_interpreter, $new_data['ParticipantCare']->hearing_interpreter);
        if ($x)
            $element_data[] = $x;

        return $element_data;
    }

    function participant_email($userData) {
        $where = array('participantId' => $userData['userId']);
        $colown = array('email', 'primary_email');
        $old_data = $this->CI->basic_model->get_record_where('participant_email', $colown, $where);

        $new_data = (object) json_decode($userData['approval_content']);

        $element_data = array();

        $old_data = $this->helpObj->make_email_data($old_data);
        $new_data = $this->helpObj->make_email_data($new_data);


        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Profile Details";
        $element_data[$i]['field'] = 'Email';
        $element_data[$i]['previous'] = $old_data;
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'CommonDisplay';

        return $element_data;
    }

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

        $new_data = json_decode($userData['approval_content']);

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
    }

    function participant_kin_update($userData) {
        $where = array('pk.participantId' => $userData['userId'], 'pk.archive' => 0);
        $colown = array('pk.firstname', 'pk.lastname', 'pk.relation as relation_id', 'pk.phone', 'pk.email', 'pk.primary_kin', 'r.name as relation');

        $this->CI->db->select($colown);
        $this->CI->db->from("tbl_participant_kin as pk");
        $this->CI->db->join("tbl_relations as r", 'r.id = pk.relation', 'inner');
        $this->CI->db->where($where);
        $query = $this->CI->db->get();
        $old_data = $query->result();

        $relations_list = $this->CI->basic_model->get_record_where('relations', array('id', 'name'), array('archive' => 0));
        $relations_list = array_column($relations_list, 'name', 'id');

        $new_data = json_decode($userData['approval_content']);
        foreach ($new_data as $key => $value) {
            $new_data[$key]->relation = $relations_list[$value->relation];
        }
        $element_data = array();

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Profile Details";
        $element_data[$i]['field'] = 'Kin Details';
        $element_data[$i]['previous'] = $old_data;
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'ParticipantKinUpdate';
        return $element_data;
    }

    function participant_booker_update($userData) {
        $where = array('participantId' => $userData['userId'], 'archive' => '0');
        $colown = array('firstname', 'lastname', 'relation as relation_id', 'phone', 'email');

        $this->CI->db->select($colown);
        $this->CI->db->select('(select name from tbl_relations where tbl_relations.id=tbl_participant_booking_list.relation) as relation', null, false);
        $this->CI->db->from(TBL_PREFIX . 'participant_booking_list');
        $this->CI->db->where($where);
        $query = $this->CI->db->get();
        $old_data = $query->result();

        $relations_list = $this->CI->basic_model->get_record_where('relations', array('id', 'name'), array('archive' => 0));
        $relations_list = array_column($relations_list, 'name', 'id');

        $new_data = json_decode($userData['approval_content']);
        foreach ($new_data as $key => $value) {
            $new_data[$key]->relation = $relations_list[$value->relation];
        }

        $element_data = array();

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Profile Details";
        $element_data[$i]['field'] = 'Booker Details';
        $element_data[$i]['previous'] = $old_data;
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'ParticipantBookerUpdate';

        return $element_data;
    }

    function participant_add_goal($userData) {
        $new_data = (object) json_decode($userData['approval_content']);

        $element_data = array();

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Goal";
        $element_data[$i]['field'] = 'Add New Gaol';
        $element_data[$i]['previous'] = '';
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'AddNewGaolParticipant';


        return $element_data;
    }

    function participant_update_goal($userData) {
        $new_data = json_decode($userData['approval_content']);

        $where = array('participantId' => $userData['userId'], 'id' => $new_data->id);
        $colown = array('title', 'start_date', 'end_date');
        $old_data = $this->CI->basic_model->get_row('participant_goal', $colown, $where);

        $element_data = array();

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Profile Details";
        $element_data[$i]['field'] = 'Update Goal';
        $element_data[$i]['previous'] = $old_data;
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'ParticipantUpdateGoal';


        return $element_data;
    }

    function participant_archive_goal($userData) {
        $new_data = json_decode($userData['approval_content']);

        $where = array('participantId' => $userData['userId'], 'id' => $new_data->participant_goalid);
        $colown = array('title', 'start_date', 'end_date');
        $old_data = $this->CI->basic_model->get_row('participant_goal', $colown, $where);

        $element_data = array();

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Profile Details";
        $element_data[$i]['field'] = 'Archive Goal';
        $element_data[$i]['previous'] = $old_data;
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'ParticipantArchiveGoal';


        return $element_data;
    }

    function participant_create_shift($userData) {
        $label = $this->CI->Approval_model->get_state();
        $new_data = json_decode($userData['approval_content']);


        $shift_requirement = [];
        if (!empty($new_data->requirement)) {
            foreach ($new_data->requirement as $val) {
                if (!empty($val->checked) && $val->checked == 1) {
                    $shift_requirement[] = $val->label;
                }
            }
            $new_data->requirement = implode(', ', $shift_requirement);
        }

        $preffer_member = [];
        if (!empty($new_data->preferred_member_ary)) {
            foreach ($new_data->preferred_member_ary as $val) {
                if (!empty($val->name)) {
                    $preffer_member[] = $val->name->label;
                }
            }
            $new_data->preferred_member = implode(', ', $preffer_member);
        }

        $element_data = array();

        $i = 0;
        $element_data[$i]['description'] = "Profile Update";
        $element_data[$i]['area'] = "Book Shift";
        $element_data[$i]['field'] = 'Create Shift';
        $element_data[$i]['previous'] = '';
        $element_data[$i]['new'] = $new_data;
        $element_data[$i]['approve'] = false;
        $element_data[$i]['subcomponet'] = 'CreateParticipantShift';
        $element_data[$i]['stateLabel'] = $label;


        return $element_data;
    }

}
