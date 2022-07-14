<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InternalMessageWebsocket
 *
 * @author user Corner Stone Solutions
 */
require APPPATH . 'Classes/imail/InternalMessageTeam.php';

class GroupChatMessage {

    public $CI;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('imail/Internal_model');
        $this->CI->load->model('imail/Group_message_model');
    }

    /* function: store_groupe_message
     * 
     * this function use for store message in database
     */

    function store_group_message($reqData, $adminId) {

        $data = array(
            'teamId' => $reqData->tm,
            'type' => ($reqData->ty == 'team') ? 1 : 2,
            'message_type' => $reqData->message_type,
            'message' => $reqData->message,
            'senderId' => $adminId,
            'created' => DATE_TIME
        );

        return $messageId = $this->CI->basic_model->insert_records('group_message', $data, $multiple = FALSE);
    }

    /* function : mark_message_read
     * 
     * this function use for mark message as read for those member who messag
     * readed
     */

    function mark_message_read($reqData, $adminId) {

        $this->CI->Group_message_model->mark_message_read($reqData, $adminId);
    }

    /* function : set_unread_status_for_group_member
     * 
     * use: set message unread for all user who exist member in team and department
     * retun team member list
     */

    function set_unread_status_for_group_member($reqData, $messageId, $adminId) {

        if ($reqData->ty == 'team') {
            $this->CI->load->model('imail/Group_message_model');

            // get all team member admin
            $team_members = $this->CI->Group_message_model->get_team_member_id($reqData->tm);
//            print_r($team_members);
        } else {
            // load Internal model
            $this->CI->load->model('imail/Group_message_model');

            // get all admin id
            $where = array('archive' => 0, 'status' => 1);
            $team_members = $this->CI->Group_message_model->get_all_admin();
        }

        if (!empty($team_members)) {
            $action_data = array();

            foreach ($team_members as $val) {
                $status = ($val->adminId == $adminId) ? 2 : 1;

                $action_data[] = array(
                    'messageId' => $messageId,
                    'senderId' => $val->adminId,
                    'status ' => $status,
                );
            }

            $this->CI->basic_model->insert_records('group_message_action', $action_data, $multiple = true);

            return $team_members;
        }
    }

    /* function : get_group_message
     * 
     * 
     * use :- load all default message of particular 
     * group and department
     */

    function get_group_message($reqData, $adminId) {
        return $this->CI->Group_message_model->get_group_message($reqData, $adminId);
    }

    /* function : mark_group_all_message_readed
     * 
     * use: if user open group chat mean is read all the message of group 
     * then mark all group message as readed
     */

    function mark_group_all_message_readed($reqData, $adminId) {
        if ($reqData->ty === 'department') {
            
        } elseif ($reqData->ty === 'team') {
            
        }
    }

    /* function : check_user_of_this_group
     * 
     * use: this user exixt in this group or not if exist if exist then return true
     * and if not then return false
     */

    function check_user_of_this_group($reqData, $adminId) {

        if ($reqData->ty === 'department') {
            return true;
        } elseif ($reqData->ty === 'team') {
            $return = $this->CI->Group_message_model->check_user_exist_in_group($reqData->tm, $adminId);
            return $return;
        }
    }

}
