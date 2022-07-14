<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for interacting with `tbl_service_agreement` table
 */
class ServiceAgreement_model extends Basic_Model
{
    var $document_type = [
        "1" => "Consent",
        "2" => "NDIS Service Agreement",
        "3" => "Support Coordination Service Agreement",
        "4" => "Private Travel Agreement"
    ];

    var $sa_types = [
        "2" => "NDIS Service Agreement",
        "3" => "Support Coordination Service Agreement",
        "4" => "Private Travel Agreement"
    ];

    public function get_service_agreement_list($reqData)
    {
        // DONT COPY/PASTE EXISTING SUBQUERIES
        // They are infested with SQL injection vulnerabilities!

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
        $viewedLog = new ViewedLog();
        // get entity type value
        $entity_type = $viewedLog->getEntityTypeValue('service_agreement');

        // searchable columns
        $src_columns = [
            'sa.service_agreement_id',
            'o.topic',
            'status_label',
            'account_name',
            'owner_fullname',
            "created_by_fullname",
        ];

        $queryHaving = null;
        if (isset($filter->search) && $filter->search != '') {

            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $available_column = array("id", "created");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column) ) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'DESC' : 'ASC';
                if ($orderBy == 'created') {
                    $orderBy = "sa.created";
                }
            }
        } else {
            $orderBy = 'sa.id';
            $direction = 'DESC';
        }

        if (isset($filter) == true && $filter != '' && !empty($filter)) {
            if (property_exists($filter, 'filter_status')) {
                if ($filter->filter_status !== "all") {
                    $this->db->where('sa.status', $filter->filter_status);
                }
            }
        }

        $select_column = [
            "sa.*",
            'o.topic as opportunity_topic',
            "(
                CASE
                    WHEN sa.status = 0 THEN 'Draft'
                    WHEN sa.status = 1 THEN 'Issued'
                    WHEN sa.status = 2 THEN 'Approved'
                    WHEN sa.status = 3 THEN 'Inactive'
                    WHEN sa.status = 4 THEN 'Declined'
                    WHEN sa.status = 5 THEN 'Active'
                END
            ) as status_label",
            "(
                CASE
                    WHEN sa.account_type = 1 THEN (
                        SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                        FROM tbl_person AS person
                        WHERE person.id = sa.account
                        LIMIT 1
                    )
                    WHEN sa.account_type = 2 THEN (
                        SELECT org.name
                        FROM tbl_organisation AS org
                        WHERE org.id = sa.account
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_name",
            "CONCAT_WS(' ', m.firstname, m.lastname) AS owner_fullname",
            "CONCAT_WS(' ', creator.firstname, creator.lastname) AS created_by_fullname"
        ];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_service_agreement sa');
        $this->db->join('tbl_opportunity AS o', 'sa.opportunity_id = o.id', 'LEFT');
        $this->db->join('tbl_member AS m', 'sa.owner = m.uuid', 'INNER');
        $this->db->join('tbl_member AS creator', 'sa.created_by = creator.uuid', 'INNER');
        $this->db->where([
            'sa.archive' => 0,
            'o.archive' => 0,
        ]);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        if ($queryHaving) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get();

        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $results = $query->result();
        //get viewed by info
        $results = $this->getViewedBy($results, $entity_type);
        $return = [
            'count' => $dt_filtered_total, // number of pages
            'data' => $results,
            'status' => true,
            'total_item' => $total_item
        ];

        return $return;
    }

    /**
     * Mark service agreement as archived
     * @param int $id
     * @return array
     */
    public function archive_service_agreement($id)
    {
        $service_agreement = $this->db->get_where('tbl_service_agreement', ['id' => $id])->row_array();

        if (!$service_agreement) {
            return ['status' => false, 'error' => 'This service agreement does not exist anymore'];
        }

        if ($service_agreement['archive'] == 1) {
            return ['status' => false, 'error' => 'This service agreement was already been archived'];
        }

        $isSuccess = $this->db->update('tbl_service_agreement', ['archive' => 1], ['id' => $id]);

        if (!$isSuccess) {
            return [
                'status' => false,
                'msg' => 'Something went wrong while marking this service agreement.'
            ];
        }

        return [
            'status' => true,
            'code' => $service_agreement['service_agreement_id'] ?? 'unknown ID',
            'msg' => 'Service agreement successfully archived'
        ];
    }


    /**
     * Find service agreement by ID
     * @param int $id
     * @return array
     */
    public function get_service_agreement_details($id, $type = NULL)
    {         
     $this->load->model('sales/Service_booking_model');
        $query = $this->db->get_where('tbl_service_agreement', [
            'archive' => 0,
            'id' => $id,
        ]);

        $result = $query->row_array();
        if (!$result) {
            return [
                'status' => false,
                'data' => null,
            ];
        }

        $result['status_label'] = $this->determine_status_label($result['status']);
        $result['owner_details'] = $this->find_member_by_id($result['owner']);
        $result['signed_by_details'] = $this->find_member_by_id($result['signed_by']);
        // To avoid double queries, let's reuse the 'account_details' contents for account_person/account_organisation
        $result['account_person'] = null;
        $result['account_organisation'] = null;
        $result['account_details'] = $this->determine_account_details($result['account'], $result['account_type']);
        if ($result['account_type'] == 1) {
            $result['account_person'] = $result['account_details'];
        } else if ($result['account_type'] == 2) {
            $result['account_organisation'] = $result['account_details'];
        }

        $result['goals'] = $this->db->get_where('tbl_service_agreement_goal', ['service_agreement_id' => $result['id'], 'archive' => 0])->result_array();

        $result['opportunity'] = $this->db->get_where('tbl_opportunity', ['archive' => 0, 'id' => $result['opportunity_id']])->row_array();
        // Get added line items by service agreement id
        $result['service_agreement_items'] = $items = $this->get_service_agreement_items_detail($id);        
        $result['service_agreement_payments'] = $this->service_agreement_payments($id);
        $result['service_docusign_datas'] = $this->service_docusign_datas($id, $type);
        $result['oppunity_decisionmaker_contacts'] = json_encode($this->oppunity_decisionmaker_contacts($result['opportunity_id']));
        $result['service_booking_list']=$this->Service_booking_model->get_service_booking_list($id);
        $SAAdditionalFund = $this->get_sa_additional_fund($id);      
        $additionsPayment = 0;
        if (!empty($SAAdditionalFund)) {
            $additionsPayment = array_sum(array_column($SAAdditionalFund, 'additional_price'));
        }
        
        $total = 0;
        // calculate total amount
        if (!empty($items)) {
            foreach ($items as $row) {
                if ($row->category_ref === "" || !$this->isParentAdded($items, $row)) {
                    $amount = $row->amount;
                    if ($amount === "") {
                        $amount = 0;
                    }
                    $amount = $amount >=0 ? $amount : $row->qty * $row->upper_price_limit;
                    $total += $amount;
                }
            }
        }

        $result['service_agreement_items_total'] = $total+$additionsPayment;

        return [
            'status' => true,
            'data' => $result
        ];
    }

    public function service_agreement_payments($id)
    {
        $result = $this->db
            ->where(['service_agreement_id' => $id])
            ->get('tbl_sa_payments')
            ->row_array();

        // Get contact name as label and id as value
        if (!empty($result) && $result['self_managed_contact_id'] && $result['self_managed_contact_id'] != null && $result['self_managed_contact_id'] != '') {
            $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value']);
            $this->db->from(TBL_PREFIX . 'person as p');
            $this->db->where(['p.archive' => 0, 'p.id' => $result['self_managed_contact_id']]);
            $query = $this->db->get();
            $contact_row = $query->num_rows() > 0 ? $query->row_array() : [];
            $result['self_managed_contact'] = $contact_row;
        }

        return $result;
    }
    protected function find_member_by_id($member_id)
    {
        return $this->db
            ->where(['archive' => 0, 'uuid' => $member_id])
            ->select(['firstname', 'lastname', 'id'])
            ->get('tbl_member')
            ->row_array();
    }

    protected function determine_status_label($status)
    {
        $status_labels = [
            '0' => 'Draft',
            '1' => 'Issued',
            '2' => 'Approved',
            '3' => 'Inactive',
            '4' => 'Declined',
            '5' => 'Active',
        ];

        if (array_key_exists($status, $status_labels)) {
            return $status_labels[$status];
        }

        return "Unknown";
    }


    protected function determine_account_person($account_id)
    {
        $query = $this->db->get_where('tbl_person', [
            'archive' => 0,
            'id' => $account_id,
        ]);

        $result = $query->row_array();
        return $result;
    }

    protected function determine_account_organisation($account_id)
    {
        $query = $this->db->get_where('tbl_organisation', [
            'archive' => 0,
            'id' => $account_id,
        ]);

        $result = $query->row_array();

        // exclude sensitive info
        if ($result) {
            unset($result['password']);
            unset($result['username']);
            unset($result['password_reset_token']);
        }

        return $result;
    }

    protected function determine_account_details($account_id, $account_type)
    {
        if ($account_type == 1) {
            return $this->determine_account_person($account_id);
        } elseif ($account_type == 2) {
            return $this->determine_account_organisation($account_id);
        } else {
            return null;
        }
    }


    /*
     * its use for update service agreement status
     *
     * @params $reqData, $adminId
     * $reqData - reqdata of requested at front-end
     * $adminId action admin
     *
     * return type boolean
     */
    function update_status_service_agreement($reqData, $adminId)
    {
        require_once APPPATH . 'Classes/sales/SalesInactiveAndCancelledReason.php';
        $reqData = (object) $reqData;

        // check backword admin permission
        $check_res = $this->check_service_agreement_status_going_to_back_then_check_admin_permission($reqData, $adminId);

        if ($check_res["status"]) {
            $formerState = $this->db->from(TBL_PREFIX . 'service_agreement')
                ->select('*')
                ->where(['id' => $reqData->service_agreement_id])
                ->get()->result();
            if (!empty($formerState)) {
                $formerState = $formerState[0];
            }
            if ($reqData->status == 4) {
                $objRes = new SalesInactiveAndCancelledReason();

                $objRes->setEntity_id($reqData->service_agreement_id);
                $objRes->setEntity_type(1);
                $objRes->setReason_id($reqData->reason_drop);
                $objRes->setReason_note($reqData->reason_note ?? "");
                $objRes->setCreated_by($adminId);

                $objRes->createReason();
            }

            $where = ["id" => $reqData->service_agreement_id];
            $data = ["status" => $reqData->status, "updated" => DATE_TIME];

            $this->basic_model->update_records("service_agreement", $data, $where);
            if (!empty($formerState) && isset($reqData->status) && $reqData->status != $formerState->status) {
                $this->load->model('Opportunity_model');
                $bSuccess = $this->db->insert(
                    TBL_PREFIX . 'service_agreement_history',
                    [
                        'service_agreement_id' => $reqData->service_agreement_id,
                        'created_by' => $adminId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                );

                if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());
                $history_id = $this->db->insert_id();
                $this->Opportunity_model->create_agreement_field_history_entry($history_id, $reqData->service_agreement_id, 'status', $reqData->status, $formerState->status);

                $service_agreement_id = $reqData->service_agreement_id;
                # Async API used to update the shift service booking warning .
                $this->load->library('Asynclibrary');
                $url = base_url()."schedule/NdisErrorFix/update_shift_ndis_warning";
                $param = array('service_agreement_id' => $service_agreement_id, 'update_sa_and_li' => true, 'adminId' => $adminId);
                $param['requestData'] = $param;
                $this->asynclibrary->do_in_background($url, $param);
            }
            $response = ['status' => true, 'msg' => 'Status updated successfully.'];
        } else {
            $response = $check_res;
        }

        # Update participant as inactive
        $inactive_status = [ 3, 4 ];
        if (isset($reqData->status) == true && in_array($reqData->status, $inactive_status) == true) {
            $status = 0; // in active
            $this->update_participant_as_inactive($reqData->service_agreement_id, $adminId, $status);
        }

        return $response;
    }

    /**
     * Update participant as inactive if service agreement masked as inactive|declined
     * @param {int} $service_agreement_id
     * @param {int} $adminId
     * @param {int} $status
     */
    function update_participant_as_inactive($service_agreement_id, $adminId, $status) {
        if ($service_agreement_id != '' && $service_agreement_id != 'null') {
            $service_agreement = $this->basic_model->get_row("service_agreement", ["participant_id"], ["id" => $service_agreement_id]);
            $participant_id = $service_agreement->participant_id ?? 0;

            # Update participant if id not empty
            if ($participant_id != 0 && $participant_id != '') {
                $upd_data["updated_at"] = DATE_TIME;
                $upd_data["updated_by"] = $adminId;
                $upd_data["active"] = $status;
                $result = $this->basic_model->update_records("participants_master", $upd_data, ["id" => $participant_id]);
            }
        }
    }

    function check_service_agreement_status_going_to_back_then_check_admin_permission($reqData, $adminId)
    {
        $opportunity = $this->basic_model->get_row("service_agreement", ["status"], ["id" => $reqData->service_agreement_id]);
        $current_status = $opportunity->status ?? 0;

        // then we have to check admin permission
        // its should be crm admin
        //
        // access_crm_admin
        if ((int)$current_status > (int)$reqData->status) {
            require_once APPPATH . 'Classes/admin/permission.php';

            $obj_permission = new classPermission\Permission();
            $result = $obj_permission->check_permission($adminId, "access_crm_admin");

            if (!$result) {
                return array('status' => false, 'error' => "Not have permission unset status of service agreement");
            }
        }

        return array('status' => true);
    }

    function get_declined_reason_of_nots($entity_id, $entity_type)
    {
        $res = $this->basic_model->get_row("sales_inactive_and_cancelled_reason", ["id", "reason_id", "reason_note"], ["archive" => 0, "entity_id" => $entity_id, "entity_type" => $entity_type]);

        if (!empty($res)) {
            $this->db->select(["r.display_name"]);
            $this->db->from(TBL_PREFIX . 'references as r');
            $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'declined_reason_service_agreement' AND rdt.archive = 0", "INNER");
            $this->db->where("r.id", $res->reason_id);
            $res->reason_label = $this->db->get()->row("display_name");
        }

        return $res;
    }

    /*
     * Get all line items associate with sevice agreement
     * param {int} service_agreement_id
     * return - array
     */
    public function get_service_agreement_items_detail($service_agreement_id)
    {
        $this->db->from(TBL_PREFIX . 'service_agreement_items as sai');
        $this->db->select(array('sai.id as incr_id_service_agreement_items', 'sai.line_item_id', 'sai.amount', 'sai.qty', 'fli.line_item_name', 'fli.line_item_number', 'fli.category_ref', 'fsc.name as support_cat', 'sai.price  AS rate', 'sai.line_item_price_id'));
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
        $this->db->join("tbl_finance_line_item as fli", "fli.id = sai.line_item_id", "inner");
        $this->db->join("tbl_finance_support_category as fsc", "fsc.id = fli.support_category AND fsc.archive=0", "inner");
        $this->db->where(array('sai.archive' => 0, 'sai.service_agreement_id' => $service_agreement_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $item_ary = $query->result();

        $sorted = [];
        $parentItem = [];
        $childItem = [];
        foreach ($item_ary as $li) {
            if (empty($li->category_ref)) { // is a cat
                array_push($parentItem, $li);
            } else {
                $childItem[$li->category_ref][] = $li;
            }
        }

        foreach ($parentItem as $item) {
            array_push($sorted, $item);
            if (isset($childItem[$item->line_item_number])) { // is a cat
                $temp_child = [];
                $temp_child = $childItem[$item->line_item_number];
                $sorted = array_merge($sorted, $temp_child);
                unset($childItem[$item->line_item_number]);
            }
        }

        if (!empty($childItem)) {
            foreach ($childItem as $item) {
                $sorted = array_merge($sorted, $item);
            }
        }

        return $sorted;
    }

    /*
     * Get all active line item list
     */
    function get_finance_active_line_item_listing($reqData)
    {
        # Get service agreement data
        $SAData = [];
        $current_date = (int) strtotime(date('Y-m-d'));
        $current_date_format = date('Y-m-d');
        
        if (!empty($reqData->data->service_agreement_id)) {
            $SAId = $reqData->data->service_agreement_id;
            $SAData = $this->get_service_agreement_data_by_id($SAId);
        }

        $src_columns = array('fli.line_item_number', 'fli.category_ref', 'fli.line_item_name');
        $this->db->reset_query();
        if (!empty($reqData->data->srch_box)) {
            $srch_val = $reqData->data->srch_box;
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $srch_val);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $srch_val);
                }
            }
            $this->db->group_end();
        }
        
        $select_column = array("fli.id", 'fli.line_item_number', 'fli.line_item_name', 'fli.category_ref', 'tflip.start_date', "tflip.end_date", 'tflip.upper_price_limit', "fft.name as funding_type", "fm.name as measure_by", "fsrg.name as support_registration_group", "fli.price_control", "tflip.id as line_item_price_id");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);

        $this->db->select("true as qty_editable", false);
        $this->db->select("true as amount_editable", false);
        $this->db->select("1 as qty", false);
        $this->db->select("'' as amount", false);

        $this->db->select("case when fli.support_type!=0 THEN
            (SELECT fst.type as support_type FROM tbl_finance_support_type as fst where fst.id = fli.support_type) ELSE '' END as support_type", false);

        $this->db->select("case when fli.support_purpose!=0 THEN
        (SELECT fsp.purpose as support_purpose FROM tbl_finance_support_purpose as fsp where fsp.id = fli.support_purpose) ELSE '' END as support_purpose", false);

        $this->db->select("case when fli.support_category!=0 THEN
            (SELECT fsc.name as support_category FROM tbl_finance_support_category as fsc where fsc.id = fli.support_category) ELSE '' END as support_category", false);

        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_funding_type as fft', 'fft.id = fli.funding_type', 'inner');
        $this->db->join('tbl_finance_measure as fm', 'fm.id = fli.units', 'left');
        $this->db->join('tbl_finance_support_registration_group as fsrg', 'fsrg.id = fli.support_registration_group', 'inner');
        $this->db->join('tbl_finance_line_item_price as tflip', "tflip.line_item_id = fli.id  AND
                (STR_TO_DATE('{$current_date_format}', '%Y-%m-%d') BETWEEN DATE_FORMAT(tflip.start_date, '%Y-%m-%d') AND DATE_FORMAT(tflip.end_date, '%Y-%m-%d'))", 'LEFT', false);
        $this->db->where("( fli.category_ref != '' AND fli.category_ref IS NOT NULL )");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $result = $query->result();

        $selected_line_item_ary = [];
        if (!empty($reqData->data->service_agreement_id)) {
            $selected_line_item = $this->get_service_agreement_items($reqData->data->service_agreement_id);
            if (!empty($selected_line_item)) {
                $selected_line_item_ary = pos_index_change_array_data($selected_line_item, 'line_item_id');
            }
        }

        # load opportunity model
        $this->load->model('Opportunity_model');

        $childItem = [];
        $parentItemSearch = [];
        if (!empty($result)) {
            foreach ($result as $k => $val) {
                $start_date = (int) strtotime(DateFormate($val->start_date, "Y-m-d"));
                $end_date = (int) strtotime(DateFormate($val->end_date, "Y-m-d"));
                $val->is_old_price = false;
                if (!$val->line_item_price_id || !isset($val->line_item_price_id) || $val->line_item_price_id == '') {
                    $line_item_id = $val->id;
                    $getLeastRate = $this->Opportunity_model->get_line_item_least_rate($line_item_id, $select_column, $current_date_format);
                    
                    if (!empty($getLeastRate) && !empty($getLeastRate['upper_price_limit'])) {
                        $val->upper_price_limit = $getLeastRate['upper_price_limit'];
                        $val->is_old_price = true;
                    }
                }

                if (!empty($selected_line_item_ary) && array_key_exists($val->id, $selected_line_item_ary)) {
                    $val->selected = true;
                    $val->qty = $selected_line_item_ary[$val->id]['qty'];
                    $val->amount = $selected_line_item_ary[$val->id]['amount'];
                    $val->incr_id_service_agreement_items = $selected_line_item_ary[$val->id]['incr_id_service_agreement_items'];
                } else {
                    $val->selected = false;
                    $val->qty = "";
                    $val->amount = '';
                    $val->incr_id_service_agreement_items = 0;
                }

                if ($start_date <= $current_date && $current_date <= $end_date) {
                    $val->status = "1"; //1 for active
                } elseif ($start_date > $current_date) {
                    $val->status = "2"; //2 inactive
                } else {
                    $val->status = "3"; //3 archive
                }
                if (!empty($val->category_ref)) {
                    $category_ref = trim($val->category_ref);
                    $childItem[$category_ref][] = $val;
                    $parentItemSearch[] = $val->category_ref;
                } else {
                    $childItem[] = $val;
                }
            }
        }

        # praent Item
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
        $this->db->select("true as qty_editable", false);
        $this->db->select("true as amount_editable", false);
        $this->db->select("1 as qty", false);
        $this->db->select("'' as amount", false);
        $this->db->select("case when fli.support_type!=0 THEN
            (SELECT fst.type as support_type FROM tbl_finance_support_type as fst where fst.id = fli.support_type) ELSE '' END as support_type", false);
        $this->db->select("case when fli.support_purpose!=0 THEN
        (SELECT fsp.purpose as support_purpose FROM tbl_finance_support_purpose as fsp where fsp.id = fli.support_purpose) ELSE '' END as support_purpose", false);
        $this->db->select("case when fli.support_category!=0 THEN
            (SELECT fsc.name as support_category FROM tbl_finance_support_category as fsc where fsc.id = fli.support_category) ELSE '' END as support_category", false);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_finance_line_item_price as tflip', 'tflip.line_item_id = fli.id', 'LEFT');
        $this->db->join('tbl_funding_type as fft', 'fft.id = fli.funding_type', 'inner');
        $this->db->join('tbl_finance_measure as fm', 'fm.id = fli.units', 'left');
        $this->db->join('tbl_finance_support_registration_group as fsrg', 'fsrg.id = fli.support_registration_group', 'left');
        $this->db->group_start();
        $this->db->where("fli.category_ref = '' OR fli.category_ref IS NULL");
        $this->db->group_end();
        if (!empty($reqData->data->srch_box) && !empty($parentItemSearch)) {
            $this->db->where_in('fli.line_item_number', array_values($parentItemSearch));
        }
        $this->db->order_by("fli.line_item_number");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $parentItem = $query->result();

        $sorted = [];
        foreach($parentItem as $p_key => $parent) {
            $start_date = (int) strtotime(DateFormate($parent->start_date, "Y-m-d"));
            $end_date = (int) strtotime(DateFormate($parent->end_date, "Y-m-d"));

            if (!empty($selected_line_item_ary) && array_key_exists($parent->id, $selected_line_item_ary)) {
                $parent->selected = true;
                $parent->qty = $selected_line_item_ary[$parent->id]['qty'];
                $parent->amount = $selected_line_item_ary[$parent->id]['amount'];
                $parent->incr_id_service_agreement_items = $selected_line_item_ary[$parent->id]['incr_id_service_agreement_items'];
            } else {
                $parent->selected = false;
                $parent->qty = "";
                $parent->amount = '';
                $parent->incr_id_service_agreement_items = 0;
            }

            if ($start_date <= $current_date && $current_date <= $end_date) {
                $parent->status = "1"; //1 for active
            } elseif ($start_date > $current_date) {
                $parent->status = "2"; //2 inactive
            } else {
                $parent->status = "3"; //3 archive
            }

            $category = trim($parent->line_item_number);
            if (isset($childItem[$category]) && !empty($childItem[$category])) {
                $temp = [];
                $temp = $childItem[$category];
                array_push($sorted, $parent);
                $sorted = array_values($sorted);
                $sorted = array_merge($sorted, $temp);
                unset($childItem[$category]);
            } else {
                $sorted[$category] = $parent;
            }
        }
        
        if (!empty($childItem) > 0) {
            // $sorted = array_merge($sorted, $childItem);
        }
        $sorted = array_values($sorted);

        $return = array('data' => $sorted, 'status' => true);
        return $return;
    }


    /*
     * Get service agreement line item by service agreement id
     * param {int}
     */
    public function get_service_agreement_items($service_agreement_id)
    {
        $this->db->from(TBL_PREFIX . 'service_agreement_items as oi');
        $this->db->select(array('oi.id as incr_id_service_agreement_items', 'oi.line_item_id', 'oi.amount', 'oi.qty'));
        $this->db->where(array('oi.archive' => 0, 'oi.service_agreement_id' => $service_agreement_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $item_ary = $query->result();
        return $item_ary;
    }

    /*
     * Save service agreement line item
     * param {array} data
     * param {int} adminId
     */
    function save_service_agreement_item($data, $adminId, $reqData = null)
    {
        if (!empty($data)) {
            $softDelete = $tempDelete = $insData = $updateData = $tempInsert = 
                $tempUpdate = $additional_payment = $addi_payment  = [];
            $before_update_amount = 0;
            $after_update_amount = 0;
            $before_update_item_sa_total_amount = 0;
            
            if (!empty($data['service_agreement_id'])) {
                $service_detail = $this->get_service_agreement_details($data['service_agreement_id'], true);
                
                $before_update_amount =  !empty($service_detail['data']['grand_total']) ? 
                $service_detail['data']['grand_total'] : 0;

                $before_update_item_sa_total_amount = !empty($service_detail['data']['line_item_sa_total']) ? 
                $service_detail['data']['line_item_total'] : 0;
            }
            foreach ($data as $item) {
                
                if (isset($item['selected']) && $item['selected'] == true) {
                    if ($item['incr_id_service_agreement_items'] == 0) {
                        $tempInsert = [
                            'service_agreement_id' => $data['service_agreement_id'] ?? 0,
                            'line_item_id' => $item['id'] ?? 0,
                            'line_item_price_id' => $item['line_item_price_id'] ?? 0,
                            'price' => $item['upper_price_limit'] ?? 0,
                            'qty' => $item['qty'] ?? 0,
                            'amount' => $item['amount'] ?? 0,
                            'created_by' => $adminId,
                        ]; 
                       
                        $insData[] = $tempInsert;
                    }
                    if ($item['incr_id_service_agreement_items'] && $item['incr_id_service_agreement_items'] > 0) {
                        $tempUpdate = [
                            'line_item_price_id' => $item['line_item_price_id'] ?? 0,
                            'price' => $item['upper_price_limit'] ?? 0,
                            'qty' => $item['qty'] ?? 0,
                            'amount' => $item['amount'] ?? 0,
                            'updated_by' => $adminId,
                            'id' => $item['incr_id_service_agreement_items']

                        ];
                        $updateData[] = $tempUpdate;
                    }
                } else {
                    if (isset($item['incr_id_service_agreement_items']) && $item['incr_id_service_agreement_items'] > 0) {
                        $tempDelete = [
                            'archive' => 1,
                            'id' => $item['incr_id_service_agreement_items']
                        ];
                        $softDelete[] = $tempDelete;
                    }
                }
            }
            if (!empty($data['additional_rows'])) {
                foreach($data['additional_rows'] as $additem) {
                    $addi_payment = [
                        'service_agreement_id' => $data['service_agreement_id'] ?? 0,
                        'additional_title' => $additem['additional_title'] ?? '',
                        'additional_price' => $additem['additional_price'] ?? 0,
                        'created_by' => $adminId,
                        'created' => DATE_TIME
                    ];
                    $additional_payment[] = $addi_payment;
                }
                
            }
            
            if (!empty($insData)) { 
                $this->basic_model->insert_records('service_agreement_items', $insData, true);                
            }
            if (!empty($updateData)) {
                $this->basic_model->insert_update_batch('update', 'service_agreement_items', $updateData, 'id');
            }
            if (!empty($softDelete)) {
                $this->basic_model->insert_update_batch('update', 'service_agreement_items', $softDelete, 'id');
            }
            
            //Archive the existing element               
            $this->basic_model->update_records('service_agreement_additional_fund', ['archive' => 1],
                ['service_agreement_id' => $data['service_agreement_id']]);

            if(!empty($additional_payment)) {
               
                
                $this->basic_model->insert_records('service_agreement_additional_fund', $additional_payment, true);
            }

            if (!empty($insData) || !empty($updateData) || !empty($softDelete)) {
                if (!empty($data['service_agreement_id'])) {
                     //Update the sa total and total values
                    $this->basic_model->update_records('service_agreement', 
                    ['line_item_sa_total' => $reqData->data->line_item_sa_total??0,
                    'grand_total' => $reqData->data->line_item_sa_total??0,
                    'line_item_total' => $reqData->data->line_item_total??0],
                    
                    ['id' => $data['service_agreement_id']]);

                    $after_update_amount = $reqData->data->line_item_total??0;
                   
                    if ($before_update_amount != $after_update_amount) {
                        $this->load->model('Opportunity_model');
                        $bSuccess = $this->db->insert(
                            TBL_PREFIX . 'service_agreement_history',
                            [
                                'service_agreement_id' => $data['service_agreement_id'],
                                'created_by' => $adminId,
                                'created_at' => date('Y-m-d H:i:s')
                            ]
                        );

                        if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());
                        $history_id = $this->db->insert_id();
                        $this->Opportunity_model->create_agreement_field_history_entry($history_id, $data['service_agreement_id'], 'grand_total', $after_update_amount, $before_update_amount);
                        
                        $this->Opportunity_model->create_agreement_field_history_entry($history_id, $data['service_agreement_id'], 'line_item_sa_total', $reqData->data->line_item_sa_total, $before_update_item_sa_total_amount);

                        $this->Opportunity_model->create_agreement_field_history_entry($history_id, $data['service_agreement_id'], 'line_item_total', $reqData->data->line_item_total, $before_update_amount);
                    }

                    $service_agreement_id = $data['service_agreement_id'];
                    # Async API used to update the shift ndis line item warning .
                    $this->load->library('Asynclibrary');
                    $url = base_url()."schedule/NdisErrorFix/update_shift_missing_ndis_line_item";
                    $param = array('service_agreement_id' => $service_agreement_id);
                    $param['requestData'] = $param;
                    $this->asynclibrary->do_in_background($url, $param);
                }
            }
        }
        return true;
    }

    public function save_sa_payment($data, $adminId, $ca_action)
    {

      
        if (!isset($data['service_agreement_id'])) {
            return '';
        }
       
        if ($data["managed_type"] == 1) {
            $data["service_booking_creator"] = $data["service_booking_creator"];
            $data["organisation_id"] = "";
            $data["organisation_contact_id"] = "";
            $data["organisation_select"] = "";
            $data["organisation_contact_select"] = "";
            $data["self_type_contact_name"] = "";
            $data["self_managed_contact_id"] = "";
        }
        if ($data["managed_type"] == 2) {
            $data["organisation_id"] = $data["organisation_id"];
            $data["organisation_contact_id"] = $data["organisation_contact_id"];
            $data["organisation_select"] = $data["organisation_select"];
            $data["organisation_contact_select"] = $data["organisation_contact_select"];
            $data["service_booking_creator"] = "";
            $data["self_type_contact_name"] = "";
            $data["self_managed_contact_id"] = "";
        }
        if ($data["managed_type"] == 3) {
            $data["organisation_id"] = "";
            $data["organisation_contact_id"] = "";
            $data["organisation_select"] = "";
            $data["organisation_contact_select"] = "";
            $data["service_booking_creator"] = "";
            $data["self_managed_contact_id"] = $data["self_managed_contact_id"];
            $data["self_type_contact_name"] = $data["self_type_contact_name"];
        }
        // Create order
        if ($ca_action == "create") {
            // echo '<pre>';print_r($data);die;
            $insData = [
                'service_agreement_id' => $data['service_agreement_id'],
                'managed_type' => $data["managed_type"],
                'service_booking_creator' => $data["service_booking_creator"],
                'organisation_id' => $data["organisation_id"],
                'organisation_contact_id' => $data["organisation_contact_id"],
                'organisation_select' => json_encode($data["organisation_select"]),
                'organisation_contact_select' => json_encode($data["organisation_contact_select"]),
                'self_managed_contact_id' => $data["self_managed_contact_id"],
                'self_type_contact_name' => $data["self_type_contact_name"],
                'created_by' => $adminId,
            ];
            //  echo '<pre>';print_r($insData);die;
            $sa_payment_id = $this->basic_model->insert_records('sa_payments', $insData);
        } else {
            $upData = [
                'managed_type' => $data["managed_type"],
                'service_booking_creator' => $data["service_booking_creator"],
                'organisation_id' => $data["organisation_id"],
                'organisation_contact_id' => $data["organisation_contact_id"],
                'organisation_select' => json_encode($data["organisation_select"]),
                'organisation_contact_select' => json_encode($data["organisation_contact_select"]),
                'self_type_contact_name' => $data["self_type_contact_name"],
                'self_managed_contact_id' => $data["self_managed_contact_id"],
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'updated_date' => DATE_TIME,
            ];
            // echo '<pre>';print_r($upData);die;

            $sa_payment_id = $this->basic_model->update_records('sa_payments', $upData, array('service_agreement_id' => $data['service_agreement_id']));
        }
      
        // echo '<pre>';print_r($sa_payment_id);die;
        return $sa_payment_id;
    }

    function save_line_items($items, $id, $adminId)
    {
        $records = [];
        foreach ($items as $item) {
            $record = new stdClass();
            $record->service_agreement_id = $id;
            $record->line_item_id = $item->line_item_id;
            $record->line_item_price_id = $item->line_item_price_id ?? NULL;
            $record->qty = $item->qty;
            $record->price = $item->rate ?? 0.0;
            $record->amount = $item->amount;
            $record->archive = 0;
            $record->created_by = $adminId;
            array_push($records, $record);
        }

        if (isset($records) == true && empty($records) == false) {
            $this->basic_model->insert_records('service_agreement_items', $records, true);
        }
    }

    /*
     * Save service agreement contract - docusign
     * @param {array} data
     * @param {int} adminId
     * @param {str} ca_action // determine the action is create or edit
     */
    public function save_add_newdocusign($data, $adminId, $ca_action)
    {
        if ($ca_action == "create") {
            // echo '<pre>';print_r($data);die;
            $insData = [
                'type' => $data['type'],
                'document_type' => $data['document_type'],
                'to' => $data["to"],
                'to_select' => json_encode($data["to_select"]),
                'account_id' => $data["account_id"]?? null,
                'account_type' => $data["account_type"]?? null,
                'related' => $data["related"],
                'service_agreement_id' => $data["service_agreement_id"]?? null,
                'signed_by' => $data["signed_by"],
                'created' => DATE_TIME,
                'created_by' => $adminId,
                'lead_id' => $data["lead_id"]?? null,
                "contract_id"=>$data["contract_id"]??null
            ];
            // echo '<pre>';print_r($insData);die;
            $sa_newdocsign_id = $this->basic_model->insert_records('service_agreement_attachment', $insData);

            // Email details
            if ($sa_newdocsign_id) {

                if (isset($data["cc_email"]) == true && empty($data["cc_email"]) == false) {
                    $cc_email = (array) ($data["cc_email"]);
                    $insData = [];
                    foreach($cc_email as $cc_index => $cc) {
                        $temp = [
                            'service_agreement_attachment_id' => $sa_newdocsign_id,
                            'subject' => $data['subject'],
                            'email_content' => $data['email_content'],
                            'cc_email_flag' => isset($data["cc_email_flag"]) ? $data["cc_email_flag"] : 0,
                            'cc_email' => $cc,
                            'created_at' => DATE_TIME,
                            'created_by' => $adminId,
                        ];
                        $insData[] = $temp;
                    }

                    $this->basic_model->insert_update_batch('insert', 'service_agreement_attachment_email', $insData);
                } else {
                    $insData = [
                        'service_agreement_attachment_id' => $sa_newdocsign_id,
                        'subject' => $data['subject'],
                        'email_content' => $data['email_content'],
                        'cc_email_flag' => isset($data["cc_email_flag"]) ? $data["cc_email_flag"] : 0,
                        'cc_email' => isset($data["cc_email"]) && !empty($data["cc_email"]) ? ($data["cc_email"]) : '',
                        'created_at' => DATE_TIME,
                        'created_by' => $adminId,
                    ];
                    $sa_email_id = $this->basic_model->insert_records('service_agreement_attachment_email', $insData);
                }
            }
        }
        return $sa_newdocsign_id;
    }

    /*
     * Get service agreement contract data by id
     * @param {int} id
     * return array
     */
    public function service_docusign_datas($id, $type = NULL, $filter = null)
    {
        $data = [];
        $base_url = 'mediaShow/SA';
        $base_s3_url = 'mediaShow/s3';
        $src_columns = array(
            "saa.*",
            "CONCAT_WS(' ', creator.firstname, creator.lastname) as created_by_fullname",
            "(
                CASE
                    WHEN saa.signed_status = 0 THEN 'Not Signed Yet'
                    WHEN saa.signed_status = 1 THEN 'Signed'
                END
            ) as contract_status",
            "(
                CASE
                    WHEN saa.account_type = 1 THEN (
                        SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                        FROM tbl_person AS person
                        WHERE person.id = saa.account_id
                        LIMIT 1
                    )
                    WHEN saa.account_type = 2 THEN (
                        SELECT org.name
                        FROM tbl_organisation AS org
                        WHERE org.id = saa.account_id
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_name",
            "CONCAT_WS(' ', person.firstname, person.lastname) as signed_by_name",
            "saa.signed_by as signed_by_id",
            "(
                CASE
                    WHEN saa.aws_uploaded_flag = 1 THEN
                    (CONCAT('".$base_s3_url."', '/', saa.id, '/', REPLACE(TO_BASE64(saa.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=true'))
                    ELSE
                    (CONCAT('".$base_url."', '/', saa.id, '?filename=', REPLACE(TO_BASE64(saa.signed_file), '=', '%3D%3D'), '&download_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=false'))
                END) as url",

            );

        $query = $this->db
            ->select(array(
                "saa.*",
                "CONCAT_WS(' ', creator.firstname, creator.lastname) AS created_by_fullname",
                "(
                    CASE
                        WHEN saa.signed_status = 0 THEN 'Not Signed Yet'
                        WHEN saa.signed_status = 1 THEN 'Signed'
                    END
                ) AS contract_status",
                "(
                    CASE
                        WHEN saa.account_type = 1 THEN (
                            SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                            FROM tbl_person AS person
                            WHERE person.id = saa.account_id
                            LIMIT 1
                        )
                        WHEN saa.account_type = 2 THEN (
                            SELECT org.name
                            FROM tbl_organisation AS org
                            WHERE org.id = saa.account_id
                            LIMIT 1
                        )
                        ELSE ''
                    END
                ) as account_name",
                "CONCAT_WS(' ', person.firstname, person.lastname) AS signed_by_name",
                "saa.signed_by AS signed_by_id",
                "(
                    CASE
                        WHEN saa.aws_uploaded_flag = 1 THEN
                        (CONCAT('".$base_s3_url."', '/', saa.id, '/', REPLACE(TO_BASE64(saa.file_path), '=', '%3D%3D'), '?ownload_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=true'))
                        ELSE
                        (CONCAT('".$base_url."', '/', saa.id, '?filename=', REPLACE(TO_BASE64(saa.signed_file), '=', '%3D%3D'), '&download_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=false'))
                    END

                ) AS url",

                ));
            
        $this->db->select($src_columns);
        $document_type_label = "(CASE ";
        foreach($this->document_type as $k => $v) {
            $document_type_label .= " WHEN saa.document_type = {$k} THEN '{$v}'";
        };
        $document_type_label .= "ELSE '' END) as document_type_label";
        $this->db->select($document_type_label);
        $this->db->join('tbl_member AS creator', 'saa.created_by = creator.uuid', 'LEFT');
        $this->db->join('tbl_person AS person', 'saa.signed_by = person.id', 'LEFT');
        if (is_array($id)) {
            $this->db->where_in('service_agreement_id', $id);
        } else {
            $this->db->where(['service_agreement_id' => $id]);
        }            
        
        if (!empty($filter->search)) {
            $status_str = '';
            if (strtolower($filter->search) === "signed") {
                $status_str = 1;
            }
            if (strtolower($filter->search) === "not signed") {
                $status_str = 0;
            }
            $this->db->group_start();
            $this->db->or_like('saa.related', $filter->search, 'both');
            if ($status_str !== '') {
                $this->db->or_like('saa.signed_status', $status_str, 'both');
            }
            $this->db->or_like(" CONCAT_WS(' ', person.firstname, person.lastname) ", $filter->search, 'both');
            $this->db->group_end();
        }
        if($type) {
            $this->db->where(['saa.type' => $type]);
        }
        $this->db->from('tbl_service_agreement_attachment as saa');
        $this->db->order_by('id','DESC');
        $query = $this->db->get();
        $s=$this->db->last_query();
        $result = $query->result();
        
        // save result if nom_rows not equal to zero
        if (!empty($result)) {
            $data = $result;
        }

        return $data;
    }

    /*
     * Get the list of contact associate with opportunity
     * @param {int} id
     * return array
     */
    public function oppunity_decisionmaker_contacts($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'sales_relation as oc');
        $this->db->select("case when oc.roll_id!=0 THEN
        (SELECT display_name as role FROM tbl_references as r where r.id=oc.roll_id AND r.archive=0 AND r.type=6) ELSE '' END as role", false);
        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS name", FALSE);
        $this->db->select(array('p.id as person_id'));
        $this->db->join("tbl_person as p", "p.id = oc.destination_data_id AND p.archive = 0", "inner");
        $this->db->where(array('oc.archive' => 0, 'oc.source_data_id' => $opportunity_id, 'oc.source_data_type' => 3, 'oc.destination_data_type' => 1));
        $query = $this->db->get()->result();
        $rows = array();
        if (!empty($query)) {
            foreach ($query as $val) {
                if ($val->role == 'Decision Maker') {
                    $rows[] = array('label' => $val->name, 'value' => $val->person_id);
                }
            }
        }
        return $rows;
    }

    /*
     * Get service agreement consent contract details by id
     * @param {int} service_agreement_attachment_id
     * @param {boolean} if attachment belongs to lead docusign consent form
     * return array
     */
    public function get_sa_attachment_details($service_agreement_attachment_id, $lead = false)
    {
        $saa_col_select = [
            "saa.id",
            "saa.type",
            "(
                CASE
                WHEN saa.type = 1 THEN 'Consent'
                WHEN saa.type = 2 THEN 'Service Agreement'
                END
            ) as contract_type",
            "saa.to",
            "(
                SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = saa.to
            ) AS to_name",
            "(
                SELECT pe.email
                FROM tbl_person AS person
                INNER JOIN tbl_person_email as pe ON pe.person_id = person.id
                WHERE person.id = saa.to and pe.primary_email = 1 AND pe.archive = 0
                LIMIT 1
            ) AS to_email",
            "saa.signed_by",
            "saa.account_id",
            "saa.account_type",
            "CONCAT_WS(' ', p.firstname, p.lastname) AS recipient_name",
            '(
                SELECT
                    email
                FROM tbl_person_email
                WHERE primary_email = 1 AND archive = 0 AND person_id = p.id limit 1
            ) as recipient_email',
            "(
                CASE
                    WHEN saa.account_type = 1 THEN (
                        SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                        FROM tbl_person AS person
                        WHERE person.id = saa.account_id
                        LIMIT 1
                    )
                    WHEN saa.account_type = 2 THEN (
                        SELECT org.name
                        FROM tbl_organisation AS org
                        WHERE org.id = saa.account_id
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_name",
            "saa.to_select"
        ];
        $where_col = [
            "saa.id" => $service_agreement_attachment_id
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $saa_col_select)), false);

        # document label
        $document_type_label = "(CASE ";
        foreach($this->document_type as $k => $v) {
            $document_type_label .= " WHEN saa.document_type = {$k} THEN '{$v}'";
        };
        $document_type_label .= "ELSE '' END) as document_type_label";
        $this->db->select($document_type_label);

        $this->db->from(TBL_PREFIX . 'service_agreement_attachment as saa');
        $this->db->join("tbl_person as p", "p.id = saa.signed_by AND p.archive = 0", "inner");
        if (empty($lead)) {
            $this->db->join("tbl_service_agreement as sa", "sa.id = saa.service_agreement_id", "inner");
        }
        $this->db->where($where_col);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * Get service agreement contract details by id
     * @param {int} service_agreement_attachment_id
     * return array
     */
    public function get_sa_contract_details($service_agreement_attachment_id)
    {
        $saa_col_select = [
            "saa.id",
            "saa.envelope_id",
            "saa.service_agreement_id",
            "saa.type",
            "(
                CASE
                WHEN saa.type = 1 THEN 'Consent'
                WHEN saa.type = 2 THEN 'Service Agreement'
                END
            ) as contract_type",
            "saa.to",
            "(
                SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = saa.to
            ) AS to_name",
            "(
                SELECT pe.email
                FROM tbl_person AS person
                INNER JOIN tbl_person_email as pe ON pe.person_id = person.id
                WHERE person.id = saa.to and pe.primary_email = 1 AND pe.archive = 0
                LIMIT 1
            ) AS to_email",
            "saa.signed_by",
            "saa.account_id",
            "saa.account_type",
            "CONCAT_WS(' ', p.firstname, p.lastname) AS recipient_name",
            '(
                SELECT
                    email
                FROM tbl_person_email
                WHERE primary_email = 1 AND archive = 0 AND person_id = p.id LIMIT 1
            ) as recipient_email',
            '(
                SELECT
                    phone
                FROM tbl_person_phone
                WHERE primary_phone = 1 AND archive = 0 AND person_id = p.id LIMIT 1
            ) as recipient_phone',
            "(
                SELECT
                    concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as address
                FROM tbl_person_address as pt
                WHERE primary_address = 1 AND archive = 0 AND person_id = p.id LIMIT 1
            ) as recipient_address",
            "(
                CASE
                    WHEN saa.account_type = 1 THEN 'Person'
                    WHEN saa.account_type = 2 THEN 'Organization'
                    ELSE ''
                END
            ) as account_type_name",
            "(
                CASE
                    WHEN saa.account_type = 1 THEN (
                        SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                        FROM tbl_person AS person
                        WHERE person.id = saa.account_id
                        LIMIT 1
                    )
                    WHEN saa.account_type = 2 THEN (
                        SELECT org.name
                        FROM tbl_organisation AS org
                        WHERE org.id = saa.account_id
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_name",
            "(
                CASE
                    WHEN saa.account_type = 1 THEN (
                        SELECT pe.email
                        FROM tbl_person AS person
                        INNER JOIN tbl_person_email as pe ON pe.person_id = person.id
                        WHERE person.id = saa.account_id and pe.primary_email = 1 AND pe.archive = 0
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_email",
            "(
                CASE
                    WHEN saa.account_type = 1 THEN (
                        SELECT pp.phone
                        FROM tbl_person AS person
                        INNER JOIN tbl_person_phone as pp ON pp.person_id = person.id
                        WHERE person.id = saa.account_id and pp.primary_phone = 1 AND pp.archive = 0
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_phone",
            "(
                CASE
                    WHEN saa.account_type = 1 THEN (
                        SELECT person.ndis_number as my_ndis_number
                        FROM tbl_person AS person
                        WHERE person.id = saa.account_id
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_my_ndis_number",
            "(
                CASE
                    WHEN saa.account_type = 1 THEN (
                        SELECT person.date_of_birth
                        FROM tbl_person AS person
                        WHERE person.id = saa.account_id
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_date_of_birth",
            "p.ndis_number as my_ndis_number",
            "p.date_of_birth",
            "sa.contract_start_date",
            "sa.contract_end_date",
            "sa.plan_start_date",
            "sa.plan_end_date",
            "sa.additional_services",
            "sa.additional_services_custom",
            "sa.opportunity_id"
        ];
        $where_col = [
            "saa.id" => $service_agreement_attachment_id
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $saa_col_select)), false);
        $this->db->from(TBL_PREFIX . 'service_agreement_attachment as saa');
        $this->db->join("tbl_person as p", "p.id = saa.signed_by AND p.archive = 0", "inner");
        $this->db->join("tbl_service_agreement as sa", "sa.id = saa.service_agreement_id", "inner");
        $this->db->where($where_col);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row_array() : [];
    }

    /*
     * Get all line items associate with sevice agreement
     * param {int} service_agreement_id
     * return - array
     */
    public function get_sa_contract_line_items($service_agreement_id)
    {
        $this->db->from(TBL_PREFIX . 'service_agreement_items as sai');
        $this->db->select(array('sai.id as incr_id_service_agreement_items', 'sai.line_item_id', 'sai.amount', 'sai.qty','sa.line_item_sa_total','sa.line_item_total', 'fli.line_item_name', 'fli.line_item_number', 'fsc.key_name as support_cat_key_name', 'fsc.name as support_cat', 'flip.upper_price_limit', 'fli.category_ref','sa.grand_total', 'sai.price'));
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
        $this->db->join("tbl_finance_line_item as fli", "fli.id = sai.line_item_id", "inner");
        $this->db->join("tbl_finance_line_item_price as flip", "flip.id = sai.line_item_price_id", "left");
        $this->db->join("tbl_finance_support_category as fsc", "fsc.id = fli.support_category AND fsc.archive=0", "left");
        $this->db->join("tbl_service_agreement as sa", "sa.id = sai.service_agreement_id", "left");
        $this->db->where(array('sai.archive' => 0, 'sai.service_agreement_id' => $service_agreement_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $item_ary = $query->result();       
        $support_category = [];
        $category_list = [];
        $total_amount_array = [];
        $total_amount_cat = [];
        $total_amount = 0;
        $no_cat = 'no_sub_categoty';
        if ($query->num_rows() > 0) {            
            $line_item_sa_total = $item_ary[0]->line_item_sa_total;
            $line_item_total = $item_ary[0]->line_item_total;
            $grand_total = $item_ary[0]->grand_total;
            
            $amount= 0; $category = NULL;
            
            foreach ($item_ary as $key => $value) {
                if ($value->support_cat_key_name == '' || $value->support_cat_key_name == null) {
                    $value->support_cat_key_name = $no_cat;
                }

                if ($value->upper_price_limit == '' || $value->upper_price_limit == null) {
                    $value->upper_price_limit = $value->price;
                }
                
                if(empty($value->category_ref)){
                    $support_category[$value->line_item_number]['items'][] = $value;
                    $support_category[$value->line_item_number]['cat_name'] = $value->support_cat;
                }else{
                    $support_category[$value->category_ref]['items'][] = $value;
                    $support_category[$value->category_ref]['cat_name'] = $value->support_cat;
                }    
               
                if(empty($value->category_ref)){
                    if (isset($total_amount_array[$value->line_item_number]) == true && $total_amount_array[$value->line_item_number] != '') {
                        $total_amount_array[$value->line_item_number] = $total_amount_array[$value->line_item_number] + $value->amount;
                    } else {
                        $total_amount_array[$value->line_item_number] = $value->amount;
                    }
                    if ($value->amount > 0) {
                        $total_amount_cat[$value->line_item_number] = $value->amount;
                    }                    
                    $amount = $total_amount_array[$value->line_item_number];
                    $category = $value->line_item_number;
                }else{
                    if (isset($total_amount_array[$value->category_ref]) == true && $total_amount_array[$value->category_ref] != '') {
                        $total_amount_array[$value->category_ref] = $total_amount_array[$value->category_ref] + $value->amount;
                    } else {
                        $total_amount_array[$value->category_ref] = $value->amount;
                    }
                    $amount = $total_amount_array[$value->category_ref];
                    $category = $value->category_ref;
                }               
                
                if (isset($total_amount_cat[$category]) & !empty($total_amount_cat[$category])) {
                    $support_category[$category]['sub_total'] = $total_amount_cat[$category];
                } else {
                    $support_category[$category]['sub_total'] = $amount;
                }                

                if (empty($value->category_ref) || !$this->isParentAdded($item_ary, $value)) {
                    $total_amount += $value->amount;
                }

            }
            $category_list['list'] = $support_category;
            $category_list['total_amount'] = $total_amount;
            $category_list['line_item_sa_total'] = $line_item_sa_total;
            $category_list['line_item_total'] = $line_item_total;
            $category_list['grand_total'] = $grand_total;
        }       
        return $category_list;
    }

    /*
     * Get all line items associate with sevice agreement additional fund
     * param {int} service_agreement_id
     * return - array
     */
    public function get_sa_additional_fund($service_agreement_id)
    {
        $this->db->from(TBL_PREFIX . 'service_agreement_additional_fund as saaf');
        $this->db->select(array('saaf.additional_title', 'saaf.additional_price'));
        $this->db->where(array('saaf.archive' => 0, 'saaf.service_agreement_id' => $service_agreement_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->result();
    }

    /*
     * Get all goals associate with sevice agreement
     * param {int} service_agreement_id
     * return - array
     */
    public function get_sa_contract_goals($service_agreement_id)
    {
        $result = $this->db->get_where('tbl_goals_master', ['service_agreement_id' => $service_agreement_id, 'archive' => 0])->result_array();
        return $result;
    }

    /*
     * Get payments with sevice agreement
     * param {int} service_agreement_id
     * return - array
     *
     * - this function used in more than places
     */
    public function get_sa_contract_payment($service_agreement_id)
    {
        $return = array();
        $return['managed_type'] = '';
        $return['portal_managed'] = [];
        $return['plan_manged'] = [];
        $return['self_managed'] = [];
        // get payment type
        $query = $this->db->select(['managed_type'])
            ->where(['service_agreement_id' => $service_agreement_id])
            ->get('tbl_sa_payments')->row('managed_type');
        $return['managed_type'] = $query;
        // get portal managed payment data
        $query = $this->db
            ->where(['service_agreement_id' => $service_agreement_id, 'managed_type' => 1])
            ->get('tbl_sa_payments');
        // echo  $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $portal_managed = $query->row_array();
        if ($query->num_rows() > 0) {
            $return['portal_managed']['service_booking_creator'] = $portal_managed['service_booking_creator'];
        }

        // get plan managed payment data
        $this->db->from(TBL_PREFIX . 'sa_payments as sap');
        $this->db->select(array(
            'sap.*',
            "
            (
                CASE
                    WHEN sap.organisation_contact_id = 0 THEN 'Accounts Payable'
                    ELSE
                        CONCAT_WS(' ', p.firstname, p.lastname)
                END
            ) as contact_name",
            "
            (
                CASE
                    WHEN sap.organisation_contact_id = 0 THEN (
                        SELECT
                        oape.email
                        FROM tbl_organisation_accounts_payable_email AS oape
                        WHERE oape.organisationId = sap.organisation_id AND oape.archive = 0 AND oape.primary_email = 1
                        LIMIT 1
                    )
                    ELSE
                        pe.email
                END
            ) as contact_email",
            "
            (
                CASE
                    WHEN sap.organisation_contact_id = 0 THEN (
                        SELECT
                        oape.phone
                        FROM tbl_organisation_accounts_payable_phone AS oape
                        WHERE oape.organisationId = sap.organisation_id AND oape.archive = 0 AND oape.primary_phone = 1
                        LIMIT 1
                    )
                    ELSE
                        pp.phone
                END
            ) as contact_phone",
            "o.name as account_name",
        ));
        $this->db->join("tbl_organisation as o", "o.id = sap.organisation_id", "INNER");
        $this->db->join("tbl_person as p", "p.id = sap.organisation_contact_id", "LEFT");
        $this->db->join("tbl_person_email as pe", "pe.person_id = sap.organisation_contact_id AND pe.archive = 0 AND pe.primary_email = 1", "LEFT");
        $this->db->join("tbl_person_phone as pp", "pp.person_id = sap.organisation_contact_id AND pp.archive = 0 AND pp.primary_phone = 1", "LEFT");
        $this->db->where(['service_agreement_id' => $service_agreement_id, 'managed_type' => 2]);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $plan_manged = $query->row_array();
        if ($query->num_rows() > 0) {
            $org_id = $plan_manged['organisation_id'];
            // get org billing address
            $org_billing_address = $this->get_organisation_address($org_id, 1);
            $plan_manged['account_address'] = $org_billing_address;
            $return['plan_manged'] = $plan_manged;
        }

        // get self managed payment data
        $this->db->from(TBL_PREFIX . 'sa_payments as sap');
        $this->db->select(array(
            'sap.*',
            "(
                CASE
                    WHEN sa.account_type = 1 THEN (
                        SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                        FROM tbl_person AS person
                        WHERE person.id = sa.account
                        LIMIT 1
                    )
                    WHEN sa.account_type = 2 THEN (
                        SELECT org.name
                        FROM tbl_organisation AS org
                        WHERE org.id = sa.account
                        LIMIT 1
                    )
                    ELSE ''
                END
            ) as account_name",
            "sa.account",
            "CONCAT_WS(' ', p.firstname, p.lastname) as contact_name",
            "pe.email as contact_email",
            "pp.phone as contact_phone",
            "sa.account_type"
        ));
        $this->db->join("tbl_person as p", "p.id = sap.self_managed_contact_id", "INNER");
        $this->db->join("tbl_person_email as pe", "pe.person_id = sap.self_managed_contact_id AND pe.archive = 0 AND pe.primary_email = 1", "LEFT");
        $this->db->join("tbl_person_phone as pp", "pp.person_id = sap.self_managed_contact_id  AND pp.archive = 0 AND pp.primary_phone = 1", "LEFT");
        $this->db->join("tbl_service_agreement as sa", "sa.id = sap.service_agreement_id", "INNER");
        $this->db->where(['sap.service_agreement_id' => $service_agreement_id, 'sap.managed_type' => 3]);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $self_managed = $query->row_array();
        if ($query->num_rows() > 0) {

            $account_type = $self_managed['account_type'];
            if ($account_type == 1) {
                // get contact address details
                $contact_id = $self_managed['account'];
                $self_managed["account_address"] = $this->get_contact_address($contact_id);
            } else {
                // get org billing address
                $org_id = $self_managed['account'];
                $org_billing_address = $this->get_organisation_address($org_id, 1);
                $self_managed['account_address'] = $org_billing_address;
            }

            $return['self_managed'] = $self_managed;
        }

        return $return;
    }

    /*
     * it is used for get contact address
     *
     * @params $contactId
     *
     * return type string
     * complete address string
     */
    function get_contact_address($contactId)
    {
        $this->db->select(["pt.street as address_line_1", "concat(pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as address_line_2"]);
        $this->db->from("tbl_person_address as pt");
        $this->db->where("pt.person_id", $contactId);
        $this->db->where("pt.primary_address", 1);
        $this->db->where("pt.archive", 0);

        return $this->db->get()->row_array();
    }

    /*
     * it is used for get organisation address
     *
     * @params $org_id, $address_type
     * blling_address / shipping_address
     *
     * return type string
     * complete address string
     */
    function get_organisation_address($org_id, $address_type)
    {
        $this->db->select(["oa.street as address_line_1", "concat(oa.city,' ',(select s.name from tbl_state as s where s.id = oa.state),' ',oa.postal) as address_line_2"]);
        $this->db->from("tbl_organisation_address as oa");
        $this->db->where("oa.organisationId", $org_id);
        $this->db->where("oa.primary_address", 1);
        $this->db->where("oa.address_type", $address_type);
        $this->db->where("oa.archive", 0);

        return $this->db->get()->row_array();
    }

    /*
     * Get contact list associated with opportunity
     * return - array
     */
    function get_opportunity_contacts($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'sales_relation as oc');
        $this->db->select("case when oc.roll_id!=0 THEN
        (SELECT display_name as role FROM tbl_references as r where r.id=oc.roll_id AND r.archive=0 AND r.type=6) ELSE '' END as role", false);
        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS name", FALSE);
        $this->db->select(array('p.id as person_id'));
        $this->db->join("tbl_person as p", "p.id = oc.destination_data_id AND p.archive = 0", "inner");
        $this->db->where(array('oc.archive' => 0, 'oc.source_data_id' => $opportunity_id, 'oc.source_data_type' => 3, 'oc.destination_data_type' => 1));
        $query = $this->db->get()->result();
        $rows = array();
        if (!empty($query)) {
            foreach ($query as $val) {
                $rows[] = array('label' => $val->name, 'value' => $val->person_id);
            }
        }
        return $rows;
    }



    /*
     * Get the contact is decision maker or not associate with opportunity
     * @param {int} opportunity_id
     * @param {int} person_id
     * return boolean
     */
    public function get_decision_maker_contact_by_id($opportunity_id, $person_id)
    {
        $this->db->from(TBL_PREFIX . 'sales_relation as oc');
        $this->db->select("case when oc.roll_id!=0 THEN
        (SELECT display_name as role FROM tbl_references as r where r.id=oc.roll_id AND r.archive=0 AND r.type=6) ELSE '' END as role", false);
        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS name", FALSE);
        $this->db->select(array('p.id as person_id'));
        $this->db->join("tbl_person as p", "p.id = oc.destination_data_id AND p.archive = 0", "inner");
        $this->db->where(array('oc.archive' => 0, 'oc.source_data_id' => $opportunity_id, 'p.id' => $person_id, 'oc.source_data_type' => 3, 'oc.destination_data_type' => 1));
        $role = $this->db->get()->row('role');

        if (isset($role) == true && $role == 'Decision Maker') {
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }

    /*
     * Get the contact is participant or not associate with opportunity
     * @param {int} person_id
     * return boolean
     */
    public function get_participant_contact_by_id($person_id)
    {
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS name", FALSE);
        $this->db->select(array('p.id as person_id', 'pt.key_name as person_type'));
        $this->db->join("tbl_person_type as pt", "pt.id = p.type AND pt.archive = 0", "inner");
        $this->db->where(array('p.id' => $person_id));
        $person_type = $this->db->get()->row('person_type');

        $person_type_check = array('applicant', 'participant');
        if (isset($person_type) == true && (in_array($person_type, $person_type_check))) {
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }

    /*
     * Get list of accounts by search str
     * @param {str} $post_data
     * return array
     */
    function get_account_list_names($post_data)
    {
        $this->db->or_like('name', $post_data);
        $this->db->select(array('id', 'name'));
        $query = $this->db->get(TBL_PREFIX . 'organisation');
        //last_query();
        $query->result();
        $rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $rows[] = array('label' => $val->name, 'value' => $val->id);
            }
        }
        return $rows;
    }

    /*
     * Get list of contacts by search str
     * @param {str} $post_data
     * return array
     */
    function get_account_contacts($post_data)
    {
        $getParmval = json_decode($post_data);
        $this->db->select(array('destination_data_id'));
        $this->db->from("tbl_sales_relation");
        $this->db->where("destination_data_type", 1);
        $this->db->where("source_data_type", 2);
        $this->db->where("source_data_id", $getParmval->orgId);
        $res = $this->db->get()->result_array();
        $rows = array();
        if (!empty($res)) {
            $ids = array_column($res, "destination_data_id");
            // echo '<pre>';print_r($ids);die;
            $this->db->select(["concat_ws(' ',firstname,lastname) as contact_name", "p.id"]);
            $this->db->from("tbl_person as p");
            $this->db->where_in("p.id", $ids);
            $main_result = $this->db->get()->result();

            if (!empty($main_result)) {
                foreach ($main_result as $val) {
                    $rows[] = array('label' => $val->contact_name, 'value' => $val->id);
                }
            }
        }
        return $rows;
    }

    /*
     * Get service agreement attachment email details
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    function get_service_agreement_attachment_email($service_agreement_attachment_id)
    {
        $this->db->where("service_agreement_attachment_id", $service_agreement_attachment_id);
        $this->db->limit(1);
        $this->db->select(array('id', 'service_agreement_attachment_id', 'subject', 'email_content', 'cc_email'));
        $query = $this->db->get(TBL_PREFIX . 'service_agreement_attachment_email');
        //last_query();
        $result = $query->row_array();

        return $result;
    }

    /*
     * Get service agreement attachment email cc details
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    function get_service_agreement_attachment_email_cc($service_agreement_attachment_id)
    {
        $this->db->where("service_agreement_attachment_id", $service_agreement_attachment_id);
        $this->db->select(array('id', 'service_agreement_attachment_id', 'subject', 'email_content', 'cc_email'));
        $query = $this->db->get(TBL_PREFIX . 'service_agreement_attachment_email');
        //last_query();
        $result = $query->result();

        return $result;
    }

    /**
     * Get history of all fields of service agreement
     * @param {object}
     * return json string
     */
    function get_field_history($data)
    {
        $items = [];

        $items = $this->db->from(TBL_PREFIX . 'service_agreement_history as h')
            ->select(['h.id', 'h.id as history_id', 'f.id as field_history_id', 'f.field', 'f.value', 'f.prev_val', 'CONCAT(m.firstname, \' \', m.lastname) as created_by', 'h.created_at', 'h.created_at', 'hf.desc as feed_title', 'hf.id as feed_id'])
            ->where(['h.service_agreement_id' => $data->service_agreement_id])
            ->join(TBL_PREFIX . 'service_agreement_field_history as f', 'f.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'service_agreement_history_feed as hf', 'hf.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'service_agreement as sa', 'sa.id = h.service_agreement_id', 'left')
            ->join(TBL_PREFIX . 'member as m', 'm.uuid = h.created_by', 'left')
            ->order_by('h.id', 'DESC')
            ->get()->result();

        // prefetching the list of possible types AoT, as there will not be high volumes of these records,
        // will be more effecient than repeated queries in loop
        $status_types           = [0 => "Draft", 1 => "Issued", 3 => "Inactive", 4 => "Declined", 5 => "Active"];

        $this->load->model('Feed_model');
        $related_type = $this->Feed_model->get_related_type('service_agreement');

        $feed = [];
        // map fields to rendered values
        foreach ($items as $item) {
            $item->related_type = $related_type;
            $item->expanded = true;
            $item->feed = false;
            $item->comments = [];
            $history_id = $item->history_id;
            // history comments
            $comments = $this->Feed_model->get_comment_by_history_id($item->history_id, $related_type);
            $item->comments = $comments;
            $item->comments_count = count($comments);
            $item->comment_create = false;
            $item->comment_post = false;
            $item->comment_desc = '';

            if($item->field=='owner'){
                $item->field='Assigned To';
            }

            switch ($item->field) {
                case 'status':
                    $item->value     = isset($item->value) ? ($this->map_history_field($status_types, $item->value)) ?? 'Draft' : 'Draft';
                    $item->prev_val  = isset($item->prev_val) ? $this->map_history_field($status_types, $item->prev_val) ?? 'Draft' : 'Draft';
                    break;

                case 'Assigned To':
                case 'signed_by':
                    $owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->value])->get()->result();
                    $prev_owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($owner) ? $owner[0]->user : 'N/A';
                    $item->prev_val = !empty($prev_owner) ? $prev_owner[0]->user : 'N/A';
                    break;

                case 'grand_total':
                case 'sub_total':
                case 'tax':
                    $item->value = isset($item->value) && is_numeric($item->value) ? '$' . $item->value : '$00.00';
                    $item->prev_val = isset($item->prev_val) && is_numeric($item->prev_val) ? '$' . $item->prev_val : '$00.00';
                    break;
                case 'customer_signed_date':
                case 'contract_start_date':
                case 'contract_end_date':
                case 'plan_start_date':
                case 'plan_end_date':
                    $item->value = !empty($item->value) && $item->value !== '0000-00-00 00:00:00' ? date("d/m/Y", strtotime($item->value)) : "N/A";
                    $item->prev_val = !empty($item->prev_val) && $item->prev_val !== '0000-00-00 00:00:00' ? date("d/m/Y", strtotime($item->prev_val)) : "N/A";
                    break;
                case 'additional_services':
                    $additionalServiceTypes = [
                        1 => "Support Coordination",
                        2 => "NDIS Client Services",
                        3 => "Supported Independent Living",
                        4 => "Plan Management",
                        5 => "Other"
                    ];
                    if (!empty($item->value)) {
                        $cval = json_decode($item->value);
                        $cval_arr = [];
                        foreach ($cval as $val) {
                            if ($val == 5) {
                                continue;
                            }
                            if (is_numeric($val)) {
                                $cval_arr[] = $additionalServiceTypes[$val];
                            } else {
                                $cval_arr[] = $val;
                            }
                        }
                        $item->value = json_encode($cval_arr);
                    } else {
                        $item->value = 'N/A';
                    }
                    if (!empty($item->prev_val)) {
                        $pval = json_decode($item->prev_val);
                        if (empty($pval)) {
                            $item->prev_val = 'N/A';
                        } else {
                            $pval_arr = [];

                            foreach ($pval as $val) {
                                if ($val == 5) {
                                    continue;
                                }
                                if (is_numeric($val)) {
                                    $pval_arr[] = $additionalServiceTypes[$val];
                                } else {
                                    $pval_arr[] = $val;
                                }
                            }
                            $item->prev_val = json_encode($pval_arr);
                        }
                    } else {
                        $item->prev_val = 'N/A';
                    }
                case 'NULL':
                case '':
                    $item->feed = true;
                    break;
                case 'default':
                    $item->value = !empty($item->value) ? $item->value : 'N/A';
                    $item->prev_val = !empty($item->prev_val) ? $item->prev_val : 'N/A';
                    break;
            }
            if ($item->feed_id == '' || $item->feed_id == NULL) {
                $item->feed = false;
            }

            if (($item->field_history_id != '' && $item->field_history_id != NULL) || ($item->feed_id != '' && $item->feed_id != NULL)) {
                $feed[$history_id][] = $item;
            }
        }
        krsort($feed);
        $feed = array_values($feed);

        // let the client group history items
        echo json_encode($feed);
    }
    private function map_history_field($types, $index)
    {
        if (isset($types[$index]) == true) {
            return $types[$index];
        }
    }

     /**
     * Get person list with account if it's a person
     * @param {int} $opportunity_id
     * @return array
     */
    function get_oppunty_contacts_with_account($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'sales_relation as oc');
        $this->db->select("case when oc.roll_id!=0 THEN
        (SELECT display_name as role FROM tbl_references as r where r.id=oc.roll_id AND r.archive=0 AND r.type=6) ELSE '' END as role", false);
        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS name", FALSE);
        $this->db->select(array('p.id as person_id'));
        $this->db->join("tbl_person as p", "p.id = oc.destination_data_id AND p.archive = 0", "inner");
        $this->db->where(array('oc.archive' => 0, 'oc.source_data_id' => $opportunity_id, 'oc.source_data_type' => 3, 'oc.destination_data_type' => 1));
        $query = $this->db->get()->result();
        $rows = array();
        if (!empty($query)) {
            foreach ($query as $val) {
                $rows[] = array('label' => $val->name, 'value' => $val->person_id);
            }
        }

        # Get opportunity account
        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS label, sub_os.account_person as value", FALSE);
        $this->db->from(TBL_PREFIX . 'opportunity as sub_os');
        $this->db->join("tbl_person as p", "p.id = sub_os.account_person AND p.archive = 0", "INNER");
        $this->db->where("sub_os.id", $opportunity_id);
        $this->db->where("sub_os.account_type", 1);
        $this->db->where("sub_os.related_lead IS NULL");
        $this->db->limit(1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $query_res = $query->result();

        $account = [];
        if (!empty($query)) {
            $account = $query_res;
        }
        $rows = array_merge($account, $rows);
        return $rows;
    }

    /*
     * Get service agreement contract data by id only NDIS
     * @param {int} id
     * return array
     */
    public function service_docusign_data_by_id($id, $type = NULL)
    {
        $data = [];
        $base_url = base_url('mediaShow/SA');
        $base_s3_url = base_url('mediaShow/s3');

        $query = $this->db
            ->select(array(
                "saa.*",
                "CONCAT_WS(' ', creator.firstname, creator.lastname) AS created_by_fullname",
                "(
                    CASE
                        WHEN saa.signed_status = 0 THEN 'Not Signed Yet'
                        WHEN saa.signed_status = 1 THEN 'Signed'
                    END
                ) AS contract_status",
                "(
                    CASE
                        WHEN saa.account_type = 1 THEN (
                            SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                            FROM tbl_person AS person
                            WHERE person.id = saa.account_id
                            LIMIT 1
                        )
                        WHEN saa.account_type = 2 THEN (
                            SELECT org.name
                            FROM tbl_organisation AS org
                            WHERE org.id = saa.account_id
                            LIMIT 1
                        )
                        ELSE ''
                    END
                ) as account_name",
                "CONCAT_WS(' ', person.firstname, person.lastname) AS signed_by_name",
                "saa.signed_by AS signed_by_id",
                "(
                    CASE
                        WHEN saa.aws_uploaded_flag = 1 THEN
                        (CONCAT('".$base_s3_url."', '/', saa.id, '/', REPLACE(TO_BASE64(saa.file_path), '=', '%3D%3D'), '?ownload_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=true'))
                        ELSE
                        (CONCAT('".$base_url."', '/', saa.id, '?filename=', REPLACE(TO_BASE64(saa.signed_file), '=', '%3D%3D'), '&download_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=false'))
                    END

                ) AS url",
                "(
                    CASE
                        WHEN saa.document_type = 2 THEN 'NDIS Service Agreement'
                    END
                ) AS contract_status",
                ));

            $this->db->join('tbl_member AS creator', 'saa.created_by = creator.uuid', 'LEFT');
            $this->db->join('tbl_person AS person', 'saa.signed_by = person.id', 'LEFT');
            $this->db->where(['service_agreement_id' => $id]);
            # 2 NDIS Document type
            $this->db->where(['document_type' => 2]);
            $this->db->where('signed_status', 1);
            if($type) {
                $this->db->where(['saa.type' => $type]);
            }
            $this->db->order_by('id', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get('tbl_service_agreement_attachment as saa')->row_array();

        // save result if nom_rows not equal to zero
        if (!empty($query)) {
            $data = $query;
        }

        return $data;
    }

    /*
     * Get service agreement contract data by id
     * @param {int} id
     * return array
     */
    public function service_docusign_data_by_sa_id($id, $sa_id = NULL)
    {
        $data = [];
        $base_url = base_url('mediaShow/SA');
        $base_s3_url = base_url('mediaShow/s3');

        $query = $this->db
            ->select(array(
                "saa.*",
                "CONCAT_WS(' ', creator.firstname, creator.lastname) AS created_by_fullname",
                "(
                    CASE
                        WHEN saa.signed_status = 0 THEN 'Not Signed Yet'
                        WHEN saa.signed_status = 1 THEN 'Signed'
                    END
                ) AS contract_status",
                "(
                    CASE
                        WHEN saa.account_type = 1 THEN (
                            SELECT CONCAT_WS(' ', person.firstname, person.lastname)
                            FROM tbl_person AS person
                            WHERE person.id = saa.account_id
                            LIMIT 1
                        )
                        WHEN saa.account_type = 2 THEN (
                            SELECT org.name
                            FROM tbl_organisation AS org
                            WHERE org.id = saa.account_id
                            LIMIT 1
                        )
                        ELSE ''
                    END
                ) as account_name",
                "CONCAT_WS(' ', person.firstname, person.lastname) AS signed_by_name",
                "saa.signed_by AS signed_by_id",
                "(
                    CASE
                        WHEN saa.aws_uploaded_flag = 1 THEN
                        (CONCAT('".$base_s3_url."', '/', saa.id, '/', REPLACE(TO_BASE64(saa.file_path), '=', '%3D%3D'), '?ownload_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=true'))
                        ELSE
                        (CONCAT('".$base_url."', '/', saa.id, '?filename=', REPLACE(TO_BASE64(saa.signed_file), '=', '%3D%3D'), '&download_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=false'))
                    END

                ) AS url",
                "(
                    CASE
                        WHEN saa.document_type = 2 THEN 'NDIS Service Agreement'
                    END
                ) AS contract_status",
                ));

            $this->db->join('tbl_member AS creator', 'saa.created_by = creator.uuid', 'LEFT');
            $this->db->join('tbl_person AS person', 'saa.signed_by = person.id', 'LEFT');
            $this->db->where(['service_agreement_id' => $id]);
            $this->db->where('saa.id', $sa_id);
            $this->db->order_by('saa.id', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get('tbl_service_agreement_attachment as saa')->row_array();

        // save result if nom_rows not equal to zero
        if (!empty($query)) {
            $data = $query;
        }

        return $data;
    }
    
    public function get_doc_type_by_service_agreement_type($sa_type) {
        $doc_types_by_name = array_flip($this->document_type);
        $doc_type = 0;
        if ($sa_type === "NDIS") {
            $doc_type = $doc_types_by_name['NDIS Service Agreement'];
        }
        if ($sa_type === "Support Coordination") {
            $doc_type = $doc_types_by_name['Support Coordination Service Agreement'];
        }
        return $doc_type;
    }

    public function get_service_agreement_contracts($data)
    {
        $sa_type = $data->service_agreement_type;
        $service_booking_id = $data->service_booking_id;
        $service_agreement_id = $data->service_agreement_id;
        $doc_type = $this->get_doc_type_by_service_agreement_type($sa_type);
        $this->db->select(['sa.contract_start_date', 'sa.contract_end_date', 'saa.id', 'saa.service_agreement_id', 'saa.signed_date','saa.contract_id']);
        $this->db->from(TBL_PREFIX . 'service_agreement_attachment as saa');
        $this->db->join(TBL_PREFIX . 'service_agreement as sa', 'sa.id = saa.service_agreement_id', 'left');
        $this->db->where(['sa.archive' => 0, 'saa.archive' => 0, 'saa.signed_status' => 1, 'saa.document_type' => $doc_type, 'saa.service_agreement_id' => $service_agreement_id]);
        $query = $this->db->get();   
        //$s = $this->db->last_query();
        $result = $query->result();
        return $result;
    }

    public function get_service_contract_funding($data) {
        $service_agreement_attachment_id = $data->service_agreement_attachment_id?? 0;
        $service_booking_id = $data->service_booking_id?? 0;
        $this->db->select(['SUM(sb.funding) as funding']);
        $this->db->from(TBL_PREFIX . 'service_booking as sb');
        $this->db->where(['sb.archive' => 0, 'service_agreement_attachment_id' => $service_agreement_attachment_id, 'id !=' => $service_booking_id]);
        $row = $this->db->get()->row();
        return $row->funding;
    }
    public function isParentAdded($items, $item) {
        if ($item->category_ref === "") {
            return false;
        }
        $parent_exists = false;
        foreach($items as $row) {
            if ($row->line_item_number === $item->category_ref && $row->amount > 0) {
                $parent_exists = true;
                break;
            }
        }
        return $parent_exists;
    }
    public function get_line_items_additional_funding_detail($data) {

        $this->db->from(TBL_PREFIX . 'service_agreement as sai');
        $this->db->select(array('sai.id as service_agreement_id', 'af.additional_title','af.additional_price'));
        $this->db->join(TBL_PREFIX . "service_agreement_additional_fund as af", "af.service_agreement_id = sai.id", "inner");      
        $this->db->where(array('sai.archive' => 0, 'af.archive' => 0, 
            'sai.id' => $data->service_agreement_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return array('data' => $query->result(), 'status' => true);
    }

    /**
     * Get service agreement data by service agreement by id
     * @param int $id
     * @return array
     */
    public function get_service_agreement_data_by_id($id)
    {         
        $query = $this->db->get_where('tbl_service_agreement', [
            'archive' => 0,
            'id' => $id,
        ]);
        return $query->row_array();
    }
    /**
     * Save line item additional funding 
     */
    function save_additional_funding($addFunds, $id, $adminId)
    {
        $records = [];
        foreach ($addFunds as $fund) {
            $record = new stdClass();
            $record->service_agreement_id = $id;
            $record->additional_title = $fund->additional_title;
            $record->additional_price = $fund->additional_price;
            $record->archive = 0;
            $record->created_by = $adminId;
            $record->created = DATE_TIME;
            array_push($records, $record);
        }
 
        if (isset($records) == true && empty($records) == false) {
            $this->basic_model->insert_records('service_agreement_additional_fund', $records, true);
        }
    }
    public function generate_service_agreement_doc($data, $filenameWithExtension,$service_agreement_attachment_id){
        // load docusing evelop
        $this->load->library('DocuSignEnvelope');
        // create envelop
        $statusDocuSign = $this->docusignenvelope->CreateEnvelope($data);
        // save envelop id and status if envelop created in docusign
        if(isset($statusDocuSign['status']) && $statusDocuSign['status']){
            $envId = $statusDocuSign['response']['envelopeId'];
            $updateAttachment = array(
                'envelope_id' => $envId,
                'unsigned_file' => $filenameWithExtension,
                'send_date' => DATE_TIME
            );
            $this->basic_model->update_records('service_agreement_attachment', $updateAttachment, array('id'=>$service_agreement_attachment_id,'archive'=>0));
        }

        $response = ['status' => true, 'msg' => "Contract generated successfully"];
        return json_encode($response);
    }

    public function consent_service_aggreement_docusign($service_agreement_attachment_id){
         // gather details for attachment
         $serviceAgreementContract = $this->ServiceAgreement_model->get_sa_attachment_details($service_agreement_attachment_id);

         // return status with false if service agreement not exist
         if (isset($serviceAgreementContract) == false && !isset($serviceAgreementContract[0]['id']) ==false) {
             return [ "status" => false, "error" => "Service Agreement contract not exist" ];
         }
 
         // Get attachment details
         $to_name = $serviceAgreementContract[0]['to_name'];
         $to_email = $serviceAgreementContract[0]['to_email'];
         $recipient_name = $serviceAgreementContract[0]['recipient_name'];
         $recipient_email = $serviceAgreementContract[0]['recipient_email'];
         $to_account_name = $serviceAgreementContract[0]['account_name'];
         $attachment_type = $serviceAgreementContract[0]['contract_type'];
         
         // get email details
         $email = $this->ServiceAgreement_model->get_service_agreement_attachment_email($service_agreement_attachment_id);
         // pr($email);
         // set error reporting 0 to avoid warning and deprecated msg
         error_reporting(0);
         // Set data for doucment 
         $data['data']['name'] = $recipient_name;
         $data['data']['to'] = $to_account_name;
         // Load library file
         $this->load->library('m_pdf');
         $pdf = $this->m_pdf->load();
         // set margin type
         $pdf->setAutoTopMargin='pad';
         // get cover page header with background image
         $data['type']='header';
         $header = $this->load->view('constent_service_agreemnet_v1',$data,true);
         // get first page content
         $data['type']='content_1';
         $content_1 = $this->load->view('constent_service_agreemnet_v1',$data,true);
         // get second page header alone logo
         $data['type']='content_2_header';
         $content_2_header = $this->load->view('constent_service_agreemnet_v1',$data,true);
         // get second page content
         $data['type']='content_2';
         $content_2 = $this->load->view('constent_service_agreemnet_v1',$data,true);
         // get page style css
         $styleContent = $this->load->view('constent_service_agreemnet_v1_style',[],true);
         // set header & footer line
         $pdf->defaultfooterline = 0;
         $pdf->defaultheaderline = 0;
         // set header
         $pdf->SetHeader($header);
         // set page layout
         $pdf->AddPage('P','','','','',0,0,0,0,0,0);
         // write page content
         $pdf->WriteHTML($styleContent);
         $pdf->WriteHTML($content_1);
         $pdf->SetHeader($content_2_header);
         // add additional page with layout
         $pdf->AddPage('P','','','','',10,10,0,0,5,0);
         // write page content
         $pdf->WriteHTML($content_2);
         // service agreement file path create if not exist
         $fileParDir = FCPATH . SERVICE_AGREEMENT_CONTRACT_PATH;
         if (!is_dir($fileParDir)) {
             mkdir($fileParDir, 0755);
         }
         // create folder with service agreement attachment id
         $fileDir = $fileParDir . '/' . $service_agreement_attachment_id;
         if (!is_dir($fileDir)) {
             mkdir($fileDir, 0755);
         }
         // file name
         $filename = $attachment_type . ' ' . 'Contract unsigned';
         $filenameWithExtension = $filename.'.pdf';
         $pdfFilePath =  $fileDir . '/' . $filenameWithExtension;
         // write file into the folder
         $service_agreement_pdf = $pdf->Output($pdfFilePath, 'F');
         // generate & send envelope if file exist
         if(file_exists($pdfFilePath))
         {   
             $contractFileNameWithoutextension = $attachment_type . ' Contract';
             $email_subject = $attachment_type.' Contract';
             $email_content = '';
             // Email subject
             if (isset($email) && isset($email['subject']) && $email['subject'] !='') {
                 $email_subject = $email['subject'];
             }
             // Email body content
             if (isset($email) && isset($email['email_content']) && $email['email_content'] !='') {
                 $email_content = '<p style="margin: 0px 0px 10px 0px;">'.$to_name.', </p>';
                 $email_content .= $email['email_content'];
             }
             // signer recipient id
             $recipient_id = 1;
 
             // Add cc user
             $cc_user = [];
             if (isset($email) && isset($email['cc_email']) && $email['cc_email'] !='') {
                 $cc_temp = [];
                 // get email details
                 $email_cc = $this->ServiceAgreement_model->get_service_agreement_attachment_email_cc($service_agreement_attachment_id);
                 if(isset($email_cc) && empty($email_cc) == false) {
                     foreach($email_cc as $cc_index => $email_temp) {
                         $cc_recipient_id = $cc_index + 1;
                         $temp = (array) $email_temp;
                         $cc_temp['name'] = strstr($temp['cc_email'], '@', true);                
                         $cc_temp['email'] = $temp['cc_email'];
                         $cc_temp['recipient_id'] = $recipient_id + $cc_recipient_id;
                         $cc_temp['routing_order'] = 1;
                         $cc_user[] = $cc_temp;        
                     }
                 }
             }
             // docugin envelop details
             $data = [
                 'userdetails' => [
                     'name' => $to_name,
                     'email_subject' => $email_subject,
                     'email_message' => $email_content,
                     'email' => $to_email,
                     'recipient_id' => $recipient_id,
                     'routing_order' => 2,
                 ],
                 'cc_user' => $cc_user,
                 'document' => ['doc_id' => 1 , 'doc_name' => $contractFileNameWithoutextension,  'doc_path' => $pdfFilePath , 'web_hook_url'=>base_url(DS_STAGING_WEBHOOK_URL_SERVICEAGREEMENT)],                
                 'position' => [
                     ['position_x' => 171, 'position_y' => 305, 'document_id' => 1, 'page_number' => 2, 'recipient_id' => $recipient_id],
                 ],
                 'position_checkbox' => [
                     ['tab_order'=>1, 'selected'=>false, 'tab_label'=> 'Confirm', 'Require_initial'=>'true', 'required'=>'true','name'=>'Confirm','position_x' => 500, 'position_y' => 260, 'document_id' => 1, 'page_number' => 2, 'recipient_id' => $recipient_id]
                 ],
                 'position_text' => [
                     [ 'height'=>30,'width'=>260, 'required'=>'false','max_length'=>1000,'tab_order'=>3,'position_x' => 206, 'position_y' => 387, 'document_id' => 1, 'page_number' => 2, 'recipient_id' => $recipient_id]
                 ],
                 
                 'position_date_signed' => [
                     ['tab_label'=> 'Date', 'Require_initial'=>'true', 'required'=>'true','name'=>'date_signed','position_x' => 98, 'position_y' => 450, 'document_id' => 1, 'page_number' => 2, 'recipient_id' => $recipient_id],
                 ],
                 
             ];
            // load docusing evelop
            $this->generate_service_agreement_doc($data, $filenameWithExtension,$service_agreement_attachment_id);      
         } else {
             return [ "status" => false, "error" => "file not being generated" ];
         }
    }

    

    public function ndis_service_aggreement_docusign($service_agreement_attachment_id){
        $serviceAgreementContract = $this->ServiceAgreement_model->get_sa_contract_details($service_agreement_attachment_id);
        // return status with false if service agreement not exist
        if (isset($serviceAgreementContract) == false && !isset($serviceAgreementContract['id']) ==false) {
            return [ "status" => false, "error" => "Service Agreement contract not exist" ];
        }
        // gather details
        $service_agreement_id = $serviceAgreementContract['service_agreement_id'];
        // get all line items associate with service agreement
        $SALineItems = $this->ServiceAgreement_model->get_sa_contract_line_items($service_agreement_id);       
        // get all line items associate with service agreement additional fund
        $SAAdditionalFund = $this->ServiceAgreement_model->get_sa_additional_fund($service_agreement_id);   
        // get all line items associate with service agreement
        $SAGoals = $this->ServiceAgreement_model->get_sa_contract_goals($service_agreement_id);
        $SAPayments = $this->ServiceAgreement_model->get_sa_contract_payment($service_agreement_id);

        // get email details
        $email = $this->ServiceAgreement_model->get_service_agreement_attachment_email($service_agreement_attachment_id);

        // To person detail
        $to = [];
        $to['id'] = $serviceAgreementContract['to'];
        $to['name'] = $serviceAgreementContract['to_name'];
        $to['email'] = $serviceAgreementContract['to_email'];

        $data['to'] = $to;

        // signed by as recipient details
        $recipient = [];
        $recipient['id'] = $serviceAgreementContract['signed_by'];
        $recipient['name'] = $serviceAgreementContract['recipient_name'];
        $recipient['email'] = $serviceAgreementContract['recipient_email'];
        $recipient['phone'] = $serviceAgreementContract['recipient_phone'];
        $recipient['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
        $recipient['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        $recipient['address'] = $serviceAgreementContract['recipient_address'];

        $data['recipient'] = $recipient;

        // account details
        $account = [];
        $account['account_id'] = $serviceAgreementContract['account_id'];
        $account['account_type'] = $serviceAgreementContract['account_type'];
        $account['account_type_name'] = $serviceAgreementContract['account_type_name'];
        $account['name'] = $serviceAgreementContract['account_name'];
        $account['email'] = $serviceAgreementContract['account_email'];
        $account['phone'] = $serviceAgreementContract['account_phone'];
        $account['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];

        $data['account'] = $account;

        // get decision maker contact
        $opportunity_id = $serviceAgreementContract['opportunity_id'];
        $person_id = $serviceAgreementContract['to'];
        $decisionMakerContact = $this->ServiceAgreement_model->get_decision_maker_contact_by_id($opportunity_id, $person_id);
        $decision_maker = [];
        if ($decisionMakerContact == true) {
            $decision_maker['id'] = $serviceAgreementContract['to'];
            $decision_maker['name'] = $serviceAgreementContract['recipient_name'];
            $decision_maker['email'] = $serviceAgreementContract['recipient_email'];
            $decision_maker['phone'] = $serviceAgreementContract['recipient_phone'];
            $decision_maker['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
            $decision_maker['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        }
        $data['decision_maker'] = $decision_maker;
        // service agreement
        $service_agreement = [];
        $service_agreement['service_agreement_id'] = $serviceAgreementContract['service_agreement_id'];
        $service_agreement['opportunity_id'] = $serviceAgreementContract['opportunity_id'];
        $service_agreement['contract_start_date'] = $serviceAgreementContract['contract_start_date'];
        $service_agreement['contract_end_date'] = $serviceAgreementContract['contract_end_date'];
        $service_agreement['plan_start_date'] = $serviceAgreementContract['plan_start_date'];
        $service_agreement['plan_end_date'] = $serviceAgreementContract['plan_end_date'];
        $service_agreement['additional_services'] = $serviceAgreementContract['additional_services'];
        $service_agreement['additional_services_custom'] = $serviceAgreementContract['additional_services_custom'];
        
        $data['service_agreement'] = $service_agreement;

        // get contact is participant or not
        $person_id = $serviceAgreementContract['account_id'];
        $participant = [];
        $participantContact = false;
        if ($serviceAgreementContract['account_type'] == 1) {
            $participantContact = $this->ServiceAgreement_model->get_participant_contact_by_id( $person_id);
        }
        if ($participantContact == true) {
            $participant['id'] = $serviceAgreementContract['account_id'];
            $participant['name'] = $serviceAgreementContract['account_name'];
            $participant['email'] = $serviceAgreementContract['account_email'];
            $participant['phone'] = $serviceAgreementContract['account_phone'];
            $participant['date_of_birth'] = $serviceAgreementContract['account_date_of_birth'];
            $participant['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];
        }
        $data['participant'] = $participant;

        $data['line_items'] = $SALineItems;
        $data['goals'] = $SAGoals;
        $data['payments'] = $SAPayments;
        $data['additional_funds'] = $SAAdditionalFund;
        // pr($data);

        // Get attachment details
        $to_name = $serviceAgreementContract['to_name'];
        $to_email = $serviceAgreementContract['to_email'];
        $recipient_name = $serviceAgreementContract['recipient_name'];
        $recipient_email = $serviceAgreementContract['recipient_email'];
        $to_account_name = $serviceAgreementContract['account_name'];
        $attachment_type = $serviceAgreementContract['contract_type'];

        // set error reporting 0 to avoid warning and deprecated msg
        error_reporting(0);
        // Load library file
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();
        // set margin type
        $pdf->setAutoTopMargin='pad';
        // get cover page header with background image
        $data['type']='header';
        $header = $this->load->view('service_agreemnet_v2',$data,true);
        // get footer image
        $data['type']='footer';
        $footer = $this->load->view('service_agreemnet_v2',$data,true);
        // get cover page content
        $data['type']='content_1';
        $content_1 = $this->load->view('service_agreemnet_v2',$data,true);
        // get second page header alone logo
        $data['type']='content_2_header';
        $content_2_header = $this->load->view('service_agreemnet_v2',$data,true);
        // get second page footer
        $data['type']='footer_2_content';
        $footer_2_content = $this->load->view('service_agreemnet_v2',$data,true);
        // get second page content
        $data['type']='content_2';
        $content_2 = $this->load->view('service_agreemnet_v2',$data,true);
        // get page style css
        $styleContent = $this->load->view('service_agreemnet_v1_style',[],true);

        // set header & footer line
        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        // set header
        $pdf->SetHeader($header);
        $pdf->SetFooter($footer);
        // set page layout
        $pdf->AddPage('P','','','','',0,0,0,0,0,0);
        // write page content
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($content_1);
        $pdf->SetHeader($content_2_header);
        $pdf->SetFooter($footer_2_content);
        // add additional page with layout        
        $pdf->AddPage('P','','','','',10,10,0,20,5,0);
        // write page content
        $pdf->WriteHTML($content_2);
        // service agreement file path create if not exist
        $fileParDir = FCPATH . SERVICE_AGREEMENT_CONTRACT_PATH;
        if (!is_dir($fileParDir)) {
            mkdir($fileParDir, 0755);
        }
        // create folder with service agreement attachment id
        $fileDir = $fileParDir . $service_agreement_attachment_id;
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755);
        }
        // file name
        $filename = $attachment_type . ' ' . 'Contract unsigned';
        $filenameWithExtension = $filename.'.pdf';
        $pdfFilePath =  $fileDir . '/' . $filenameWithExtension;
        // write file into the folder
        $page_count = $pdf->page;

        $service_agreement_pdf = $pdf->Output($pdfFilePath, 'F');
        // exit;
        // generate & send envelope if file exist
        if(file_exists($pdfFilePath))
        {   
            $contractFileNameWithoutextension = $attachment_type . ' Contract';
            $email_subject = $attachment_type.' Contract';
            $email_content = '';
            // Email subject
            if (isset($email) && isset($email['subject']) && $email['subject'] !='') {
                $email_subject = $email['subject'];
            }
            // Email body content
            if (isset($email) && isset($email['email_content']) && $email['email_content'] !='') {
                $email_content = '<p style="margin: 0px 0px 10px 0px;">'.$to_name.', </p>';
                $email_content .= $email['email_content'];
            }

            // signer recipient id
            $recipient_id = 1;

            // Add cc user
            $cc_user = [];
            if (isset($email) && isset($email['cc_email']) && $email['cc_email'] !='') {
                $cc_temp = [];
                // get email details
                $email_cc = $this->ServiceAgreement_model->get_service_agreement_attachment_email_cc($service_agreement_attachment_id);
                if(isset($email_cc) && empty($email_cc) == false) {
                    foreach($email_cc as $cc_index => $email_temp) {
                        $cc_recipient_id = $cc_index + 1;
                        $temp = (array) $email_temp;
                        $cc_temp['name'] = strstr($temp['cc_email'], '@', true);                
                        $cc_temp['email'] = $temp['cc_email'];
                        $cc_temp['recipient_id'] = $recipient_id + $cc_recipient_id;
                        $cc_temp['routing_order'] = 1;
                        $cc_user[] = $cc_temp;        
                    }
                }
            }
            // docugin envelop details
            $data = [
                'userdetails' => [
                    'name' => $to_name,
                    'email_subject' => $email_subject,
                    'email_message' => $email_content,
                    'email' => $to_email,                    
                    'recipient_id' => $recipient_id,
                    'routing_order' => 2,
                ],
                'cc_user' => $cc_user,
                'document' => ['doc_id' => 1 , 'doc_name' => $contractFileNameWithoutextension,  'doc_path' => $pdfFilePath , 'web_hook_url'=>base_url(DS_STAGING_WEBHOOK_URL_SERVICEAGREEMENT)],                
                'position' => [
                    ['position_x' => 81, 'position_y' => 238, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                ],
                'position_text' => [
                    [ 'height'=>30,'width'=>210, 'required'=>'false','max_length'=>1000,'tab_order'=>3,'position_x' => 60, 'position_y' => 403, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id]
                ],
                'position_date_signed' => [
                    ['tab_label'=> 'Date', 'Require_initial'=>'true', 'required'=>'true','name'=>'date_signed','position_x' => 64, 'position_y' => 455, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                    ['tab_label'=> 'Date', 'Require_initial'=>'false', 'required'=>'false','name'=>'date_signed_1','position_x' => 64, 'position_y' => 718, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                ],
                
            ];

       // load docusing evelop
       $this->generate_service_agreement_doc($data, $filenameWithExtension,$service_agreement_attachment_id);

    } else {
        return [ "status" => false, "error" => "file not being generated" ];
    }
    
    }

    public function support_coordination_service_aggreement_docusign($service_agreement_attachment_id){
        $serviceAgreementContract = $this->ServiceAgreement_model->get_sa_contract_details($service_agreement_attachment_id);
        // return status with false if service agreement not exist
        if (isset($serviceAgreementContract) == false && !isset($serviceAgreementContract['id']) ==false) {
            return [ "status" => false, "error" => "Service Agreement contract not exist" ];
        }
        // gather details
        $service_agreement_id = $serviceAgreementContract['service_agreement_id'];
        // get all line items associate with service agreement
        $SALineItems = $this->ServiceAgreement_model->get_sa_contract_line_items($service_agreement_id); 
        // get all line items associate with service agreement additional fund
        $SAAdditionalFund = $this->ServiceAgreement_model->get_sa_additional_fund($service_agreement_id);      
        // get all line items associate with service agreement
        $SAGoals = $this->ServiceAgreement_model->get_sa_contract_goals($service_agreement_id);
        $SAPayments = $this->ServiceAgreement_model->get_sa_contract_payment($service_agreement_id);

        // get email details
        $email = $this->ServiceAgreement_model->get_service_agreement_attachment_email($service_agreement_attachment_id);

        // To person detail
        $to = [];
        $to['id'] = $serviceAgreementContract['to'];
        $to['name'] = $serviceAgreementContract['to_name'];
        $to['email'] = $serviceAgreementContract['to_email'];

        $data['to'] = $to;

        // signed by as recipient details
        $recipient = [];
        $recipient['id'] = $serviceAgreementContract['signed_by'];
        $recipient['name'] = $serviceAgreementContract['recipient_name'];
        $recipient['email'] = $serviceAgreementContract['recipient_email'];
        $recipient['phone'] = $serviceAgreementContract['recipient_phone'];
        $recipient['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
        $recipient['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        $recipient['address'] = $serviceAgreementContract['recipient_address'];

        $data['recipient'] = $recipient;

        // account details
        $account = [];
        $account['account_id'] = $serviceAgreementContract['account_id'];
        $account['account_type'] = $serviceAgreementContract['account_type'];
        $account['account_type_name'] = $serviceAgreementContract['account_type_name'];
        $account['name'] = $serviceAgreementContract['account_name'];
        $account['email'] = $serviceAgreementContract['account_email'];
        $account['phone'] = $serviceAgreementContract['account_phone'];
        $account['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];

        $data['account'] = $account;

        // get decision maker contact
        $opportunity_id = $serviceAgreementContract['opportunity_id'];
        $person_id = $serviceAgreementContract['to'];
        $decisionMakerContact = $this->ServiceAgreement_model->get_decision_maker_contact_by_id($opportunity_id, $person_id);
        $decision_maker = [];
        if ($decisionMakerContact == true) {
            $decision_maker['id'] = $serviceAgreementContract['to'];
            $decision_maker['name'] = $serviceAgreementContract['recipient_name'];
            $decision_maker['email'] = $serviceAgreementContract['recipient_email'];
            $decision_maker['phone'] = $serviceAgreementContract['recipient_phone'];
            $decision_maker['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
            $decision_maker['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        }
        $data['decision_maker'] = $decision_maker;
        // service agreement
        $service_agreement = [];
        $service_agreement['service_agreement_id'] = $serviceAgreementContract['service_agreement_id'];
        $service_agreement['opportunity_id'] = $serviceAgreementContract['opportunity_id'];
        $service_agreement['contract_start_date'] = $serviceAgreementContract['contract_start_date'];
        $service_agreement['contract_end_date'] = $serviceAgreementContract['contract_end_date'];
        $service_agreement['plan_start_date'] = $serviceAgreementContract['plan_start_date'];
        $service_agreement['plan_end_date'] = $serviceAgreementContract['plan_end_date'];
        $service_agreement['additional_services'] = $serviceAgreementContract['additional_services'];
        $service_agreement['additional_services_custom'] = $serviceAgreementContract['additional_services_custom'];
        
        $data['service_agreement'] = $service_agreement;

        // get contact is participant or not
        $person_id = $serviceAgreementContract['account_id'];
        $participant = [];
        $participantContact = false;
        if ($serviceAgreementContract['account_type'] == 1) {
            $participantContact = $this->ServiceAgreement_model->get_participant_contact_by_id( $person_id);
        }
        if ($participantContact == true) {
            $participant['id'] = $serviceAgreementContract['account_id'];
            $participant['name'] = $serviceAgreementContract['account_name'];
            $participant['email'] = $serviceAgreementContract['account_email'];
            $participant['phone'] = $serviceAgreementContract['account_phone'];
            $participant['date_of_birth'] = $serviceAgreementContract['account_date_of_birth'];
            $participant['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];
        }
        $data['participant'] = $participant;

        $data['line_items'] = $SALineItems;
        $data['goals'] = $SAGoals;
        $data['payments'] = $SAPayments;
        $data['additional_funds'] = $SAAdditionalFund;
        // pr($data);

        // Get attachment details
        $to_name = $serviceAgreementContract['to_name'];
        $to_email = $serviceAgreementContract['to_email'];
        $recipient_name = $serviceAgreementContract['recipient_name'];
        $recipient_email = $serviceAgreementContract['recipient_email'];
        $to_account_name = $serviceAgreementContract['account_name'];
        $attachment_type = $serviceAgreementContract['contract_type'];

        // set error reporting 0 to avoid warning and deprecated msg
        error_reporting(0);
        // Load library file
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();
        // set margin type
        $pdf->setAutoTopMargin='pad';
        // get cover page header with background image
        $data['type']='header';
        $header = $this->load->view('service_agreemnet_v3',$data,true);
        // get footer image
        $data['type']='footer';
        $footer = $this->load->view('service_agreemnet_v3',$data,true);
        // get cover page content
        $data['type']='content_1';
        $content_1 = $this->load->view('service_agreemnet_v3',$data,true);
        // get second page header alone logo
        $data['type']='content_2_header';
        $content_2_header = $this->load->view('service_agreemnet_v3',$data,true);
        // get second page footer
        $data['type']='footer_2_content';
        $footer_2_content = $this->load->view('service_agreemnet_v3',$data,true);
        // get second page content
        $data['type']='content_2';
        $content_2 = $this->load->view('service_agreemnet_v3',$data,true);
        // get page style css
        $styleContent = $this->load->view('service_agreemnet_v1_style',[],true);

        // set header & footer line
        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        // set header
        $pdf->SetHeader($header);
        $pdf->SetFooter($footer);
        // set page layout
        $pdf->AddPage('P','','','','',0,0,0,0,0,0);
        // write page content
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($content_1);
        $pdf->SetHeader($content_2_header);
        $pdf->SetFooter($footer_2_content);
        // add additional page with layout        
        $pdf->AddPage('P','','','','',10,10,0,20,5,0);
        // write page content
        $pdf->WriteHTML($content_2);
        // service agreement file path create if not exist
        $fileParDir = FCPATH . SERVICE_AGREEMENT_CONTRACT_PATH;
        if (!is_dir($fileParDir)) {
            mkdir($fileParDir, 0755);
        }
        // create folder with service agreement attachment id
        $fileDir = $fileParDir . $service_agreement_attachment_id;
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755);
        }
        // file name
        $filename = $attachment_type . ' ' . 'Contract unsigned';
        $filenameWithExtension = $filename.'.pdf';
        $pdfFilePath =  $fileDir . '/' . $filenameWithExtension;
        // write file into the folder
        $page_count = $pdf->page;

        $service_agreement_pdf = $pdf->Output($pdfFilePath, 'F');
        // generate & send envelope if file exist
        if(file_exists($pdfFilePath))
        {   
            $contractFileNameWithoutextension = $attachment_type . ' Contract';
            $email_subject = $attachment_type.' Contract';
            $email_content = '';
            // Email subject
            if (isset($email) && isset($email['subject']) && $email['subject'] !='') {
                $email_subject = $email['subject'];
            }
            // Email body content
            if (isset($email) && isset($email['email_content']) && $email['email_content'] !='') {
                $email_content = '<p style="margin: 0px 0px 10px 0px;">'.$to_name.', </p>';
                $email_content .= $email['email_content'];
            }
            // signer recipient id
            $recipient_id = 1;
            
            // Add cc user
            $cc_user = [];
            if (isset($email) && isset($email['cc_email']) && $email['cc_email'] !='') {
                $cc_temp = [];
                // get email details
                $email_cc = $this->ServiceAgreement_model->get_service_agreement_attachment_email_cc($service_agreement_attachment_id);
                if(isset($email_cc) && empty($email_cc) == false) {
                    foreach($email_cc as $cc_index => $email_temp) {
                        $cc_recipient_id = $cc_index + 1;
                        $temp = (array) $email_temp;
                        $cc_temp['name'] = strstr($temp['cc_email'], '@', true);                
                        $cc_temp['email'] = $temp['cc_email'];
                        $cc_temp['recipient_id'] = $recipient_id + $cc_recipient_id;
                        $cc_temp['routing_order'] = 1;
                        $cc_user[] = $cc_temp;        
                    }
                }
            }
            // docugin envelop details
            $data = [
                'userdetails' => [
                    'name' => $to_name,
                    'email_subject' => $email_subject,
                    'email_message' => $email_content,
                    'email' => $to_email,                    
                    'recipient_id' => $recipient_id,
                    'routing_order' => 2,
                ],
                'cc_user' => $cc_user,
                'document' => ['doc_id' => 1 , 'doc_name' => $contractFileNameWithoutextension,  'doc_path' => $pdfFilePath , 'web_hook_url'=>base_url(DS_STAGING_WEBHOOK_URL_SERVICEAGREEMENT)],                
                'position' => [
                    ['position_x' => 81, 'position_y' => 238, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                ],
                'position_text' => [
                    [ 'height'=>30,'width'=>210, 'required'=>'false','max_length'=>1000,'tab_order'=>3,'position_x' => 60, 'position_y' => 403, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id]
                ],
                'position_date_signed' => [
                    ['tab_label'=> 'Date', 'Require_initial'=>'true', 'required'=>'true','name'=>'date_signed','position_x' => 64, 'position_y' => 455, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                    ['tab_label'=> 'Date', 'Require_initial'=>'false', 'required'=>'false','name'=>'date_signed_1','position_x' => 64, 'position_y' => 718, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                ],
                
            ];
           // load docusing evelop
           $this->generate_service_agreement_doc($data, $filenameWithExtension,$service_agreement_attachment_id);    
        } else {
            return [ "status" => false, "error" => "file not being generated" ];
        }
    }

    public function private_travel_service_aggreement_docusign($service_agreement_attachment_id){
        $serviceAgreementContract = $this->ServiceAgreement_model->get_sa_contract_details($service_agreement_attachment_id);
        // return status with false if service agreement not exist
        if (isset($serviceAgreementContract) == false && !isset($serviceAgreementContract['id']) ==false) {
            return [ "status" => false, "error" => "Service Agreement contract not exist" ];
        }

        // gather details
        $service_agreement_id = $serviceAgreementContract['service_agreement_id'];

        // get email details
        $email = $this->ServiceAgreement_model->get_service_agreement_attachment_email($service_agreement_attachment_id);

        // get dynamic data
        $data = $this->ServiceAgreementContract_model->get_dynamic_data_for_contract($service_agreement_id, $serviceAgreementContract, $service_agreement_attachment_id);

        // Get attachment details
        $to_name = $serviceAgreementContract['to_name'];
        $to_email = $serviceAgreementContract['to_email'];
        $attachment_type = $serviceAgreementContract['contract_type'];

        // geneate pdf template
        $pdf_data = $this->ServiceAgreementContract_model->genreatePdfTemplate($data, $service_agreement_attachment_id, $attachment_type);

        $pdfFilePath = $pdf_data['pdfFilePath'];
        $page_count = $pdf_data['page_count'];
        $filenameWithExtension = $pdf_data['filenameWithExtension'];
        // generate & send envelope if file exist
        if(file_exists($pdfFilePath))
        {   
            $contractFileNameWithoutextension = $attachment_type . ' Contract';
            $email_subject = $attachment_type.' Contract';
            $email_content = '';
            // Email subject
            if (isset($email) && isset($email['subject']) && $email['subject'] !='') {
                $email_subject = $email['subject'];
            }
            // Email body content
            if (isset($email) && isset($email['email_content']) && $email['email_content'] !='') {
                $email_content = '<p style="margin: 0px 0px 10px 0px;">'.$to_name.', </p>';
                $email_content .= $email['email_content'];
            }

            // signer recipient id
            $recipient_id = 1;
            
            // Add cc user
            $cc_user = [];
            if (isset($email) && isset($email['cc_email']) && $email['cc_email'] !='') {
                $cc_temp = [];
                // get email details
                $email_cc = $this->ServiceAgreement_model->get_service_agreement_attachment_email_cc($service_agreement_attachment_id);
                if(isset($email_cc) && empty($email_cc) == false) {
                    foreach($email_cc as $cc_index => $email_temp) {
                        $cc_recipient_id = $cc_index + 1;
                        $temp = (array) $email_temp;
                        $cc_temp['name'] = strstr($temp['cc_email'], '@', true);                
                        $cc_temp['email'] = $temp['cc_email'];
                        $cc_temp['recipient_id'] = $recipient_id + $cc_recipient_id;
                        $cc_temp['routing_order'] = 1;
                        $cc_user[] = $cc_temp;        
                    }
                }
            }
            // docugin envelop details
            $data = [
                'userdetails' => [
                    'name' => $to_name,
                    'email_subject' => $email_subject,
                    'email_message' => $email_content,
                    'email' => $to_email,
                    'recipient_id' => $recipient_id,
                    'routing_order' => 2,
                ],
                'cc_user' => $cc_user,
                'document' => ['doc_id' => 1 , 'doc_name' => $contractFileNameWithoutextension,  'doc_path' => $pdfFilePath , 'web_hook_url'=>base_url(DS_STAGING_WEBHOOK_URL_SERVICEAGREEMENT)],                
                'position' => [
                    ['position_x' => 81, 'position_y' => 238, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                ],
                'position_text' => [
                    [ 'height'=>30,'width'=>210, 'required'=>'false','max_length'=>1000,'tab_order'=>3,'position_x' => 60, 'position_y' => 403, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id]
                ],
                'position_date_signed' => [
                    ['tab_label'=> 'Date', 'Require_initial'=>'true', 'required'=>'true','name'=>'date_signed','position_x' => 64, 'position_y' => 455, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                    ['tab_label'=> 'Date', 'Require_initial'=>'false', 'required'=>'false','name'=>'date_signed_1','position_x' => 64, 'position_y' => 718, 'document_id' => 1, 'page_number' => $page_count, 'recipient_id' => $recipient_id],
                ],
                
            ];
            // load docusing evelop
            $this->generate_service_agreement_doc($data, $filenameWithExtension,$service_agreement_attachment_id);    
        } else {
            return [ "status" => false, "error" => "file not being generated" ];
        }
    }


    public function lead_consent_service_aggreement_docusign($service_agreement_attachment_id, $lead_id){
        // gather details for attachment
        $serviceAgreementContract = $this->ServiceAgreement_model->get_sa_attachment_details($service_agreement_attachment_id, $lead_id);

        $obj = new stdClass();
        $obj->value = "";
        $to_select = !empty($serviceAgreementContract)? json_decode($serviceAgreementContract[0]['to_select']) : $obj;
        $lead = $this->Lead_model->get_lead_details($lead_id);
        $to_name = trim($lead['firstname'] . ' ' . $lead['lastname']);
        $to_email = $to_select->value?? trim($lead['email']);
        $recipient_name = $to_name;
        $recipient_email = $to_email;
        $to_account_name = $to_name;
        $attachment_type = 'Consent';
        
        // get email details
        $email = $this->ServiceAgreement_model->get_service_agreement_attachment_email($service_agreement_attachment_id);
        // set error reporting 0 to avoid warning and deprecated msg
        error_reporting(0);
        // Set data for doucment 
        $data['data']['name'] = $recipient_name;
        $data['data']['to'] = $to_account_name;
        // Load library file
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load(['mode' => 'utf-8']);
        // set margin type
        $pdf->setAutoTopMargin='pad';
        // get cover page header with background image
        $data['type']='header';
        $header = $this->load->view('lead_constent_v1',$data,true);
        // get first page content
        $data['type']='content_1';
        $content_1 = $this->load->view('lead_constent_v1',$data,true);
        // get page style css
        $styleContent = $this->load->view('constent_service_agreemnet_v1_style',[],true);
        // set header & footer line
        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        // set header
        $pdf->SetHeader($header);
        // set page layout
        $pdf->AddPage('P','','','','',0,0,0,0,0,0);
        // write page content
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($content_1);
        // service agreement file path create if not exist
        $fileParDir = FCPATH . SERVICE_AGREEMENT_CONTRACT_PATH;
        if (!is_dir($fileParDir)) {
            mkdir($fileParDir, 0755);
        }
        // create folder with service agreement attachment id
        $fileDir = $fileParDir . '/' . $service_agreement_attachment_id;
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755);
        }
        // file name
        $filename = $attachment_type . ' ' . 'Contract unsigned';
        $filenameWithExtension = $filename.'.pdf';
        $pdfFilePath =  $fileDir . '/' . $filenameWithExtension;
        // write file into the folder
        $service_agreement_pdf = $pdf->Output($pdfFilePath, 'F');
        // exit;
        // generate & send envelope if file exist
        if(file_exists($pdfFilePath))
        {   
            $contractFileNameWithoutextension = $attachment_type . ' Contract';
            $email_subject = $attachment_type.' Contract';
            $email_content = '';
            // Email subject
            if (isset($email) && isset($email['subject']) && $email['subject'] !='') {
                $email_subject = $email['subject'];
            }
            // Email body content
            if (isset($email) && isset($email['email_content']) && $email['email_content'] !='') {
                $email_content = '<p style="margin: 0px 0px 10px 0px;">'.$to_name.', </p>';
                $email_content .= $email['email_content'];
            }
            // signer recipient id
            $recipient_id = 1;

            // Add cc user
            $cc_user = [];
            if (isset($email) && isset($email['cc_email']) && $email['cc_email'] !='') {
                $cc_temp = [];
                // get email details
                $email_cc = $this->ServiceAgreement_model->get_service_agreement_attachment_email_cc($service_agreement_attachment_id);
                if(isset($email_cc) && empty($email_cc) == false) {
                    foreach($email_cc as $cc_index => $email_temp) {
                        $cc_recipient_id = $cc_index + 1;
                        $temp = (array) $email_temp;
                        $cc_temp['name'] = strstr($temp['cc_email'], '@', true);                
                        $cc_temp['email'] = $temp['cc_email'];
                        $cc_temp['recipient_id'] = $recipient_id + $cc_recipient_id;
                        $cc_temp['routing_order'] = 1;
                        $cc_user[] = $cc_temp;        
                    }
                }
            }

            // docugin envelop details
            $data = [
                'userdetails' => [
                    'name' => $to_name,
                    'email_subject' => $email_subject,
                    'email_message' => $email_content,
                    'email' => $to_email,
                    'recipient_id' => $recipient_id,
                    'routing_order' => 2,
                ],
                'cc_user' => $cc_user,
                'document' => ['doc_id' => 1 , 'doc_name' => $contractFileNameWithoutextension,  'doc_path' => $pdfFilePath , 'web_hook_url'=>base_url(DS_STAGING_WEBHOOK_URL_SERVICEAGREEMENT)],
                'position' => [
                    ['position_x' => 170, 'position_y' => 542, 'document_id' => 1, 'page_number' => 1, 'recipient_id' => $recipient_id],
                ],
                'position_checkbox' => [
                    ['tab_order'=>1, 'selected'=>false, 'tab_label'=> 'Confirm', 'Require_initial'=>'true', 'required'=>'true','name'=>'Confirm','position_x' => 497, 'position_y' => 490, 'document_id' => 1, 'page_number' => 1, 'recipient_id' => $recipient_id]
                ],
                'position_text' => [
                    [ 'height'=>30,'width'=>260, 'required'=>'false','max_length'=>30,'tab_order'=>3,'position_x' => 205, 'position_y' => 626, 'document_id' => 1, 'page_number' => 1, 'recipient_id' => $recipient_id],
                    // [ 'height'=>30,'width'=>235, 'required'=>false,'max_length'=>30,'tab_order'=>3,'position_x' => 230, 'position_y' => 674, 'document_id' => 1, 'page_number' => 1, 'recipient_id' => $recipient_id]
                ],
                'position_date_signed' => [
                    ['tab_label'=> 'Date', 'Require_initial'=>'true', 'required'=>'true','name'=>'date_signed','position_x' => 120, 'position_y' => 734, 'document_id' => 1, 'page_number' => 1, 'recipient_id' => $recipient_id],
                ],
                
            ];
           // load docusing evelop
           $this->generate_service_agreement_doc($data, $filenameWithExtension,$service_agreement_attachment_id);    
        } else {
            return [ "status" => false, "error" => "file not being generated" ];
        }
    }

}
