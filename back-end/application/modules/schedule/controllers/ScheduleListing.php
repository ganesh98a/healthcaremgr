<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ScheduleListing extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Listing_model');
        $this->load->model('Roster_model');
        $this->loges->setLogType('shift');
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

    public function get_unconfirmed_and_quote_shifts() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Listing_model->unconfirmed_shift($reqData);
            echo json_encode($result);
        }
    }

    public function get_rejected_and_cancelled() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Listing_model->rejected_and_cancelled_shift($reqData);
            echo json_encode($result);
        }
    }

    public function get_shift_listing() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Listing_model->get_shift_listing($reqData);
            echo json_encode($result);
        }
    }

    public function move_to_app() {
        $reqData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqData->adminId);



        $moveable_data = array();
        if (!empty($reqData->data)) {
            if (!empty($reqData->data)) {
                foreach ($reqData->data as $key => $val) {
                    if ($val) {
                        $moveable_data[] = $key;
                    }
                }

                if (!empty($moveable_data)) {
                    $return = $this->basic_model->update_records('shift', $data = array('push_to_app' => 1), $where = 'id IN (' . implode(', ', $moveable_data) . ')');
                    $return = array('status' => true);

                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->setTitle('Move to app : Shift Ids ' . implode(', ', $moveable_data));
                    $this->loges->createLog();

                    #This method send notification to all member under 40 km of shift location
                    $this->Listing_model->send_notification_to_all_member($moveable_data);
                } else {
                    $return = array('status' => false, 'error' => 'Please select at least one shift');
                }
            }
        } else {
            $return = array('status' => false, 'error' => 'Please select at least one shift');
        }
        echo json_encode($return);
    }

    public function reinitiate_shift() {
        $reqData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData->data) {
            $shiftId = $reqData->data->shiftId;

            $this->loges->setUserId($shiftId);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->setTitle('Reinitiate shift : Shift Id ' . $shiftId);
            $this->loges->createLog();

            $result = $this->basic_model->update_records('shift', $data = array('status' => 1), $where = array('id' => $shiftId));
            echo json_encode(array('status' => true));
        }
    }

    public function get_roster() {
        $reqData = request_handler('access_schedule');

        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Roster_model->get_active_roster($reqData);
            echo json_encode($result);
        }
    }

    

    public function get_roster_loges() {
        $reqestData = request_handler('access_schedule');
        if ($reqestData->data) {

            $result = $this->Roster_model->get_roster_logs($reqestData->data);

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    function get_shift_category_options() {
        $reqestData = request_handler('access_schedule');
        if ($reqestData->data) {

            $result = $this->basic_model->get_record_where("finance_time_of_the_day", ["id as value", "short_name as label"], ["archive" => 0]);

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }


}
