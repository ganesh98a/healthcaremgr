<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * class: Contact_model
 * use for query operation of List view controls
 */

//class Master extends MX_Controller
class List_view_controls_model extends CI_Model
{

    public function __construct()
    {

        parent::__construct();
        $this->date_fields = [
            'created',
            'scheduled_start_datetime',
            'scheduled_end_datetime',
            'interview_start_datetime',
            'interview_end_datetime',
            'updated_at',
            'created_at',
            'start_date',
            'end_date',
            'event_date',
            'updated'
        ];
    }

    /*
     * its use for to insert the list view controls based on related type
     *
     * @params
     * type array
     *
     * return type array
     * return id
     */
    public function  create_update_list_view_controls($reqData, $adminId)
    {

        if (!empty($reqData->filter_list_id)) {
            $list_control_data = [
                "list_name" => $reqData->list_name ?? null,
                "user_view_by" => $reqData->user_view_by ?? 0,
                "related_type" => $reqData->related_type,
                "updated_at" => DATE_TIME,
                "archive" => 0,
                "updated_by" => $adminId,
            ];


            $filterId = $this->basic_model->update_records('list_view_controls', $list_control_data, $where = array('id' => $reqData->filter_list_id, 'related_type' => $reqData->related_type));

            $where = array('pinned_id' => $reqData->filter_list_id, 'admin_id!=' => $adminId, 'related_type' => $reqData->related_type, 'archive' => 0);
            $column = array('id', 'pinned_id');
            // check if the data is pinned or not
            $check_pinned = $this->basic_model->get_record_where('list_view_controls_pinned', $column, $where);
            if ($check_pinned) {
                $list_control_data = [
                    "pinned_id" => 0,
                ];
                $this->basic_model->update_records('list_view_controls_pinned', $list_control_data, $where);
            }

            return $filterId;
        } else {
            $list_control_data = [
                "list_name" => $reqData->list_name ?? null,
                "user_view_by" => $reqData->user_view_by ?? 0,
                "related_type" => $reqData->related_type,
                "created_at" => DATE_TIME,
                "archive" => 0,
                "created_by" => $adminId,
            ];

            return  $this->basic_model->insert_records("list_view_controls", $list_control_data, false);
        }
    }

    /*
     * its use for get list control view by related_type
     * return type array
     */

    public function  get_list_view_controls_by_related_type($reqData, $adminId, $uuid_user_type)
    {
     
        if ($uuid_user_type == ORGANISATION_PORTAL) {
            $query = sprintf("SELECT lv.list_name as label,lv.id as value, 'false' as is_created_by_current_user FROM tbl_list_view_controls as lv where lv.user_view_by = 2 and archive=0 and lv.created_by =" . $adminId . " and lv.related_type=" . $reqData->related_type .
            " union
                SELECT lvc.list_name as label,lvc.id as value,
                (CASE WHEN lvc.user_view_by = 1 
                 THEN ('true')
               WHEN lvc.user_view_by != 1 THEN ('false')
                ELSE '' END) as is_created_by_current_user 
                 FROM tbl_list_view_controls as lvc
                where lvc.created_by=%d and archive=0  and lvc.related_type='%s'", $adminId, $this->db->escape_str($reqData->related_type) ); 
        } else {
            $query = sprintf("SELECT lv.list_name as label,lv.id as value, 'false' as is_created_by_current_user FROM tbl_list_view_controls as lv where lv.user_view_by = 2 and archive=0 and lv.created_by!=" . $adminId . " and lv.related_type=" . $reqData->related_type .
                " union
                    SELECT lvc.list_name as label,lvc.id as value,
                    (CASE WHEN lvc.user_view_by = 1 
                    THEN ('true')
                WHEN lvc.user_view_by != 1 THEN ('false')
                    ELSE '' END) as is_created_by_current_user 
                    FROM tbl_list_view_controls as lvc
                    where lvc.created_by=%d and archive=0  and lvc.related_type='%s'", $adminId, $this->db->escape_str($reqData->related_type) ); 
        }
        $query = $this->db->query($query);
        return $query->result();
    }
    /*
     * its use for get list control view by id
     * return type array
     */
    public function  get_list_view_controls_by_id($related_type, $filter_list_id, $adminId)
    {
        $this->db->select(["lv.list_name as label", "lv.id as value", "lv.filter_data", "lv.filter_logic", "lv.filter_operand", "lv.created_by", "lv.user_view_by"]);
        $this->db->from("tbl_list_view_controls as lv");
        $this->db->where(['lv.related_type' => $related_type, 'lv.archive' => 0, 'lv.id' => $filter_list_id]);
        $query = $this->db->get();
        return $query->row();
    }

    /*
     * its use for to update the list view controls
     * based on related type for filter data changes     *
     * @params
     * type array
     *
     * return type array
     * return id
     */
    public function  update_filter_by_id($list_id, $filter_logic, $filter_operand_length, $filter_data, $adminId)
    {

        $list_control_data = [
            "filter_data" => json_encode($filter_data),
            "filter_logic" => $filter_logic,
            "filter_operand" => $filter_operand_length,
            "updated_at" => DATE_TIME,
            "updated_by" => $adminId,
        ];
          $this->basic_model->update_records('list_view_controls', $list_control_data, ["id" => $list_id]);

        return  $this->basic_model->update_records('list_view_controls', $list_control_data, ["id" => $list_id]);
    }

    /*
     * its use for archive filter
     *
     * @params $filter_list_id
     * return type boolean
     * true/false
     */

    public function archive_filter_list($filter_list_id)
    {
        $updated_data = ["archive" => 1];
        return $this->basic_model->update_records("list_view_controls", $updated_data, ["id" => $filter_list_id]);
    }

    /*
     * Update the Pin based on related type
     * @params
     * type array
     *
     * return id
     */
    public function pin_unpin_filter($reqData, $adminId)
    {
        $this->db->select(["p.id", "p.admin_id", "p.pinned_id"]);
        $this->db->from('tbl_list_view_controls_pinned p');
        $this->db->where(['p.archive' => 0, 'p.admin_id' => $adminId, 'p.related_type' => $reqData->related_type]);
        $pinned_data = $this->db->get();
        $pinned_data = $pinned_data->row();
        if (!empty($pinned_data)) {
            $list_control_data = [
                "pinned_id" => $reqData->pin_list_id,
            ];
            $pinnedId = $this->basic_model->update_records('list_view_controls_pinned', $list_control_data, ["id" => $pinned_data->id, "admin_id" => $adminId, "related_type" => $reqData->related_type]);
        } else {
            $list_control_data = [
                "pinned_id" => $reqData->pin_list_id,
                "admin_id" => $adminId,
                "related_type" => $reqData->related_type,
            ];
            $pinnedId = $this->basic_model->insert_records('list_view_controls_pinned', $list_control_data, false);
        }

        return $pinnedId;
    }

    /*
     * Update the default pin based on related type
     * @params
     * type array
     *
     * return id
     */
    public function default_pin_filter($reqData, $adminId)
    {
        if (!empty($reqData->related_type)) {
            $list_control_data = [
                "pinned_id" => 0,
            ];
            $pinnedId = $this->basic_model->update_records('list_view_controls_pinned', $list_control_data, ["related_type" => $reqData->related_type, "admin_id" => $adminId]);
        }

        return $pinnedId;
    }

    /*
     * its use for get list control view by default pinned
     * return type array
     */

    public function  get_list_view_controls_by_default_pinned($reqData, $adminId)
    {
        $this->db->select(["lv.id as value", "lv.list_name as label", "lv.filter_data", "lv.filter_logic", "lv.filter_operand"]);
        $this->db->from('tbl_list_view_controls lv');
        $this->db->where(['lv.archive' => 0, 'lv.created_by' => $adminId, 'lv.related_type' => $reqData->related_type]);
        $query = $this->db->get();
        return $query->row();
    }

    /*
     * check if the list has default pinned
     * return type array
     */

    public function  check_list_has_default_pinned($reqData, $adminId)
    {
        $this->db->select(["p.admin_id", "p.pinned_id"]);
        $this->db->from('tbl_list_view_controls_pinned p');
        $this->db->where(['p.archive' => 0, 'p.admin_id' => $adminId, 'p.related_type' => $reqData->related_type]);
        $query = $this->db->get();
        return $query->row();
    }
    public function get_filter_logic($reqData,$isNeedAssessment=false) {
        $cond = '';
        $filterdatas = array_filter(json_decode(json_encode($reqData->data->tobefilterdata??[]), true));
        $limit = $reqData->data->pageSize ?? 9999;
        $page = $reqData->data->page ?? 0;
        $filter_logic = $reqData->data->filter_logic ?? null;
        $filter_operand_length = $reqData->data->filter_operand_length ?? 0;
        $filter_list_id = $reqData->data->filter_list_id ?? null;
        if (!empty($filter_logic) && !empty($filterdatas)) {
            $filter_logic_result = $this->validate_filter_logic($filter_logic);
            if (!$filter_logic_result) {
                return ["data"=>[] ,"status" => false, 'msg'=>'filter_error' ,"error" => "Invalid filter format"];
            }
            $cond = $this->get_selected_filter_condition($filterdatas, $limit, $filter_logic, $filter_operand_length,$isNeedAssessment);
        }
        if ($reqData->data->save_filter_logic) {
            $this->update_filter_by_id($filter_list_id,$filter_logic,$filter_operand_length,$filterdatas, $reqData->adminId);
        }
        return $cond;
    }
    public function validate_filter_logic($filter_logic)
    {
        $str = $filter_logic;
        $operator_array = array("or", "and");
        $valid_str = array("(", ")", "or", "and");
        $str_temp1 = trim(preg_replace('/\s+/', ' ', $str));
        $str_temp2 = preg_replace('/(?<!\ )[()]/', ' $0', $str_temp1);
        $str_temp = trim(preg_replace('/\s+/', ' ', $str_temp2));

        $res = explode(" ", $str_temp);
        $res = $this->clean_parenthesis($res);

        $valid_parenthesis = 0;
        if (count(array_keys($res, "(")) != count(array_keys($res, ")")))
            return false;

        for ($i = 0; $i < sizeof($res); $i++) {
            $source = strtolower($res[$i]);
            $valid_parenthesis = 0;
            if ((is_numeric($source) && $source <= 10) or in_array($source, $valid_str)) {
                if ($source == "(") {
                    $valid_parenthesis = $this->validate_parenthesis($i + 1, $res);

                    if ($valid_parenthesis == 0)
                        return false;
                }
            } else if (is_numeric($source) and $i > 0) {
                $valid_operand = $this->validate_operand($i, $res, $operator_array);
                if ($valid_operand == 0)
                    return false;
                else if (in_array($source, $operator_array)) {
                    if ($i + 1 < sizeof($res)) {
                        $valid_operator = $this->validate_operator($i, $res);
                        if ($valid_operator == 0)
                            return false;
                    } else
                        return false;
                }
            } else
                return false;
        } //for

        return true;
    }

    public function get_selected_filter_condition($filterdatas, $limit, $filter_logic, $filter_operand_length,$isNeedAssessment=false)
    {
        $forhavingappend = [];
        $whereArrData = [];
        foreach ($filterdatas  as $filterdata) {
            $wherecondition = null;
            $filter_field = $filterdata['select_filter_field_val'];
            $filter_value = $filterdata['select_filter_value'];
            $filter_operator = $filterdata['select_filter_operator'];
            if ($filter_field && $filter_value != '' && $filter_operator) {
                $wherecondition = $this->getoperator($filterdata, $forhavingappend, $filter_logic,$isNeedAssessment);
                array_push($whereArrData, $wherecondition);
                if (empty($filter_logic)) {
                    //$this->db->where($wherecondition);
                }
            }
            //pr($wherecondition);
            if ($filter_field == "created_by") {
                $forhavingappend[$filterdata['select_filter_field_val']][] = $wherecondition;
            }
        }

        // validating the filter logic ex (1 OR 2) AND 3
        $whereLogic =  $filter_logic;
        if (!empty($filter_logic)) {
            if ($filter_operand_length == count($whereArrData)) {
                for ($i = 1; $i <= 10; $i++) {
                    $whereLogic = str_replace($i, "operand-" . $i, $whereLogic);
                }
                for ($i = 1; $i <= 10; $i++) {
                    $searchVal = "operand-" . $i;
                    if ($i <= count($whereArrData)) {
                        $whereLogic = str_replace($searchVal, $whereArrData[$i - 1], $whereLogic);
                    }
                }
            }
        }

        return $whereLogic;
    }

    public function getoperator($filter, $forhavingappend, $filter_logic,$isNeedAssessment=false)
    {
        $cond = null;
        $select_filter_field_val = $filter["select_filter_field_val"];
        $select_filter_value = $filter["select_filter_value"];
        $select_filter_operator = $filter["select_filter_operator"];

        $contact_type_array = array('1' => "Applicant", '2' => "Lead", '3' => "Participant", '4' => "Booker", '5' => "Agent", '6' => "Organisation");
        $status_option_array = array('1' => "Active", '0' => "In Active");
        $interview_invite_type_array = array('1' => "Quiz", '2' => "Meeting Invite");

        // Check Created Date Query
        if  ( in_array($filter["select_filter_field_val"], $this->date_fields) ) {
            $date_operator = $filter["select_filter_operator"];
            $date_opertr_sym = $filter["select_filter_operator_sym"];
            if (isset($filter["selected_date_range"]) && $filter["selected_date_range"] && $filter["selected_date_range"] != "" && !empty($filter["selected_date_range"])) {
                $start_end_date = explode(",", $filter["selected_date_range"]);
                if (count($start_end_date) > 1) {
                    return $this->createdDateConditionsCustomField($filter["select_filter_field_val"], $date_operator, $start_end_date[0], $start_end_date[1], $date_opertr_sym);
                } else {
                    $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter["select_filter_value"])));
                    return $this->createdDateConditionsForSpecificField($filter["select_filter_field_val"], $date_operator, $formated_date, $date_opertr_sym);
                }
            } else {
                $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter["select_filter_value"])));
                return $this->createdDateConditionsForSpecificField($filter["select_filter_field_val"], $date_operator, $formated_date, $date_opertr_sym);
            }
        }

        // Check Type Cast Query

        if ($select_filter_field_val == "type") {
            $type_operator = $filter["select_filter_operator"];
            $type_opertr_sym =  $filter["select_filter_operator_sym"];


            if (in_array($select_filter_value, $contact_type_array)) {
                $select_filter_value = array_search($select_filter_value, $contact_type_array);
            }
            return $this->typeConditions($type_operator, $select_filter_value, $select_filter_field_val, $type_opertr_sym);
        }

        // Check Invite Type Cast Query

        if ($select_filter_field_val == "invite_type") {
            $type_operator = $filter["select_filter_operator"];
            $type_opertr_sym = $filter["select_filter_operator_sym"];
            if (in_array($select_filter_value, $interview_invite_type_array)) {
                $select_filter_value = array_search($select_filter_value, $interview_invite_type_array);
            }
            return $this->interviewInviteTypeConditions($type_operator, $select_filter_value, $select_filter_field_val, $type_opertr_sym);
        }

        // Check Status Types Integer Type Query

        else if ($select_filter_field_val == "status" || $select_filter_field_val == "o.status") {
            $status_operator = $filter["select_filter_operator"];
            $status_opertr_sym = $filter["select_filter_operator_sym"];
            if (in_array($select_filter_value, $status_option_array)) {
                $select_filter_value = array_search($select_filter_value, $status_option_array);
            }
            return $this->statusConditions($status_operator, $select_filter_value, $select_filter_field_val, $status_opertr_sym);
        }

        // Check hired as member or not

        else if ($select_filter_field_val == "hired_as_member") {
            $member_status_operator = $filter["select_filter_operator"];
            $member_status_opertr_sym = $filter["select_filter_operator_sym"];
            return $this->memberStatusConditions($member_status_operator, $select_filter_value, $select_filter_field_val, $member_status_opertr_sym);
        }

        // Check Full Names CONCAT Type Query

        // if ($select_filter_field_val == "fullname") {
        //     $setsymbol = $filter["select_filter_operator_sym"];
        //     $fullname_operator = $filter["select_filter_operator"];
        //     return $this->fullnameConditions($setsymbol, $fullname_operator, $select_filter_value);
        // }

        if ($select_filter_field_val == "created_by") {
            $setsymbol = $filter["select_filter_operator_sym"];
            $creby_operator = $filter["select_filter_operator"];
            return $this->createdByConditions($setsymbol, $creby_operator, $select_filter_value, $forhavingappend, $filter_logic,$isNeedAssessment);
        }


        if($select_filter_field_val == "notification_type") {

            if($select_filter_value == 'in-app' && $select_filter_operator == "equals") {
                $select_filter_operator = "not equal to";
            } else if($select_filter_value == 'in-app' && $select_filter_operator == "not equal to") {
                $select_filter_operator = "equals";
            }
            $select_filter_value = 9;
        }

        $operator =  $select_filter_operator ?? "";
        $filter_value =  $select_filter_value != '' ? $select_filter_value : "";
        $field_val =  $select_filter_field_val ?? "";
        if ($operator && $filter_value != '' && $field_val) {
            return $this->switchCaseOperators($operator, $filter_value, $field_val);
        }
    }

    // validate the custome filed date ex: this week, this month
    public function createdDateConditionsCustomField($field_name, $operator, $start_date, $end_date, $opertr_sym)
    {
        $cond = null;

        if ($operator == "equals" || $operator == "contains" || $operator == "starts with") {
            $cond .= "DATE($field_name) BETWEEN '" . $start_date . "' AND '" . $end_date . "' ";
        } else if ($operator == "not equal to" || $operator == "does not contains") {
            $cond .= "DATE($field_name) NOT BETWEEN '" . $start_date . "' AND '" . $end_date . "' ";
        } else {
            $cond .= "DATE($field_name) " . $opertr_sym . " '" . $start_date . "'";
        }
        return $cond;
    }

    // validate the specific filed date
    public function createdDateConditionsForSpecificField($field_name, $operator, $formated_date, $opertr_sym)
    {
        $cond = null;
        if ($operator == "does not contains") {
            $cond .= "DATE($field_name) NOT LIKE '%" . $formated_date . "'";
        } else if ($operator == "contains") {
            $cond .= "DATE($field_name) LIKE '%" . $formated_date . "'";
        } else if ($operator == "starts with") {
            $cond .= "DATE($field_name) LIKE '" . $formated_date . "%'";
        } else {
            $cond .= "DATE($field_name) " . $opertr_sym . " '" . $formated_date . "'";
        }
        return $cond;
    }

    public function typeConditions($operator, $filter_value, $field_val, $opertr_sym)
    {
        $cond = null;
        if ($operator == "does not contains") {
            $cond .= "(CAST(type AS INTEGER) NOT LIKE '" . $filter_value . "'" . ' OR  ' . "`type` IS NULL)";
        } else if ($operator == "contains" || $operator == "starts with") {
            $cond .= "CAST(type AS INTEGER) LIKE '" . $filter_value . "%'";
        } else if ($operator == "not equal to") {
            $cond .= "(" . $field_val . $opertr_sym . $filter_value . ' OR  ' . "`type` IS NULL" . ")";
        } else {
            $cond .= $field_val . $opertr_sym . $filter_value;
        }
        return $cond;
    }
    // interview invite type
    public function interviewInviteTypeConditions($operator, $filter_value, $field_val, $opertr_sym)
    {
        $cond = null;
        if ($operator == "does not contains") {
            $cond .= "(CAST(invite_type AS INTEGER) NOT LIKE '" . $filter_value . "'" . ' OR  ' . "`invite_type` IS NULL)";
        } else if ($operator == "contains" || $operator == "starts with") {
            $cond .= "CAST(invite_type AS INTEGER) LIKE '" . $filter_value . "%'";
        } else if ($operator == "not equal to") {
            $cond .= "(" . $field_val . $opertr_sym . $filter_value . ' OR  ' . "`invite_type` IS NULL" . ")";
        } else {
            $cond .= $field_val . $opertr_sym . $filter_value;
        }
        return $cond;
    }

    public function createdByConditions($setsymbol, $operator, $filter_value, $forhavingappend, $filter_logic,$isNeedAssessment=false)
    {
        $cond = null;
        $setnotEqual = '';
        $secondpercentage = "%";
        $firstPercenatge = "%";
        if ($operator == "equals") {
            $firstPercenatge = "";
            $secondpercentage = "";
        }
        if ($operator == "does not contains") {
            $setsymbol =  " NOT LIKE ";
            $setnotEqual =   "OR `created_by` IS NULL";
        } else if ($operator == "contains") {
            $setsymbol = " LIKE ";
        } else if ($operator == "starts with") {
            $setsymbol = " LIKE ";
            $firstPercenatge = "";
        } else if ($operator == "not equal to") {
            $setnotEqual =  "OR `created_by` IS NULL";
            $firstPercenatge = "";
            $secondpercentage = "";
        }

        $cond .= "(`created_by` " . $setsymbol . " '" . $firstPercenatge . $filter_value . $secondpercentage . "' " . $setnotEqual . "    )";
        if($isNeedAssessment)
        {
            return $cond;
        }
        if (count($forhavingappend) == 0 && !empty($filter_logic)) {
            //  $cond .= "`p`.`archive` = 0 ON HAVING (`created_by` ".$setsymbol." '".$firstPercenatge.$filter_value.$secondpercentage."' ".$setnotEqual."    )";
            $cond .= " and `p`.`archive` = 0  And (`p`.`created_by` " . $setsymbol . " '" . $firstPercenatge . $filter_value . $secondpercentage . "' " . $setnotEqual . "    )";
        } else if (count($forhavingappend) == 0) {
            //  $cond .= "`p`.`archive` = 0  HAVING(`created_by` ".$setsymbol." '".$firstPercenatge.$filter_value.$secondpercentage."' ".$setnotEqual."    )";
            $cond .= " and `p`.`archive` = 0  And (`p`.`created_by` " . $setsymbol . " '" . $firstPercenatge . $filter_value . $secondpercentage . "' " . $setnotEqual . "    )";
        } else {
            $cond .= "(`created_by` " . $setsymbol . " '" . $firstPercenatge . $filter_value . $secondpercentage . "' " . $setnotEqual . "    )";
        }
        return $cond;
    }
    public function fullnameConditions($setsymbol, $operator, $filter_value)
    {
        $cond = null;
        $secondpercentage = "%";
        $firstPercenatge = "%";
        if ($operator == "equals") {
            $firstPercenatge = "";
            $secondpercentage = "";
        }
        if ($operator == "does not contains") {
            $setsymbol =  " NOT LIKE ";
        }
        if ($operator == "contains") {
            $setsymbol = " LIKE ";
        }
        if ($operator == "starts with") {
            $setsymbol = " LIKE ";
            $firstPercenatge = "";
        }
        if ($operator == "not equal to") {
            $firstPercenatge = "";
            $secondpercentage = "";
        }
        $cond .= "CONCAT(firstname,' ',lastname)" . $setsymbol  . "'" . $firstPercenatge . $filter_value . $secondpercentage . "'";
        return $cond;
    }
    public function statusConditions($operator, $filter_value, $field_val, $opertr_sym)
    {
        $cond = null;
        if ($operator == "does not contains") {
            $cond .= "(CAST(status AS INTEGER) NOT LIKE '" . $filter_value . "'" . ' OR  ' . "`status` IS NULL)";
        } else if ($operator == "contains" || $operator == "starts with") {
            $cond .= "CAST(status AS INTEGER) LIKE '" . $filter_value . "%'";
        } else if ($operator == "not equal to") {
            $cond .= "(" . $field_val . $opertr_sym . $filter_value . ' OR  ' . "`status` IS NULL" . ")";
        } else {
            $cond .= $field_val . $opertr_sym . $filter_value;
        }
        return $cond;
    }

    public function memberStatusConditions($operator, $filter_value, $field_val, $opertr_sym)
    {
        $member_filter_value = '1';
        $cond = null;
        if($filter_value=='No'){
            $member_filter_value = '0';
            $member_filter_value_in_active = '2';
            if ($operator == "does not contains") {
                $cond .= "(CAST(hired_as AS INTEGER) NOT LIKE '" . $member_filter_value . "'" . " OR (CAST(hired_as AS INTEGER) NOT LIKE '" . $member_filter_value_in_active . "'"  . ' OR  ' . "`hired_as` IS NULL)";
            } else if ($operator == "contains" || $operator == "starts with") {
                $cond .= "CAST(hired_as AS INTEGER) LIKE '" . $member_filter_value . "%'" . " OR CAST(hired_as AS INTEGER) LIKE '" . $member_filter_value_in_active . "%'";
            } else if ($operator == "not equal to") {
                $cond .= "(" . $field_val . $opertr_sym . $member_filter_value . ' and  ' . $field_val . $opertr_sym . $member_filter_value_in_active . ' OR  ' . "`hired_as` IS NULL" . ")";
            } else {
                $cond .= "(" .$field_val . $opertr_sym . $member_filter_value . ' OR ' . $field_val . $opertr_sym . $member_filter_value_in_active . ")";
            }
        }else{
            if ($operator == "does not contains") {
                $cond .= "(CAST(hired_as AS INTEGER) NOT LIKE '" . $member_filter_value . "'" . ' OR  ' . "`hired_as` IS NULL)";
            } else if ($operator == "contains" || $operator == "starts with") {
                $cond .= "CAST(hired_as AS INTEGER) LIKE '" . $member_filter_value . "%'";
            } else if ($operator == "not equal to") {
                $cond .= "(" . $field_val . $opertr_sym . $member_filter_value . ' OR  ' . "`hired_as` IS NULL" . ")";
            } else {
                $cond .= $field_val . $opertr_sym . $member_filter_value;
            }
        }

        return $cond;
    }

    //Switch Cases For Operators
    public function switchCaseOperators($operator, $filter_value, $field_val)
    {
        $cond = null;
        switch ($operator) {
            case 'equals':
                $cond .= $field_val . ' = "' . $filter_value . '"';
                break;
            case 'not equal to':
                $cond .= $field_val . ' <> "' .  $filter_value . '"';
                break;
            case 'less than':
                $cond .= $field_val . ' < "' .  $filter_value . '"';
                break;
            case 'greater than':
                $cond .= $field_val . ' > "' .  $filter_value . '"';
                break;
            case 'less or equal':
                $cond .= $field_val . ' <= "' .  $filter_value . '"';
                break;
            case 'greater or equal':
                $cond .= $field_val . ' >= "' .  $filter_value . '"';
                break;
            case 'contains':
                $cond .= $field_val . " LIKE '%" . $filter_value . "%'";
                break;
            case 'does not contains':
                $cond .= $field_val . " NOT LIKE '%" . $filter_value . "%'";
                break;
            case 'starts with':
                $cond .= $field_val . " LIKE '" . $filter_value . "%'";
                break;
            default:
                break;
        }
        return $cond;
    }

    /**
     * get the related to type id
     * @param {str} related_type
     */
    public function give_id_of_related_type($related_type)
    {
        $source_data_type = array(
            'opportunity' => 1,
            'lead' => 2,
            'service' => 3,
            'need' => 4,
            'risk' => 5,
            'shift' => 6
        );
        return $source_data_type[$related_type];
    }

    // Validate the filter logic for parenthesis
    public function validate_parenthesis($index, $source_str)
    {
        for ($j = $index; $j < sizeof($source_str); $j++) {
            $index_actual = $j + 1;
            if ($source_str[$j] == ")" && (($index_actual - $index) % 4) == 0) {
                return 1;
            }
        } //for J
        return 0;
    }

    public function clean_parenthesis($source_arr)
    {
        $source_temp = $source_arr;
        $start_index = 0;
        $end_index = 0;
        for ($k = 0; $k < sizeof($source_arr); $k++) {
            // check whether we found parenthesis with chars without spaces
            if ((strpos($source_arr[$k], "(") !== false or strpos($source_arr[$k], ")") !== false) and strlen($source_arr[$k]) > 1) {
                $index = 1;
                $end_index++;
                $delimeter = "(";
                if (strpos($source_arr[$k], "(") !== false) {
                    $delimeter = "(";
                    $index = 1;
                } else if (strpos($source_arr[$k], ")") !== false) {
                    $delimeter = ")";
                    $index = 0;
                }

                //split the chars from ) or )
                $par_array_temp = explode($delimeter, $source_arr[$k]);

                // form the correct string with parenthesis
                if (strpos($source_arr[$k], ")") !== false) {
                    if (!empty($par_array_temp[1]))
                        $par_array = array($par_array_temp[$index], $delimeter, $par_array_temp[1]);
                    else
                        $par_array = array($par_array_temp[$index], $delimeter);
                } else {

                    if (!empty($par_array_temp[0]))
                        $par_array = array($par_array_temp[0], $delimeter, $par_array_temp[$index]);
                    else
                        $par_array = array($delimeter, $par_array_temp[$index]);
                }

                $temp_end = array_slice($source_temp, $k + $end_index, sizeof($source_temp), true);

                if ($k == 0 and strpos($source_arr[$k], ")") == false)
                    $res_array = array_merge($par_array, $temp_end);
                //if Paraenthesis at mid of the string
                else {
                    $temp_start = array_slice($source_temp, 0, $k + $start_index, true);
                    $res_array_1 = array_merge($temp_start, $par_array);
                    $res_array = array_merge($res_array_1, $temp_end);
                }
                $source_temp = $res_array;

                $start_index++;
            }
        }
        return $source_temp;
    }

    // Validate the filter logic for operand
    public function validate_operand($index, $source_str, $operator_array)
    {
        $next_pos = $index + 1;
        $prev_pos = $index - 1;
        if ($next_pos < sizeof($source_str)) {
            if (in_array($source_str[$next_pos], $operator_array) || in_array($source_str[$prev_pos], $operator_array) || $source_str[$next_pos] == "(")
                return 1;
            else
                return 0;
        } else {
            if (in_array($source_str[$prev_pos], $operator_array))
                return 1;
            else
                return 0;
        }
    }

    // Validate the filter logic for operator
    public function validate_operator($index, $source_str)
    {
        $next_pos =  $index + 1;
        $prev_pos = $index - 1;
        if (($source_str[$next_pos] == "(" or  $source_str[$prev_pos] == ")" or  is_numeric($source_str[$next_pos]) or is_numeric($source_str[$prev_pos])) and $next_pos !=  sizeof($source_str))
            return 1;
        else {

            return 0;
        }
    }
}
