<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller
class Internal_imail extends MX_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model('Internal_model');
        $this->load->model('Group_message_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('internal_imail');
//        $this->load->logs('chat_log');
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

    public function index() {
        
    }

    public function get_internal_messages() {
        $reqData = request_handler('access_imail');

        $result = $this->Internal_model->get_internal_messages($reqData->data, $reqData->adminId);
        echo json_encode(array('status' => true, 'data' => $result));
    }

    public function get_single_chat() {
        $reqData = request_handler('access_imail');
        $currentAdminId = $reqData->adminId;

        if ($reqData->data) {
            $reqData = $reqData->data;

            $result = $this->Internal_model->get_single_chat($reqData, $currentAdminId);

            echo json_encode($result);
        }
    }

    function sendImail($objInMail) {
        // create message
        $messageId = $objInMail->createMessage();
        $objInMail->setMessageId($messageId);
        // create message content
        $objInMail->createMessageContent();

        return $messageId;
    }

    function create_update_team() {
        $reqestData = request_handler('create_imail');
        $this->loges->setCreatedBy($reqestData->adminId);

        $reqData = $reqestData->data;
        $this->form_validation->set_data((array) $reqData);

        $id = (!empty($reqData->id) ? $reqData->id : '');
        $validation_rules = array(
            array('field' => 'team_color', 'label' => 'team color', 'rules' => 'required'),
            array('field' => 'team_name', 'label' => 'Team name', 'rules' => 'callback_check_team_name_imail[' . json_encode(array('id' => $id, 'adminId' => $reqestData->adminId)) . ']'),
        );

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);
        if ($this->form_validation->run()) {

            require_once APPPATH . 'Classes/imail/InternalMessageTeam.php';
            $objInt = new InternalMessageTeamClass\InternalMessageTeam();

            $objInt->setAdminId($reqestData->adminId);
            $objInt->setTeam_color($reqData->team_color);
            $objInt->setTeam_name($reqData->team_name);
            $objInt->setMemberIds($reqData->team_member);
            $objInt->setArchive(0);

            if ($reqData->mode == 'update') {
                $objInt->setId($reqData->id);

                // update team
                $teamId = $objInt->updateTeam();
                $this->loges->setTitle('Update team by: user HCM-ID' . $reqestData->adminId);
            } else {
                // create team
                $teamId = $objInt->createTeam();
                $this->loges->setTitle('Create new team by: user HCM-ID' . $reqestData->adminId);
            }

            $objInt->InsertGroupMember();

            // create log
            $this->loges->setUserId($reqestData->adminId);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->createLog();

            $response = array('status' => true);
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        echo json_encode($response);
    }

    function check_team_name_imail($team_name, $data) {
        $data = json_decode($data, true);

        if (!empty($team_name)) {
            $colown = array('id');
            $where = array('adminId' => $data['adminId'], 'archive' => 0, 'team_name' => $team_name);

            if (!empty($data['id'])) {
                $where['id !='] = $data['id'];
            }

            $result = $this->basic_model->get_record_where('internal_message_team', $colown, $where);

            if (!empty($result)) {
                $this->form_validation->set_message('check_team_name_imail', 'You have already this team name');
                return false;
            }
        } else {
            $this->form_validation->set_message('check_team_name_imail', 'The team name is required field');
            return false;
        }

        return true;
    }

    function get_team_datails_and_department() {
        $reqestData = request_handler('access_imail');
        $reqData = $reqestData->data;

        $result['teamList'] = $this->Group_message_model->get_admin_groups($reqData, $reqestData->adminId);

        $result['department'] = $this->basic_model->get_record_where('department', array('id', 'name'), '');

        echo json_encode(array('status' => true, 'data' => $result));
    }

    function get_team_member() {
        $reqestData = request_handler('access_imail');
        $reqData = $reqestData->data;

        require_once APPPATH . 'Classes/imail/InternalMessageTeam.php';
        $objInt = new InternalMessageTeamClass\InternalMessageTeam();

        $objInt->setAdminId($reqestData->adminId);
        $objInt->setId($reqData->teamId);

        $result = $objInt->getTeamMember();

        echo json_encode(array('status' => true, 'data' => $result));
    }

    function remove_team_member() {
        $reqestData = request_handler('delete_imail');
        $reqData = $reqestData->data->loges;


        require_once APPPATH . 'Classes/imail/InternalMessageTeam.php';
        $objInt = new InternalMessageTeamClass\InternalMessageTeam();

        $objInt->setMemberIds($reqData->adminMemberId);
        $objInt->setId($reqData->teamId);

        $objInt->removeTeamMember();

        echo json_encode(array('status' => true));
    }

    function get_admin_name() {
        $reqestData = request_handler('access_imail');
        $reqData = $reqestData->data;
        if ($reqData) {
            $rows = $this->Internal_model->get_admin_name($reqData, $reqestData->adminId);

            echo json_encode($rows);
        }
    }

    function get_composer_admin_name() {
        $reqestData = request_handler('access_imail');
        $reqData = $reqestData->data;
        if ($reqData) {
            $rows = $this->Internal_model->get_composer_admin_name($reqData, $reqestData->adminId);

            echo json_encode($rows);
        }
    }

    function compose_new_mail() {
        $reqData = request_handlerFile('create_imail');
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData) {
            $current_admin = $reqData->adminId;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'content', 'label' => 'Content', 'rules' => 'required'),
                array('field' => 'title', 'label' => 'subject', 'rules' => 'required'),
                array('field' => 'to_user', 'label' => 'categories', 'rules' => 'required'),
                array('field' => 'submit_type', 'label' => 'Type', 'rules' => 'required'),
                array('field' => 'is_priority', 'label' => 'priority', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // compose new mail
                $this->Internal_model->compose_new_mail($reqData, $current_admin);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function reply_mail() {
        $reqData = request_handlerFile('create_imail');
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData) {
            $current_admin = $reqData->adminId;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'content', 'label' => 'To:', 'rules' => 'required'),
                array('field' => 'title', 'label' => 'subject', 'rules' => 'required'),
                array('field' => 'to_user', 'label' => 'categories', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // reply mail
                $this->Internal_model->reply_mail($reqData, $current_admin);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function validate_compose_mail($reqData) {
        $this->form_validation->set_data((array) $reqData);

        $validation_rules = array(
            array('field' => 'content', 'label' => 'mail content', 'rules' => 'required'),
            array('field' => 'title', 'label' => 'subject', 'rules' => 'required'),
            array('field' => 'to_user', 'label' => 'categories', 'rules' => 'required'),
            array('field' => 'contentId', 'label' => 'contentId', 'rules' => 'required'),
            array('field' => 'messageId', 'label' => 'messageId', 'rules' => 'required'),
            array('field' => 'submit_type', 'label' => 'Type', 'rules' => 'required'),
            array('field' => 'is_priority', 'label' => 'priority', 'rules' => 'required'),
        );

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {

            $response = array('status' => true);
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        return $response;
    }

    function send_draft_mail() {
        $reqData = request_handlerFile('create_imail');
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData) {
            $current_admin = $reqData->adminId;

            $this->validate_compose_mail($reqData);

            if ($this->form_validation->run()) {

                // save draft mail or open draft mail
                $this->Internal_model->save_or_send_draft_mail($reqData, $current_admin);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function get_internal_reply_person() {
        $reqData = request_handler('access_imail');
        $currentAdminId = $reqData->adminId;
        $reqData = $reqData->data;

        if ($reqData) {
            $rows = $this->Internal_model->get_reply_to_person($reqData, $currentAdminId);

            echo json_encode(array('status' => true, 'data' => $rows));
        }
    }

    function add_to_favourite() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'is_fav', 'label' => 'favourite status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('is_fav' => $reqtData->is_fav);
                $where = array('messageId' => $reqtData->messageId, 'userId' => $current_admin);
                $this->basic_model->update_records('internal_message_action', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function add_to_flage() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'is_flage', 'label' => 'flag status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('is_flage' => $reqtData->is_flage);
                $where = array('messageId' => $reqtData->messageId, 'userId' => $current_admin);
                $this->basic_model->update_records('internal_message_action', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function add_to_block() {
        $reqestData = request_handler('update_imail');
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'is_block', 'label' => 'block status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('is_block' => $reqtData->is_block);
                $where = array('id' => $reqtData->messageId);
                $this->basic_model->update_records('internal_message', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function add_to_archive() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'archive', 'label' => 'archive status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('archive' => $reqtData->archive);
                $where = array('messageId' => $reqtData->messageId, 'userId' => $current_admin);
                $this->basic_model->update_records('internal_message_action', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function mark_as_read_unread() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $this->Internal_model->mark_read_unread($reqtData->messageId, $current_admin, $reqtData->action);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function get_group_message() {
        $type = 'team';
        $reqData = array('tm' => 1, 'ty' => 'team');
        $res = $this->Group_message_model->get_group_message((object) $reqData, $currentAdminId = 52);
        echo "<pre>";
        print_r($res);
    }

    function get_team_department_info() {
        $reqData = request_handler('access_imail');
        $currentAdminId = $reqData->adminId;

        if ($reqData) {
            $data = $this->Group_message_model->get_team_department_info($reqData->data, $currentAdminId);

            $response = array('status' => true, 'data' => $data);
            echo json_encode($response);
        }
    }

    function upload_group_chat_attachment() {
        include APPPATH . 'Classes/imail/GroupChatMessage.php';

        $reqData = request_handlerFile('access_imail');
        $currentAdminId = $reqData->adminId;
        $groupObj = new GroupChatMessage();

        if (!empty($_FILES) && $_FILES['attachments']['error'] == 0) {

            $config['upload_path'] = GROUP_MESSAGE_PATH;
            $config['input_name'] = 'attachments';
            $config['directory_name'] = $reqData->tm;
            $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|iso|zip|odt|rtf';

            $is_upload = do_upload($config);

            if (isset($is_upload['error'])) {
                echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                exit();
            } else {
                $reqData->message = $is_upload['upload_data']['file_name'];

                // insert data
                //$groupObj->store_group_message($reqData, $currentAdminId);
                echo json_encode(array('status' => true, 'filename' => $reqData->message));
                exit();
            }
        }
    }

    function get_message_count() {
        $reqData = request_handler('access_imail');
        $res = $this->Group_message_model->get_group_message_unread_count($reqData->adminId);
        echo json_encode(array('status' => true, 'data' => $res));
    }

    function get_mail_pre_filled_data() {
        $reqData = request_handler('access_imail');
        $res = $this->Internal_model->get_mail_pre_filled_data($reqData->data, $reqData->adminId);
        echo json_encode(array('status' => true, 'data' => $res));
    }

    function get_group_chating() {
        $reqData = request_handler('access_imail');
        $res = $this->Group_message_model->get_group_message($reqData->data, $reqData->adminId);
        echo json_encode(array('status' => true, 'data' => $res, 'adminId' => $reqData->adminId));
    }

}
