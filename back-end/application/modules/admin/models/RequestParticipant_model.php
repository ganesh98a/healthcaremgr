<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class RequestParticipant_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    function update_places($reqData) {
        $participantPlaceData = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();
        $this->basic_model->delete_records('participant_place', $where = array('participantId' => $participantId));

        if (!empty($participantPlaceData)) {
            $this->basic_model->insert_records('participant_place', $participantPlaceData, $multiple = true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_activity($reqData) {
        $participantActiviyData = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        $this->basic_model->delete_records('participant_activity', $where = array('participantId' => $participantId));

        if (!empty($participantActiviyData)) {
            $this->basic_model->insert_records('participant_activity', $participantActiviyData, $multiple = true);
        }

        return true;
    }

    function archive_goal($reqData) {
        $goal_data = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;


        $data = array('status' => 2);
        $where = array('participantId' => $participantId, 'id' => $goal_data->participant_goalid);
        $this->basic_model->update_records('participant_goal', $data, $where);

        return true;
    }

    function update_goal($reqData) {
        $goal_data = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        $data = array('title' => $goal_data->title, 'start_date' => $goal_data->start_date, 'end_date' => $goal_data->end_date);
        $where = array('participantId' => $participantId, 'id' => $goal_data->id);

        $this->basic_model->update_records('participant_goal', $data, $where);

        return true;
    }

    function add_goal($reqData) {
        $goal_data = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        $data = array('title' => $goal_data->title, 'start_date' => $goal_data->start_date, 'end_date' => $goal_data->end_date, 'participantId' => $participantId, 'status' => 1);


        $this->basic_model->insert_records('participant_goal', $data);

        return true;
    }

    function update_address($reqData) {
        $participant_address = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();
        
        $this->db->delete(TBL_PREFIX . 'participant_address', $where = array('participantId' => $participantId));

        if (!empty($participant_address)) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_address', $participant_address, true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_bookers($reqData) {
        $new_bookers = json_decode($reqData->approval_content, true);
        $participantId = $reqData->userId;

        // get previous bookers
        $where = array('participantId' => $participantId, 'archive' => '0');
        $colown = array('id', 'relation', 'phone', 'email', 'firstname', 'lastname');
        $previous_booker = $this->basic_model->get_record_where('participant_booking_list', $colown, $where);
       
//        print_r($previous_booker);
        $previous_booker = json_decode(json_encode($previous_booker), true);
        $previous_booker_ids = array_column($previous_booker, 'id');

        if (!empty($new_bookers)) {
            foreach ($new_bookers as $key => $val) {
                if (!empty($val['id'])) {
                    if (in_array($val['id'], $previous_booker_ids)) {

                        $key = array_search($val['id'], $previous_booker_ids);
                    
                        // those value on done action remove from array
                        unset($previous_booker[$key]);

                        $update_booker = $this->mapping_kin_array($val, $participantId);
                        $this->basic_model->update_records('participant_booking_list', $update_booker, array('id' => $val['id']));
                    }
                } else {

                    // collect new data
                    $inset_booker[] = $this->mapping_kin_array($val, $participantId);
                }
            }

            if (!empty($inset_booker)) {
                $this->basic_model->insert_records('participant_booking_list', $inset_booker, true);
            }
         
            
            // archive those member who remove
            if (!empty($previous_booker)) {
                foreach ($previous_booker as $val) {
                    $archive_booker = array('archive' => '1');
                    $this->basic_model->update_records('participant_booking_list', $archive_booker, array('id' => $val['id']));
                }
            }
        }


//        // start for rollback
//        $this->db->trans_begin();
//
//        $this->db->delete(TBL_PREFIX . 'participant_booking_list', $where = array('participantId' => $participantId));
//
//        if (!empty($participant_bookers)) {
//            $this->db->insert_batch(TBL_PREFIX . 'participant_booking_list', $participant_bookers, true);
//            $this->db->trans_commit();
//        } else {
//            // for previous data remove 
//            $this->db->trans_rollback();
//        }

        return true;
    }

    function mapping_kin_array($data, $participantId) {
        $update_kin = array(
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'relation' => $data['relation'],
            'participantId' => $participantId,
        );

        return $update_kin;
    }

    function update_phone($reqData) {
        $participant_phone = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();

        $this->db->delete(TBL_PREFIX . 'participant_phone', $where = array('participantId' => $participantId));

        if (!empty($participant_phone)) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_phone', $participant_phone, true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_email($reqData) {
        $participant_phone = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();

        $this->db->delete(TBL_PREFIX . 'participant_email', $where = array('participantId' => $participantId));

        if (!empty($participant_phone)) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_email', $participant_phone, true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_kin_details($reqData) {
        $this->load->model('participant/Participant_profile_model');
        $participant_kin = json_decode($reqData->approval_content);
        $participantId = $reqData->userId;


        // use participant model for update
        $this->Participant_profile_model->update_kin_details($participant_kin, $participantId);

        return true;
    }

    function update_asistance($reqData) {
        $participant_data = json_decode($reqData->approval_content, true);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();

        $this->db->delete(TBL_PREFIX . 'participant_assistance', $where = array('participantId' => $participantId, 'type' => 'assistance'));

        if (!empty($participant_data['AssistanceUpdate'])) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_assistance', $participant_data['AssistanceUpdate'], true);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_mobility($reqData) {
        $participant_data = json_decode($reqData->approval_content, true);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();

        $this->db->delete(TBL_PREFIX . 'participant_assistance', $where = array('participantId' => $participantId, 'type' => 'mobality'));

        if (!empty($participant_data['MobilityUpdate'])) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_assistance', $participant_data['MobilityUpdate']);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_support_required($reqData) {
        $participant_data = json_decode($reqData->approval_content, true);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();

        $this->db->delete(TBL_PREFIX . 'participant_support_required', $where = array('participantId' => $participantId));

        if (!empty($participant_data['SupportRequiredUpdate'])) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_support_required', $participant_data['SupportRequiredUpdate']);
            $this->db->trans_commit();
        } else {
            // for previous data remove 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_oc_service($reqData) {
        $participant_data = json_decode($reqData->approval_content, true);
        $participantId = $reqData->userId;

        // start for rollback
        $this->db->trans_begin();

        $this->db->delete(TBL_PREFIX . 'participant_oc_services', $where = array('participantId' => $participantId));

        if (!empty($participant_data['OCServicesUpdate'])) {
            $this->db->insert_batch(TBL_PREFIX . 'participant_oc_services', $participant_data['OCServicesUpdate'], true);
            $this->db->trans_commit();
        } else {
            // for previous data 
            $this->db->trans_rollback();
        }

        return true;
    }

    function update_care_requirement($reqData, $aboutCare) {
        $participantId = $reqData->userId;

        $where = array('participantId' => $participantId);
        $this->basic_model->update_records('participant_care_requirement', $aboutCare, $where);

        return true;
    }

    function create_participant_shift($reqData) {
        $s_data = json_decode($reqData->approval_content, true);
        $participantId = $reqData->userId;

        // shift 
        $shift_data = array(
            'booked_by' => 2,
            'shift_date' => DateFormate($s_data['start_time'], 'Y-m-d'),
            'start_time' => DateFormate($s_data['start_time'], 'Y-m-d H:i:s'),
            'end_time' => DateFormate($s_data['end_time'], 'Y-m-d H:i:s'),
            'status' => 1,
            'created_by' => 1, //shift is created from participant portal
        );

        $shiftId = $this->basic_model->insert_records('shift', $shift_data, $multiple = false);


        // shift location
        if (!empty($s_data['location'])) {
            foreach ($s_data['location'] as $val) {

                $address = $val['address'] . ' ' . $val['suburb']['label'] . ' ' . $val['state'] . ' ' . $val['postal'];
                $lat_long = getLatLong($address);

                $lat = $lat_long['lat'] ? $lat_long['lat'] : '';
                $lang = $lat_long['long'] ? $lat_long['long'] : '';

                $shift_location[] = array('shiftId' => $shiftId, 'address' => $val['address'], 'suburb' => $val['suburb']['label'], 'state' => $val['state'], 'postal' => $val['postal'], 'lat' => $lat, 'long' => $lang);
            }

            // insert address
            $this->CI->Listing_model->create_shift_location($shift_location);
        }

        // shift participant
        $shift_participant = array('shiftId' => $shiftId, 'participantId' => $participantId, 'status' => 1, 'created' => DATE_TIME);
        $this->basic_model->insert_records('shift_participant', $shift_participant, $multiple = false);


        // shift requirement
        if (!empty($s_data['requirement'])) {
            $shift_requirement = array();

            foreach ($s_data['requirement'] as $val) {
                if (!empty($val['checked'])) {
                    $shift_requirement[] = array('requirementId' => $val['value'], 'shiftId' => $shiftId);
                }
            }

            if (!empty($shift_requirement)) {
                $this->basic_model->insert_records('shift_requirements', $shift_requirement, $multiple = true);
            }
        }

        // preferred member
        if (!empty($s_data['preferred_member_ary'])) {
            $preferred_member = array();

            foreach ($s_data['preferred_member_ary'] as $val) {
                if (!empty($val['name'])) {
                    $preferred_member[] = array('memberId' => $val['name']['value'], 'shiftId' => $shiftId);
                }
            }

            if (!empty($preferred_member)) {
                $this->basic_model->insert_records('shift_preferred_member', $preferred_member, $multiple = true);
            }
        }

        return $shiftId;
    }

}
