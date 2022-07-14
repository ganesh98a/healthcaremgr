<?php

require_once APPPATH . 'Classes/websocket/Websocket.php';

/**
 * Description of Notification
 *
 * @author corner stone solutions
 */
class Notification {

    protected $CI;
    private $userId;
    private $user_type;
    private $title;
    private $shortdescription;
    private $created = DATE_TIME;
    private $status = 0;
    private $sender_type = 2;

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    function setUser_type($user_type) {
        $this->user_type = $user_type;
    }

    function getUser_type() {
        return $this->user_type;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function getTitle() {
        return $this->title;
    }

    function setShortdescription($shortdescription) {
        $this->shortdescription = $shortdescription;
    }

    function getShortdescription() {
        return $this->shortdescription;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getStatus() {
        return $this->status;
    }

    function setSender_type($sender_type) {
        $this->sender_type = $sender_type;
    }

    function getSender_type() {
        return $this->sender_type;
    }

    function createNotification() {
        $data = array(
            'userId' => $this->userId,
            'user_type' => $this->user_type,
            'title' => $this->title,
            'shortdescription' => $this->shortdescription,
            'created' => $this->created,
            'status' => $this->status,
            'sender_type' => $this->sender_type
        );

        $this->CI->db->insert(TBL_PREFIX . 'notification', $data);

        $notificationId = $this->CI->db->insert_id();

        $wbObj = new Websocket();

        if ($wbObj->check_webscoket_on() && $this->user_type == 2) {
            
            $send_data = ['notificationId' => $notificationId, 'participantId' => $this->userId];
            $data = array('chanel' => 'server', 'req_type' => 'participant_approval_or_update_notification', 'token' => $wbObj->get_token(), 'data' => $send_data);
            $wbObj->send_data_on_socket($data);
        }
    }

}
