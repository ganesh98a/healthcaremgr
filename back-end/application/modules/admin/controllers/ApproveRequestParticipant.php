<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of ApproveRequest
 *
 * @author corner stone solutions
 */
class ApproveRequestParticipant extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('notification_helper');
        $this->load->helper('approval_helper');
        $this->load->library('Notification');
        $this->load->library('Approval');

        $this->load->model('RequestParticipant_model');

        $this->notification->setUser_type(2);
    }

    function approve_profile_details() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $profileData = array();
            if (!empty($reqData->approval_data)) {
                foreach ($reqData->approval_data as $val) {
                    if (!empty($val->approve)) {
                        $profileData[$val->key] = $val->value;
                    }
                }
            }

            $this->approval->setId($reqData->id);

            $this->notification->setUserId($reqData->userId);
            $this->notification->setTitle(notification_msgs('update_your_detail', 'title'));

            if (!empty($profileData)) {

                $where = array('id' => $reqData->userId);
                $this->basic_model->update_records('participant', $profileData, $where);
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

    function approve_place() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_preferred_places');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_places($reqData);
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_activity() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_preferred_activities');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_activity($reqData);
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_archive_goal() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'archive_participant_goal');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->archive_goal($reqData);
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_update_goal() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_participant_goal');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_goal($reqData);
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_add_goal() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'add_participant_goal');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->add_goal($reqData);
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_address() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_participant_address');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_address($reqData);
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_booker_update() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_participant_booker');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_bookers($reqData);
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_phone() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_participant_phone');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_phone($reqData);
            } else {
                $this->db->trans_rollback();
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_email() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_participant_email');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_email($reqData);
            } else {
                $this->db->trans_rollback();
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_kin_update() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'update_participant_kin_details');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->update_kin_details($reqData);
            } else {
                $this->db->trans_rollback();
            }

            echo json_encode(array('status' => true));
        }
    }

    function approve_care_requirement() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);
        $approve_status = false;

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $about_care = array();
            if (!empty($reqData->approval_data)) {
                foreach ($reqData->approval_data as $val) {
                    if (!empty($val->approve)) {
                        $approve_status = true;
                        if ($val->key == 'asistance') {

                            $this->RequestParticipant_model->update_asistance($reqData);
                        } elseif ($val->key == 'mobility') {

                            $this->RequestParticipant_model->update_mobility($reqData);
                        } elseif ($val->key == 'support_required') {

                            $this->RequestParticipant_model->update_support_required($reqData);
                        } elseif ($val->key == 'oc_service') {

                            $this->RequestParticipant_model->update_oc_service($reqData);
                        } elseif ($val->key == 'preferred_language' || $val->key == 'linguistic_interpreter' || $val->key == 'hearing_interpreter') {

                            $about_care[$val->key] = $val->new;
                        }
                    }
                }
            }

            if (!empty($about_care)) {
                $this->RequestParticipant_model->update_care_requirement($reqData, $about_care);
            }

            $this->approval->setId($reqData->id);

            $this->notification->setUserId($reqData->userId);
            $this->notification->setTitle(notification_msgs('update_participant_care_requirement', 'title'));

            if (!empty($approve_status)) {
                $this->notification->setShortdescription(notification_msgs('update_participant_care_requirement', 'approve'));

                // approve request
                $this->approval->approveRequest();
            } else {

                $this->notification->setShortdescription(notification_msgs('update_participant_care_requirement', 'deny'));

                // deny request
                $this->approval->denyRequest();
            }

            $this->notification->createNotification();

            echo json_encode(array('status' => true));
        }
    }

    function approve_participant_shift() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'participant_create_shift');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->create_participant_shift($reqData);
            } else {
                $this->db->trans_rollback();
            }

            echo json_encode(array('status' => true));
        }
    }

}
