<?php

namespace DisableUserClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class
/*
 * Filename: Member.php
 * Desc: The member file defines a module which checks the details and updates of members, upcoming shifts, create new availability, cases(FMS), create cases.
 * @author YDT <yourdevelopmentteam.com.au>
*/

if (!defined("BASEPATH")) exit("No direct script access allowed");

/*
 * Class: Member
 * Desc: The Member Class is a class which holds infomation about members like memberid, firstname, lastname etc.
 * The class includes variables and some methods. The methods are used to get and store information of members.
 * The visibility mode of this variables are private and the methods are made public.
 * Created: 01-08-2018
*/

class DisableUser
{
    public $CI;
    function __construct()
    {
        $this->CI = &get_instance();
        //$this->CI->load->model('Org_model');
        $this->CI->load->model('Basic_model');
    }

    private $id;
    private $crm_staff_id;
    private $disable_account;
    private $account_allocated;
    private $account_allocated_to;
    private $relevant_note;


    function setId($id)
    {
        $this->id = $id;
    }
    function getId()
    {
        return $this->id;
    }

    function setCrmStaffId($crm_staff_id)
    {
        $this->crm_staff_id = $crm_staff_id;
    }
    function getCrmStaffId()
    {
        return $this->crm_staff_id;
    }


    function setDisableAccount($disable_account)
    {
        $this->disable_account = $disable_account;
    }
    function getDisableAccount()
    {
        return $this->disable_account;
    }

    function setAccountAllocated($account_allocated)
    {
        $this->account_allocated = $account_allocated;
    }
    function getAccountAllocated()
    {
        return $this->account_allocated;
    }

    function setAccountAllocatedTo($account_allocated_to)
    {
        $this->account_allocated_to = $account_allocated_to;
    }
    function getAccountAllocatedTo()
    {
        return $this->account_allocated_to;
    }

    function setRelevantNote($relevant_note)
    {
        $this->relevant_note = $relevant_note;
    }
    function getRelevantNote()
    {
        return $this->relevant_note;
    }
    function setStaffDetails($staff_details)
    {
        $this->staff_details = $staff_details;
    }
    function getStaffDetails()
    {
        return $this->staff_details;
    }



    public function get_organisation_profile()
    {
        return $this->CI->Org_model->get_organisation_profile($this);
    }

    public function get_organisation_about()
    {
        return $this->CI->Org_model->get_organisation_about($this);
    }

    public function get_sub_org()
    {
        return $this->CI->Org_model->get_sub_org($this);
    }

    function check_exist_disable_recruiter()
    {
        $where = array('crm_staff_id' => $this->getCrmStaffId());
        $result = $this->CI->Basic_model->get_record_where('crm_staff_disable', 'id,crm_staff_id', $where);
        if ($result) {
            return $result[0]->id;
        } else {
            return false;
        }
    }

    function add_disable_recruiter()
    {
        $staff_data = $this->getStaffDetails();
        $tbl_1 = TBL_PREFIX . 'member';
        $tbl_2 = TBL_PREFIX . 'crm_participant';
        $tbl_4 = TBL_PREFIX . 'crm_participant_schedule_task';
        $this->CI->db->select(array($tbl_2 . '.id as participant_id'));
        $this->CI->db->from($tbl_1);
        $this->CI->db->join('tbl_crm_participant', 'tbl_crm_participant.assigned_to = tbl_member.id', 'inner');
        $this->CI->db->where($tbl_1 . '.id=', $this->getCrmStaffId());
        $this->CI->db->where('tbl_crm_participant.booking_status=4');
        // $this->CI->db->where_in(TBL_PREFIX . 'crm_participant.booking_status', [2, 4, 5]);
        $participant_data = $this->CI->db->get();

        if ($this->CI->db->affected_rows() > 0) {
            foreach ($participant_data->result_array() as $value) {
                if ($this->getAccountAllocated() == '1') {
                    $assigned_counts =  $this->assign_participant($this->getCrmStaffId());
                    if (!empty($assigned_counts)) {
                        $min = min($assigned_counts);
                        $key = array_search($min, $assigned_counts);
                        $where = array($tbl_2 . ".id" => $value['participant_id']);
                        $data = array($tbl_2 . ".assigned_to" => $key);

                        $task_where = array(
                            $tbl_4 . ".crm_participant_id" => $value['participant_id'],
                            $tbl_4 . ".due_date >=" => date('Y-m-d'),
                        );
                        $task_data = array($tbl_4 . ".assign_to" => $key);

                        $this->CI->Basic_model->update_records('crm_participant', $data, $where);
                        $this->CI->Basic_model->update_records('crm_participant_schedule_task', $task_data, $task_where);
                    } else {
                        return false;
                    }
                } else {
                    $where = array($tbl_2 . ".id" => $value['participant_id']);
                    $data = array($tbl_2 . ".assigned_to" => $this->getAccountAllocatedTo());
                    $query = $this->CI->db->query("SELECT status from tbl_crm_staff where admin_id=" . $this->getAccountAllocatedTo())->row_array();
                    if ($query['status'] == 1) {
                        $task_where = array(
                            $tbl_4 . ".crm_participant_id" => $value['participant_id'],
                            $tbl_4 . ".due_date >=" => date('Y-m-d'),
                        );
                        $task_data = array($tbl_4 . ".assign_to" => $this->getAccountAllocatedTo());
                        $this->CI->Basic_model->update_records('crm_participant', $data, $where);
                        $this->CI->Basic_model->update_records('crm_participant_schedule_task', $task_data, $task_where);
                    } else {
                        return false;
                    }
                }
            }

            foreach ($staff_data as $value) {
                if (!empty($value->changed_id)) {
                    $where = array($tbl_2 . ".id" => $value->p_id);
                    $p_data = array($tbl_2 . ".assigned_to" => $value->changed_id);
                    $query = $this->CI->db->query("SELECT status from tbl_crm_staff where admin_id=" . $value->changed_id)->row_array();
                    if ($query['status'] == 1) {
                        // $t_where = array($tbl_4 . ".crm_participant_id" => $value->p_id);
                        $t_where =  array(
                            $tbl_4 . ".crm_participant_id" => $value->p_id,
                            $tbl_4 . ".due_date >=" => date('Y-m-d'),
                        );
                        $t_data = array($tbl_4 . ".assign_to" => $value->changed_id);
                        $this->CI->Basic_model->update_records('crm_participant', $p_data, $where);
                        $this->CI->Basic_model->update_records('crm_participant_schedule_task', $t_data, $t_where);
                    } else {
                        return false;
                    }
                }
            }
        }
        $data = array(
            'crm_staff_id' => $this->getCrmStaffId(),
            'disable_account' => $this->getDisableAccount(),
            'account_allocated' => $this->getAccountAllocated(),
            'relevant_note' => $this->getRelevantNote(),
        );
        $this->CI->Basic_model->insert_records('crm_staff_disable', $data);
        $update_user = $this->CI->Basic_model->update_records('crm_staff', array('status' => 0), array('admin_id' => $this->getCrmStaffId()));
        return ($update_user) ? true : false;
    }

    function assign_participant($staffId)
    {
        $tbl_1 = TBL_PREFIX . 'member';
        $tbl_3 = TBL_PREFIX . 'crm_staff';
        $this->CI->db->select(
            array(
                $tbl_1 . '.id as ocs_id',
                $tbl_1 . '.status',
                "CONCAT(tbl_member.firstname,' ',tbl_member.lastname) AS name",
                "count(tbl_member.id) as count"
            )
        );
        $this->CI->db->from($tbl_3);
        $this->CI->db->join('tbl_member', 'tbl_crm_staff.admin_id = tbl_member.id', 'left');
        $this->CI->db->join('tbl_crm_participant', 'tbl_crm_participant.assigned_to = tbl_member.id', 'left');
        $this->CI->db->where($tbl_1 . '.archive=', "0");
        $this->CI->db->where($tbl_1 . '.id<>', $staffId);
        $this->CI->db->where($tbl_3 . '.status=', "1");
        $this->CI->db->group_by($tbl_1 . '.id');
        $data = $this->CI->db->get();
        $assigned_counts = array();
        foreach ($data->result_array() as $value) {
            $assigned_counts[$value['ocs_id']] =  $value['count'];
        }
        return $assigned_counts;
    }
    function update_disable_recruiter()
    {
        $data = array(
            'crm_staff_id' => $this->getCrmStaffId(),
            'disable_account' => $this->getDisableAccount(),
            'account_allocated' => $this->getAccountAllocated(),
            'account_allocated_to' => $this->getAccountAllocatedTo(),
            'relevant_note' => $this->getRelevantNote(),
        );

        $res = $this->CI->Basic_model->update_records('crm_staff_disable', $data, array('id' => $this->getId()));
        $staff_data = $this->getStaffDetails();
        foreach ($staff_data as $value) {
            if (!empty($value->changed_id)) {
                $data = array(
                    'crm_staff_id' => $this->getCrmStaffId(),
                    'disable_account' => $this->getDisableAccount(),
                    'account_allocated' => $this->getAccountAllocated(),
                    'account_allocated_to' => $value->changed_id,
                    'relevant_note' => $this->getRelevantNote(),
                );
                $this->CI->Basic_model->update_records('crm_staff_disable', $data, array('id' => $this->getId()));
            }
        }
        return $res;
    }
}
