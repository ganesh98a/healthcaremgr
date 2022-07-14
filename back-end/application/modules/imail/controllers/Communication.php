<?php

defined('BASEPATH') or exit('No direct script access allowed');
include APPPATH . 'Classes/websocket/Websocket.php';
include APPPATH . 'Classes/imail/GroupChatMessage.php';
include APPPATH . 'Classes/admin/permission.php';

//class Master extends MX_Controller
class Communication extends CI_Controller {

    public $host;
    public $port;
    public $null;
    public $teams;
    public $departments;
    public $active_user_list = []; //active admin user
    public $active_participant_list = []; // active participant user

    function __construct() {

        parent::__construct();
        $this->host = WEBSOCKET_HOST_NAME;
        $this->port = WEBSOCKET_HOST_PORT;
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->null = null;
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

    function start_communication() {
        shell_exec('nohup php /path/to/server.php 2>&1 > /dev/null &');

        try {

            $wbObj = new Websocket();

            //Create TCP/IP sream socket
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            //reuseable port
            socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

            //bind socket to specified host
            socket_bind($socket, 0, $this->port);

            //listen to port
            socket_listen($socket);

            //create & add listning socket to the list
            $clients = array($socket);

            $system_socket = $socket;
            //start endless loop, so that our script doesn't stop
            while (true) {
                //manage multipal connections
                $changed = $clients;

                //returns the socket resources in $changed array
                socket_select($changed, $this->null, $this->null, 0, 10);


                //check for new socket
                if (in_array($socket, $changed)) {

                    //accpet new socket
                    $socket_new = socket_accept($socket);


                    //read data sent by the socket
                    $header = socket_read($socket_new, 1024, PHP_BINARY_READ);

                    //perform websocket handshake
                    $reqData = $this->perform_handshaking($header, $socket_new);


                    if (!empty($reqData)) {

                        //add socket to client array
                        $clients[] = $socket_new;

                        if ($reqData['req_type'] === 'user_connect_to_socket') {

                            if ($reqData['user_type'] === 'participant') {
                                // add user to participant list
                                $this->add_user_in_participant_listing($reqData, $socket_new);
                            } else {
                                // add user to user list
                                $this->add_user_in_user_listing($reqData, $socket_new);
                            }
                        }
                    } else {

                        socket_close($socket_new);
                    }
                }

                //loop through all connected sockets
                foreach ($changed as $changed_socket) {
                    if ($changed_socket === $system_socket)
                        continue;

                    //check for any incomming data
                    while (socket_recv($changed_socket, $buf, 1024, 0) >= 1) {

                        $received_text = $wbObj->unmask($buf); //unmask data

                        $rc_msg = (json_decode($received_text, true)); //json decode .
                        // check here req_type is not empty

                        if (!empty($rc_msg['req_type'])) {

                            if ($rc_msg['req_type'] === 'single_group_chat') {

                                // here manage group chat
                                $this->manage_group_chat($rc_msg);
                            } elseif ($rc_msg['req_type'] === 'user_connect_to_socket') {

                                // here any request comes from login admin site
                                $this->manage_user_request($rc_msg);
                            } elseif ($rc_msg['req_type'] === 'admin_internal_imail_notification') {

                                // here new imail notification notify
                                $this->notify_user_internal_imail_notification($rc_msg);
                            } elseif ($rc_msg['req_type'] === 'user_external_imail_notification') {

                                // here new external imail notification notify
                                $this->notify_user_external_imail_notification($rc_msg);
                            } elseif ($rc_msg['req_type'] === 'client_update_notification') {

                                // here new imail notification notify
                                $this->notify_user_notification($rc_msg);
                            } elseif ($rc_msg['req_type'] === 'update_user_admin') {

                                // here call function for update admin
                                $this->update_user_admin($rc_msg);
                            } elseif ($rc_msg['req_type'] === 'participant_approval_or_update_notification') {

                                // here call function for update participant mean any thing update form admin site then notify to client
                                $this->notify_to_participant_for_new_notification($rc_msg);
                            } elseif ($rc_msg['req_type'] === 'recruitment_admin_actionable_notification') {
                                // here call function for update participant mean any thing update form admin site then notify to client
                                $this->notify_to_recruitment_admin_actionable_notification($rc_msg);
                            }
                        }

                        break 2; //exist this loop
                    }

                    $buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
                    if ($buf === false) { // check disconnected client
                        // remove client for $clients array
                        $found_socket = array_search($changed_socket, $clients);

                        //                    socket_getpeername($changed_socket, $ip);
                        if (in_array($changed_socket, $this->active_user_list)) {
                            $found_adminId = array_search($changed_socket, $this->active_user_list);
                            unset($this->active_user_list[$found_adminId]);
                        }

                        if (in_array($changed_socket, $this->active_participant_list)) {
                            $found_adminId = array_search($changed_socket, $this->active_participant_list);
                            unset($this->active_participant_list[$found_adminId]);
                        }

                        unset($clients[$found_socket]);
                    }
                }
            }
            // close the listening socket
            socket_close($socket);
        } catch (Exception $exc) {
            echo 'girish';
            echo $exc->getTraceAsString();
        }
    }

    function send_message($msg, $clients) {
        foreach ($clients as $changed_socket) {
            @socket_write($changed_socket, $msg, strlen($msg));
        }
        return true;
    }

    //handshake new client.
    function perform_handshaking($receved_header, $client_conn) {
        $wbObj = new Websocket();

        $get_vars = array();
        if (preg_match("/GET (.*) HTTP/", $receved_header, $match)) {

            // check require paramter coming or not
            $res = $this->check_request_valid_params($match);
            //print_r($res);

            if ($res['status'] === true) {
                $get_vars = $res['data'];
                $websocket_status = false;

                if (!empty($get_vars['chanel'] === 'server')) {

                    // check token of server
                    if ($wbObj->check_server_token($get_vars['token'])) {
                        $websocket_status = true;
                    }
                } elseif ($get_vars['chanel'] === 'client') {

                    if ($get_vars['user_type'] == 'participant') {
                        $get_vars['participantId'] = $wbObj->check_participant_token($get_vars['token']);

                        if (!empty($get_vars['participantId']))
                            $websocket_status = true;
                    } else {

                        $get_vars['adminId'] = $wbObj->check_token($get_vars['token']);

                        if (!empty($get_vars['adminId']))
                            $websocket_status = true;
                    }
                }


                if ($websocket_status === true) {
                    $headers = array();

                    $lines = preg_split("/\r\n/", $receved_header);

                    foreach ($lines as $key => $line) {
                        $line = chop($line);

                        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                            $headers[$matches[1]] = $matches[2];
                        }
                    }

                    $wbObj->handshake($this->host, $this->port, $client_conn, $headers);
                    return $get_vars;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    function check_request_valid_params($match) {
        $parts = parse_url($match[1]);

        if (empty($parts['query'])) {
            return array('status' => false, 'error' => 'query paramter not found');
        }

        $reqData = array();
        parse_str($parts['query'], $reqData);

        $this->form_validation->set_data((array) $reqData);

        $validation_rules = array(
            array('field' => 'req_type', 'label' => 'req_type:', 'rules' => 'required'),
            array('field' => 'chanel', 'label' => 'chanel', 'rules' => 'required'),
            array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
        );

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            $response = array('status' => true, 'data' => $reqData);
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        return $response;
    }

    function send_message_in_group($notification, $reqData, $team_member) {
        $wbObj = new Websocket();

        if (!empty($team_member)) {
            foreach ($team_member as $val) {
                if (!empty($this->active_user_list[$val->adminId])) {

                    $socketId = $this->active_user_list[$val->adminId];
                    $wbObj->send_message($notification, $socketId);
                }
            }
        }
    }

    function send_group_message($reqData) {
        $wbObj = new Websocket();
        $groupObj = new GroupChatMessage();


        $adminId = $wbObj->check_token($reqData['token']);
        $chk_res = $groupObj->check_user_of_this_group((object) $reqData, $adminId);
        if (!$chk_res) {
            return false;
        }

        $messageData = $wbObj->get_admin_details($adminId);

        $messageData['message'] = $reqData['message'];
        $messageData['created'] = DATE_TIME;
        $messageData['senderId'] = $adminId;
        $messageData['message_type'] = $reqData['message_type'];


        $messageData['messageId'] = $groupObj->store_group_message((object) $reqData, $adminId);

        // set unread this message for to all member and return team member list
        $team_members = $groupObj->set_unread_status_for_group_member((object) $reqData, $messageData['messageId'], $adminId);

        if ($reqData['message_type'] == 2) {
            $messageData['attach_uri'] = base_url() . GROUP_MESSAGE_PATH . '/' . $reqData['tm'] . '/' . $messageData['message'];
        }

        $gr_msg = array('type' => 'usermsg', 'message_data' => $messageData, 'ty' => $reqData['ty'], 'tm' => $reqData['tm']);


        if ($reqData['ty'] == 'team') {
            $this->send_message_notification_to_team($gr_msg, $reqData['tm'], $team_members); //send notification all active team member
            $this->send_message_in_group($gr_msg, $reqData, $team_members); //send data
        } else {
            $this->send_message_notification_to_department($gr_msg, $reqData['tm'], $team_members); //send notification all active department member
            $this->send_message_in_group($gr_msg, $reqData, $team_members); //send data
        }
    }

    function mark_as_message_readed($rc_msg) {
        $wbObj = new Websocket();
        $groupObj = new GroupChatMessage();

        $adminId = $wbObj->check_token($rc_msg['token']);

        $groupObj->mark_message_read((object) $rc_msg, $adminId);
    }

    function manage_group_chat($rc_msg) {
        if ($rc_msg['ms_type'] == 'new_message') {

            // send new message to all croup user
            $this->send_group_message($rc_msg);
        } elseif ($rc_msg['ms_type'] == 'read_notify') {

            // insert entry in database for message read
            $this->mark_as_message_readed($rc_msg);
        }
    }

    function add_user_in_user_listing($reqData, $socket_new) {
        $adminId = $reqData['adminId'];

        $this->active_user_list[$adminId] = $socket_new;
    }

    function add_user_in_participant_listing($reqData, $socket_new) {
        $participantId = $reqData['participantId'];

        $this->active_participant_list[$participantId] = $socket_new;
    }

    function send_message_notification_to_team($gr_msg, $teamId, $team_members) {
        $wbObj = new Websocket();

        if (!empty($team_members)) {
            foreach ($team_members as $val) {
                if (!empty($this->active_user_list[$val->adminId])) {
                    $socketId = $this->active_user_list[$val->adminId];
                    $notification = ['type' => 'unread_group_message_notification', 'data' => ['teamId' => $teamId, 'type' => 'team']];

                    $wbObj->send_message($notification, $socketId);
                }
            }
        }
    }

    function send_message_notification_to_department($gr_msg, $teamId, $team_members) {
        $wbObj = new Websocket();

        if (!empty($team_members)) {

            foreach ($team_members as $val) {
                if (!empty($this->active_user_list[$val->adminId])) {

                    $socketId = $this->active_user_list[$val->adminId];
                    $notification = ['type' => 'unread_group_message_notification', 'data' => ['teamId' => $teamId, 'type' => 'department']];
                    $wbObj->send_message($notification, $socketId);
                }
            }
        }
    }

    function notify_user_internal_imail_notification($reqData) {
        $this->load->model('admin/Notification_model');
        $this->load->model('imail/Internal_model');

        $wbObj = new Websocket();

        if (!empty($reqData['data'])) {
            $dt = $reqData['data'];

            foreach ($dt as $adminId) {

                // update imail of that people who is currently active
                if (!empty($this->active_user_list[$adminId])) {


                    $result_ex_im = $this->Notification_model->get_external_imail_notification($adminId);
                    $result_int = $this->Notification_model->get_internal_imail_notification($adminId);

                    $result = array_merge($result_ex_im, $result_int);
                    $res = ['ImailNotificationData' => $result, 'internal_imail_count' => count($result_int), 'external_imail_count' => count($result_ex_im)];

                    $socketID = $this->active_user_list[$adminId];

                    $msg = ['type' => 'imail_notification', 'data' => $res];
                    $wbObj->send_message($msg, $socketID);


                    $int_msg = ['type' => 'internal_imail_listing', 'data' => ''];
                    $wbObj->send_message($int_msg, $socketID);
                }
            }
        }
    }

    function notify_user_external_imail_notification($reqData) {
        $this->load->model('admin/Notification_model');
        $this->load->model('imail/Internal_model');

        $wbObj = new Websocket();

        if (!empty($reqData['data'])) {
            $dt = $reqData['data'];

            if (!empty($dt[1])) {
                foreach ($dt[1] as $adminId) {

                    // update imail of that people who is currently active
                    if (!empty($this->active_user_list[$adminId])) {


                        $result_ex_im = $this->Notification_model->get_external_imail_notification($adminId);
                        $result_int = $this->Notification_model->get_internal_imail_notification($adminId);

                        $result = array_merge($result_ex_im, $result_int);
                        $res = ['ImailNotificationData' => $result, 'internal_imail_count' => count($result_int), 'external_imail_count' => count($result_ex_im)];

                        $socketID = $this->active_user_list[$adminId];

                        $msg = ['type' => 'imail_notification', 'data' => $res];
                        $wbObj->send_message($msg, $socketID);


                        $int_msg = ['type' => 'external_imail_listing', 'data' => ''];
                        $wbObj->send_message($int_msg, $socketID);
                    }
                }
            }
            if (!empty($dt[2])) {

                foreach ($dt[2] as $participantId) {
                    if (!empty($this->active_participant_list[$participantId])) {

                        $socketID = $this->active_participant_list[$participantId];

                        $msg = ['type' => 'imail_and_notification', 'data' => ''];

                        $wbObj->send_message($msg, $socketID);
                    }
                }
            }
        }
    }

    function notify_user_notification($reqData) {
        $this->load->model('admin/Notification_model');

        $wbObj = new Websocket();

        if (!empty($reqData['data'])) {
            $dt = $reqData['data'];
            $notificationId = $dt['notificationId'];

            $notificaitonData = $this->Notification_model->get_single_notification($notificationId);

            if (!empty($this->active_user_list) && !empty($notificaitonData)) {
                foreach ($this->active_user_list as $val) {

                    $msg = ['type' => 'bell_notification', 'data' => $notificaitonData];
                    $wbObj->send_message($msg, $val);
                }
            }
        }
    }

    function notify_to_participant_for_new_notification($reqData) {
        $this->load->model('admin/Notification_model');

        $wbObj = new Websocket();

        if (!empty($reqData['data'])) {
            $dt = $reqData['data'];
            $participantId = $dt['participantId'];

            if (!empty($this->active_participant_list[$participantId])) {
                $socketID = $this->active_participant_list[$participantId];
                $msg = ['type' => 'imail_and_notification', 'data' => ''];
                $wbObj->send_message($msg, $socketID);
            }
        }
    }

    function update_user_admin($reqData) {
        $wbObj = new Websocket();
        if ($reqData['data']) {
            $dt = $reqData['data'];

            if (!empty($this->active_user_list[$dt['adminId']])) {
                $stts = false;

                if ($dt['update_type'] === 'update_permission') {
                    $stts = true;

                    $objPerm = new classPermission\Permission();

                    // get current user permission
                    $result = $objPerm->get_all_permission($dt['adminId']);
                    $msg = ['type' => 'update_permission', 'data' => $result];
                } elseif ($dt['update_type'] === 'logout_admin_user') {

                    $stts = true;
                    $msg = ['type' => 'logout_admin_user', 'data' => true];
                }

                if ($stts) {
                    $socketID = $this->active_user_list[$dt['adminId']];
                    $wbObj->send_message($msg, $socketID);
                }
            }
        }
    }

    function notify_to_recruitment_admin_actionable_notification($reqData) {
        $this->load->model('admin/Notification_model');

        $wbObj = new Websocket();

        if (!empty($reqData['data'])) {
            if (!empty($this->active_user_list)) {
                foreach($this->active_user_list as $key =>$socketID ){
                    $msg = ['type' => 'recruitment_admin_actionable_notification', 'data' => true];
                    $wbObj->send_message($msg, $socketID);
                }              
            }
        }
    }

}
