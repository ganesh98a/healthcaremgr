<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use XeroPHP\Models\Accounting\History;

/**
 * Description of LineItemTransactionHistory
 *
 * @author user
 */
class LineItemTransactionHistory
{

    private $id;
    private $user_plan_line_items_id;
    private $line_item_fund_used;
    private $line_item_fund_used_type;
    private $line_item_use_id;
    private $status;
    private $archive;
    private $created = DATE_TIME;
    private $block_action_date;
    private $used_action_date;
    private $relased_action_date;
    private $shiftId;
    private $allData = [];

    function __construct()
    {
        $this->CI = &get_instance();
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getId()
    {
        return $this->id;
    }

    function setUser_plan_line_items_id($user_plan_line_items_id)
    {
        $this->user_plan_line_items_id = $user_plan_line_items_id;
    }

    function getUser_plan_line_items_id()
    {
        return $this->user_plan_line_items_id;
    }

    function setLine_item_fund_used($line_item_fund_used)
    {
        $this->line_item_fund_used = $line_item_fund_used;
    }

    function getLine_item_fund_used()
    {
        return $this->line_item_fund_used;
    }

    function setLine_item_fund_used_type($line_item_fund_used_type)
    {
        $this->line_item_fund_used_type = $line_item_fund_used_type;
    }

    function getLine_item_fund_used_type()
    {
        return $this->line_item_fund_used_type;
    }

    function setLine_item_use_id($line_item_use_id)
    {
        $this->line_item_use_id = $line_item_use_id;
    }

    function getLine_item_use_id()
    {
        return $this->line_item_use_id;
    }

    function setStatus($status)
    {
        $this->status = $status;
    }

    function getStatus()
    {
        return $this->status;
    }

    function setArchive($archive)
    {
        $this->archive = $archive;
    }

    function getArchive()
    {
        return $this->archive;
    }

    function setCreated($created)
    {
        $this->created = $created;
    }

    function getCreated()
    {
        return $this->created;
    }

    function setBlock_action_date($block_action_date)
    {
        $this->block_action_date = $block_action_date;
    }

    function getBlock_action_date()
    {
        return $this->block_action_date;
    }

    function setUsed_action_date($used_action_date)
    {
        $this->used_action_date = $used_action_date;
    }

    function getUsed_action_date()
    {
        return $this->used_action_date;
    }

    function setRelased_action_date($relased_action_date)
    {
        $this->relased_action_date = $relased_action_date;
    }

    function getRelased_action_date()
    {
        return $this->relased_action_date;
    }

    function setShiftId($shiftId)
    {
        $this->shiftId = $shiftId;
    }

    function getShiftId()
    {
        return $this->shiftId;
    }


    function getAllData()
    {
        return $this->allData;
    }

    private function setAllData($allData = [])
    {
        $this->allData = $allData;
    }

    private function setSingleDataInAllData($allData = [])
    {
        $this->allData[] = $allData;
    }

    function create_history()
    {
        $history_data = [
            'user_plan_line_items_id' => $this->user_plan_line_items_id,
            'line_item_fund_used' => $this->line_item_fund_used,
            'line_item_fund_used_type' => $this->line_item_fund_used_type,
            'line_item_use_id' => $this->line_item_use_id,
            'status' => $this->status,
            'archive' => $this->archive,
            'created' => $this->created,
            'block_action_date' => DATE_TIME,
        ];

        $this->CI->db->insert('tbl_user_plan_line_item_history', $history_data);
        return $this->CI->db->insert_id();
    }

    function update_fund_blocked()
    {
        $query = $this->CI->db->query("UPDATE tbl_user_plan_line_items as upli                 
        SET  
        upli.fund_used = (SELECT sum(uplih.line_item_fund_used) FROM tbl_user_plan_line_item_history uplih WHERE  upli.id = uplih.user_plan_line_items_id AND uplih.status IN ('1','0') AND uplih.archive=0 ),
        upli.fund_remaining = (upli.total_funding-COALESCE((SELECT sum(uplih.line_item_fund_used) FROM tbl_user_plan_line_item_history uplih WHERE  upli.id = uplih.user_plan_line_items_id AND uplih.status IN ('1','0') AND uplih.archive=0 ),0))");

        return true;
    }

    function relese_fund_by_shiftId()
    {
        $this->CI->load->model("finance/Finance_ammount_calculation_model");

        $this->CI->Finance_ammount_calculation_model->release_archive_shift_fund($this->shiftId);
        $this->update_fund_blocked();
    }

    function block_to_used_status_update_shift_fund_by_shift_id()
    {
        $this->CI->load->model("finance/Finance_ammount_calculation_model");

        $this->CI->Finance_ammount_calculation_model->block_to_used_status_update_shift_fund_by_shift_id($this->shiftId);
        $this->update_fund_blocked();
    }

    function save_temp_history()
    {
        $history_data = [
            'user_plan_line_items_id' => $this->user_plan_line_items_id,
            'line_item_fund_used' => $this->line_item_fund_used,
            'line_item_fund_used_type' => $this->line_item_fund_used_type,
            'line_item_use_id' => $this->line_item_use_id,
            'status' => $this->status,
            'archive' => $this->archive,
            'created' => $this->created
        ];

        $blockActionDate = $this->getBlock_action_date();
        if ($blockActionDate != '' && validateDateWithFormat($blockActionDate, DATE_TIME_FORMAT)) {
            $history_data['block_action_date']  = $blockActionDate;
        } else {
            $history_data['block_action_date']  = DATE_TIME;
        }

        $usedActionDate = $this->getUsed_action_date();
        if ($usedActionDate != '') {
            $history_data['used_action_date']  = validateDateWithFormat($usedActionDate, DATE_TIME_FORMAT) ? $usedActionDate : '0000-00-00 00:00:00';
        } else {
            $history_data['used_action_date']  = '0000-00-00 00:00:00';
        }

        $relasedActionDate = $this->getRelased_action_date();

        if ($relasedActionDate != '') {
            $history_data['relased_action_date']  = validateDateWithFormat($relasedActionDate, DATE_TIME_FORMAT) ? $relasedActionDate : '0000-00-00 00:00:00';
        } else {
            $history_data['relased_action_date']  = '0000-00-00 00:00:00';
        }
        $this->setSingleDataInAllData($history_data);
        $this->clear_data_input();
    }

    function save_all()
    {
        $history_data = $this->getAllData();
        if (!empty($history_data)) {
            $this->CI->db->insert_batch('tbl_user_plan_line_item_history', $history_data);
            $this->clear_all_input();
            return true;
        } else {
            return false;
        }
    }

    private function clear_all_input()
    {
        $this->allData = [];
        $this->clear_data_input();
    }

    private function clear_data_input()
    {
        $this->id = '';
        $this->user_plan_line_items_id = '';
        $this->line_item_fund_used = '';
        $this->line_item_fund_used_type = '';
        $this->line_item_use_id = '';
        $this->status = '';
        $this->archive = '';
        $this->created = DATE_TIME;
        $this->block_action_date = '';
        $this->used_action_date = '';
        $this->relased_action_date = '';
    }
}
