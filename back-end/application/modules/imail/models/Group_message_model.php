<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Group_message_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    function get_group_message($reqData, $currentAdminId) {
        $type = $reqData->team_type;
        $teamId = $reqData->teamId;


        $this->db->select(array('tbl_group_message.id', 'tbl_group_message.senderId', 'tbl_group_message.teamId', 'tbl_group_message.message_type', 'tbl_group_message.message', 'tbl_group_message.created', 'concat(firstname," ",lastname) as user_name', 'tbl_member.profile_image', 'tbl_member.gender', 'tbl_group_message_action.status'));

        $this->db->from('tbl_group_message');
        $this->db->join('tbl_group_message_action', 'tbl_group_message_action.messageId = tbl_group_message.id AND tbl_group_message_action.senderId = ' . $currentAdminId, 'inner');
        $this->db->join('tbl_member', 'tbl_member.id = tbl_group_message.senderId', 'inner');

        $this->db->where('tbl_group_message.teamId', $teamId);
        $this->db->order_by('tbl_group_message.id', 'asc');

        if ($type == 'department') {
            $this->db->where('tbl_group_message.type', 2);
        } else {
            $this->db->where('tbl_group_message.type', 1);
        }

        $query = $this->db->get();
        $result = $query->result();
//        last_query();
        $gr_message = array();
        if ($result) {
            foreach ($result as $val) {
                $x['message_type'] = $val->message_type;
                $x['created'] = $val->created;
                $x['user_name'] = $val->user_name;
                $x['user_img'] = get_admin_img($val->senderId, $val->profile_image, $val->gender);
                $x['senderId'] = $val->senderId;
                $x['message'] = $val->message;
                $x['attach_uri'] = base_url() . GROUP_MESSAGE_PATH . '/' . $teamId . '/' . $val->message;
                $x['status'] = ($val->senderId == $currentAdminId) ? 2 : $val->status;
                $x['id'] = $val->id;

                $gr_message[] = $x;
            }
        }

        return $gr_message;
    }

    function get_admin_groups($reqData, $currnetAdminId) {
        $colown = array('id', 'team_name', 'team_color');


        $this->db->select("IF(tbl_internal_message_team.adminId = " . $currnetAdminId . ", 1, 0) as update_permission");
        $this->db->select($colown);
        $this->db->from('tbl_internal_message_team');

        $this->db->join('tbl_internal_message_team_admin', 'tbl_internal_message_team_admin.teamId = tbl_internal_message_team.id', 'left');

        $this->db->where('(tbl_internal_message_team.adminId = ' . $currnetAdminId . ' or tbl_internal_message_team_admin.adminId = ' . $currnetAdminId . ')');
        $this->db->where('tbl_internal_message_team.archive', 0);
        $this->db->group_by('tbl_internal_message_team.id');

        $query = $this->db->get();
        return $query->result();
    }

    function get_team_department_info($reqData, $currentAmminId) {
        if ($reqData->team_type == 'team') {
            $result = $this->basic_model->get_row('internal_message_team', array('team_name'), array('id' => $reqData->teamId));
        } else {
            $result = $this->basic_model->get_row('department', array('name as team_name'), array('id' => $reqData->teamId));
        }

        return $result;
    }

    function get_team_member($obj) {
        $this->db->select(array('imta.adminId', 'admin.profile_image as adminProfile', 'admin.gender as adminGender'));
        $this->db->select("concat(admin.firstname, ' ',admin.lastname) as adminName");

        $this->db->from('tbl_internal_message_team_admin as imta');
        $this->db->join('tbl_member as admin', 'admin.id = imta.adminId', 'left');
        $this->db->join('tbl_internal_message_team as imt', 'imt.id = imta.teamId', 'left');

        $this->db->where(array('imta.teamId' => $obj->getId()));

        $query = $this->db->get();
        return $query->result();
    }

    function get_team_member_id($teamId) {
        $this->db->select(array('imta.adminId'));
        $this->db->from('tbl_internal_message_team_admin as imta');
        $this->db->where(array('imta.teamId' => $teamId));

        $query = $this->db->get();
        $team_members = $query->result();

        $team_members[] = $this->basic_model->get_row('internal_message_team', ['adminId'], ['id' => $teamId]);

        return $team_members;
    }

    function get_all_admin() {
        $this->db->select(array('m.id as adminId'));
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');
        $this->db->where(array('m.archive' => 0, 'm.status' => 1));

        $query = $this->db->get();
        $team_members = $query->result();

        return $team_members;
    }

    function get_group_message_unread_count($adminId) {
        $this->db->select(array('count(teamId) as count', 'tbl_group_message.type', 'tbl_group_message.teamId'));

        $this->db->from('tbl_group_message');
        $this->db->join('tbl_group_message_action', 'tbl_group_message_action.messageId = tbl_group_message.id AND tbl_group_message_action.senderId = ' . $adminId, 'left');
        $this->db->join('tbl_member', 'tbl_member.id = tbl_group_message.senderId', 'left');

        $this->db->where('tbl_group_message_action.status', 1);
        $this->db->group_by('tbl_group_message.type');
        $this->db->group_by('tbl_group_message.teamId');

        $query = $this->db->get();
        $result = $query->result();

        $counts = array('team_count' => [], 'department_count' => []);

        if (!empty($result)) {
            foreach ($result as $val) {
                if ($val->type == 1) {
                    $counts['team_count'][$val->teamId] = $val->count;
                } else {
                    $counts['department_count'][$val->teamId] = $val->count;
                }
            }
        }

//        last_query();
        return $counts;
    }

    function mark_message_read($reqData, $adminId) {
        $data = array(
            'status ' => 2,
        );

        $this->db->where_in('messageId', $reqData->messageId);
        $this->db->where('senderId', $adminId);
        $this->db->update(TBL_PREFIX . 'group_message_action', $data);
        return $this->db->affected_rows();
    }

    function check_user_exist_in_group($teamId, $adminId) {
        $tbl_internal_message_team_admin = TBL_PREFIX . 'internal_message_team_admin';
        $tbl_internal_message_team = TBL_PREFIX . 'internal_message_team';


        $this->db->select(array($tbl_internal_message_team_admin . '.adminId'));

        $this->db->from($tbl_internal_message_team_admin);
        $this->db->join($tbl_internal_message_team, $tbl_internal_message_team . '.id = ' . $tbl_internal_message_team_admin . '.teamId', 'left');

        $this->db->where(array($tbl_internal_message_team_admin . '.teamId' => $teamId));
        $this->db->where($tbl_internal_message_team_admin . '.adminId =' . $adminId . ' OR ' . $tbl_internal_message_team . '.adminId =' . $adminId);

        $query = $this->db->get();

        return $query->num_rows();
    }

}
