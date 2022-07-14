<?php

namespace InternalMessageTeamClass;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InternalMessageTeam
 *
 * @author Corner stone solution
 */
class InternalMessageTeam {

    private $id;
    private $adminId;
    private $team_name;
    private $team_color;
    private $created;
    private $archive;
    private $memberIds;

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setAdminId($adminId) {
        $this->adminId = $adminId;
    }

    function getAdminId() {
        return $this->adminId;
    }

    function setTeam_name($team_name) {
        $this->team_name = $team_name;
    }

    function getTeam_name() {
        return $this->team_name;
    }

    function setTeam_color($team_color) {
        $this->team_color = $team_color;
    }

    function getTeam_color() {
        return $this->team_color;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function setMemberIds($memberIds) {
        $this->memberIds = $memberIds;
    }

    function getMemberIds() {
        return $this->memberIds;
    }

    function createTeam() {
        $CI = & get_instance();

        $team_details = array('adminId' => $this->adminId, 'team_name' => $this->team_name, 'team_color' => $this->team_color, 'created' => DATE_TIME, 'archive' => $this->archive);

        $teamId = $CI->basic_model->insert_records('internal_message_team', $team_details, $multiple = FALSE);

        $this->setId($teamId);
        return $teamId;
    }

    function updateTeam() {
        $CI = & get_instance();

        $team_details = array('team_name' => $this->team_name, 'team_color' => $this->team_color,);
        $where = array('id' => $this->id, 'adminId' => $this->adminId);

        $CI->basic_model->update_records('internal_message_team', $team_details, $where);
        return true;
    }

    function InsertGroupMember() {
        $CI = & get_instance();

        $memberIds = array();

        if ($this->memberIds) {
            foreach ($this->memberIds as $val) {
                if ($val->member) {
                    $memberIds[] = array('adminId' => $val->member->value, 'teamId' => $this->id);
                }
            }

            if (!empty($memberIds))
                $CI->basic_model->insert_records('internal_message_team_admin', $memberIds, $multiple = true);
        }
    }

    function getTeamMember() {
        $CI = & get_instance();

        return $CI->Group_message_model->get_team_member($this);
    }

    function removeTeamMember() {
        $CI = & get_instance();

        $where = array('teamId' => $this->id, 'adminId' => $this->memberIds);
        
        return $CI->basic_model->delete_records('internal_message_team_admin', $where);
    }

}
