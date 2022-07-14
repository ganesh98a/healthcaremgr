<?php

require_once APPPATH . 'Classes/admin/permission.php';
require_once APPPATH . 'Classes/KeyPay.php';
require_once APPPATH . '../vendor/ihor/nspl/autoload.php';
require_once APPPATH . '../vendor/autoload.php';

use function nspl\a\zip;
use MathPHP\Statistics\Distance;
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_model extends Basic_Model
{

    var $hourly_times = ["00:00", "01:00", "02:00", "03:00", "04:00", "05:00", "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00", "23:00"];
    var $half_hourly_times = [];

    public function __construct()
    {
        $this->table_name = 'member';
        $this->load->model('sales/Contact_model');
        $this->load->model('common/Common_model');
        $this->object_fields['firstname'] = "First Name";
        $this->object_fields['lastname'] = "Last Name";
        $this->object_fields['fullname'] = "Name";
        $this->object_fields['Contact'] =   [
                                                'field' => 'person_id',
                                                'object_fields' => $this->Contact_model->getObjectFields()
                                            ];
        $this->object_fields['hours_per_week'] = "Hours Per Week";
        $this->object_fields['mem_experience'] = "Experience";
        $this->object_fields['status'] = "Status";
        $this->object_fields['email'] = function ($memberId = '') {
                                                                        if (empty($memberId)) {
                                                                            return 'Email';
                                                                        }
                                                                        $result = $this->get_record_where('member', 'username', ['id' => $memberId, 'archive' => '0']);
                                                                        return $result[0]->username;
                                                                    };
        parent::__construct();
    }

    /**
     * generating half hourly time series starting from 12:00AM, 12:30AM ... 11:30PM
     */
    public function get_half_hourly_times($numericIndex = true)
    {
        $data = null;
        $time = '23:30'; // start
        for ($i = 0; $i <= 47; $i++) {
            $next = strtotime('+30mins', strtotime($time)); // add 30 mins
            $time = date('g:i A', $next); // format the next time
            if ($numericIndex) {
                $data[] = $time;
            } else {
                $key = date('H:i:s', $next);
                $data[$key] = $time;
            }
        }
        return $data;
    }

    /**
     * returns the value and label array of half hourly time series
     */
    public function get_time_slots_half_hour($numericIndex = true)
    {
        $data = null;
        foreach ($this->get_half_hourly_times($numericIndex) as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            if ($numericIndex) {
                $newrow['value'] = ($value + 1);
            } else {
                $newrow['value'] = $value;
            }
            $data[] = $newrow;
        }
        return $data;
    }

    /**
     * fetching the members list for assigning to a shift
     */
    public function get_members_for_shift($reqData, $adminId) {
        $this->load->library('AmazonLambda');
        if (empty($reqData)) return;

        # validating age range is it is specified
        $errors = null;
        if (isset($reqData['age_range_only']) && $reqData['age_range_only']) {
            $reqData['age_from'] = (int)($reqData['age_from']);
            $reqData['age_to'] = (int)($reqData['age_to']);
            if(!($reqData['age_from'] >= 0))
                $errors[] = "Please enter Age From";
            if(!($reqData['age_to'] >= 0))
                $errors[] = "Please enter Age To";
            if(!$errors && $reqData['age_from'] > $reqData['age_to'])
                $errors[] = "Age From should be lower than or equal to Age To";
            
            if($errors) {
                $response = [
                    "status" => false,
                    "error" => implode(",", $errors)
                ];
                return $response;
            }
        }

        # is the object being altered currently by other user? if yes - cannot perform this action
        $lock_data['object_type'] = "shift";
        $lock_data['object_id'] = $reqData['shift_id'];
        $lock_taken = $this->Common_model->get_take_access_lock($lock_data, $adminId);
        if($lock_taken['status'] == false)
        return $lock_taken;

        $this->load->model('schedule/Schedule_model');
        $shift_result = $this->Schedule_model->get_shift_details($reqData['shift_id']);
        $shift_details = $shift_result['data'];

        $src_columns = array("m.fullname", "(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),p.date_of_birth)), '%Y')+0)", "rf.display_name", "rf1.display_name");

        $orderBy = 'm.fullname';
        $direction = 'ASC';

        # text search
        if (!empty($reqData['srch_box'])) {
            $search_key = $this->db->escape_str($reqData['srch_box'], TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== FALSE) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        

        $select_column = array('m.id', '(CASE WHEN am.id > 0 THEN 1 ELSE 0 END) as is_preferred', 'm.fullname', 'm.max_dis_to_travel', 'm.person_id');
        $this->db->select($select_column);
        $this->db->select("(select id from tbl_shift_member sm where sm.shift_id = " . $reqData['shift_id'] . " and m.id = sm.member_id and sm.archive = 0) as selected");

        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join('tbl_person p', 'm.person_id = p.id', 'inner');
        
       // $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"', 'inner');

        # filters of mandatory skills
        if (isset($reqData['mandatory_skills_only']) && $reqData['mandatory_skills_only']) {
            $this->db->from('tbl_member_skill as ms');
            $this->db->join('tbl_shift_skills sk', 'ms.skill_id = sk.skill_id and ms.member_id = m.id and ms.archive = 0 and sk.archive = 0  and sk.condition = 1 and ms.start_date <= \''.date("Y-m-d H:i:s").'\' and ms.end_date >= \''.date("Y-m-d H:i:s").'\'', 'inner');
        }
        else {
            $this->db->join('tbl_member_skill ms', 'ms.member_id = m.id and ms.archive = 0', 'left');
        }
        $this->db->join('tbl_references rf1', 'p.gender = rf1.id', 'left');
        $this->db->join('tbl_references rf', 'ms.skill_id = rf.id', 'left');
        $this->db->where("m.archive", "0");

        # members have to match their roles with shift role 
        if (isset($shift_details->role_id) == true && $shift_details->role_id != '') {
            $this->db->join('tbl_member_role_mapping as mrm', "mrm.archive = 0 and mrm.member_id = m.id and mrm.member_role_id = " . $shift_details->role_id, 'inner');
            $startTime = date("Y-m-d h:i:s");
            $endTime = date("Y-m-d h:i:s");
            // check role expiried or not
            $this->db->where("mrm.start_time <= '" . $startTime . "'
                AND
                mrm.end_time >= CASE
                    WHEN mrm.end_time != '0000-00-00 00:00:00' THEN '" . $endTime . "'
                    ELSE
                        '0000-00-00 00:00:00'
                    END
            ");
        }

        # not including non preferred members in the match
        if ($shift_details->account_type == 1) {
            $this->db->join('tbl_participant_member as am', "am.member_id = m.id and am.archive = 0 and am.participant_id = " . $shift_details->account_id, 'left');

            $this->db->where("m.id not in (select am.member_id from tbl_participant_member am inner join tbl_references rf2 on am.status = rf2.id and rf2.display_name = 'Do Not Use' and am.archive = 0 and am.participant_id = " . $shift_details->account_id . ")");
        } else if ($shift_details->account_type == 2) {
            $this->db->join('tbl_organisation_member as am', "am.member_id = m.id and am.archive = 0 and am.organisation_id = " . $shift_details->account_id, 'left');

            $this->db->where("m.id not in (select am.member_id from tbl_organisation_member am inner join tbl_references rf2 on am.status = rf2.id and am.archive = 0 and rf2.display_name = 'Do Not Use' and am.organisation_id = " . $shift_details->account_id . ")");
        }

        # only including preferred members
        if (isset($reqData['preferred_only']) && $reqData['preferred_only']) {
            $this->db->where("am.id is not null");
        }

        # only including certain gender specific members
        if ((isset($reqData['male_only']) && $reqData['male_only']) && isset($reqData['female_only']) && $reqData['female_only']) {
            $this->db->join('tbl_references rf4', 'p.gender = rf4.id and rf4.key_name in (\'male\',\'female\')', 'inner');
        }
        else if (isset($reqData['male_only']) && $reqData['male_only']) {
            $this->db->join('tbl_references rf4', 'p.gender = rf4.id and rf4.key_name = \'male\'', 'inner');
        }
        else if(isset($reqData['female_only']) && $reqData['female_only']) {
            $this->db->join('tbl_references rf4', 'p.gender = rf4.id and rf4.key_name = \'female\'', 'inner');
        }

        # only including age specific members only
        if (isset($reqData['age_range_only']) && $reqData['age_range_only']) {
            $this->db->where("(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),p.date_of_birth)), '%Y')+0) >= {$reqData['age_from']} and (DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),p.date_of_birth)), '%Y')+0) <= {$reqData['age_to']} and p.date_of_birth != '0000-00-00' and p.date_of_birth is not null");
        }

        $this->db->group_by('m.id');
        $this->db->order_by($orderBy, $direction);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $members = $query->result();
        $dataResult = array();
        $p_ids = [];
        foreach ($members as $val) {
            //collect person id against member id
            $p_ids[$val->id] = $val->person_id;
        }
        $member_distances = []; 
        if (!empty($p_ids)) {
            $this->db->select(['p.id', "pe.email", 'concat(pa.street," ",pa.suburb," ",st.name," ",pa.postcode) as fulladdress', 'rf1.display_name as gender_label', '(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),p.date_of_birth)), \'%Y\')+0) as age']);
            $this->db->from('tbl_person p');        
            $this->db->join('tbl_person_address pa', 'pa.person_id = p.id and pa.archive = 0', 'left');
            $this->db->join('tbl_state st', 'pa.state = st.id', 'left');
            $this->db->join('tbl_person_email pe', 'pe.person_id = p.id and pe.primary_email = 1 and pe.archive = 0', 'left');
            $this->db->join('tbl_references rf1', 'p.gender = rf1.id', 'left');
            $this->db->where_in('p.id', array_values($p_ids));
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $rows = $query->result();
            $person_data = [];
            foreach($rows as $row) {
                $person_data[$row->id] = $row;
            }
            foreach($members as $row) {
                $p = $person_data[$row->person_id];
                $row->email = $p->email;
                $row->fulladdress = $p->fulladdress;
                $row->gender_label = $p->gender_label;
                $row->age = $p->age;
                $row->address1 = $row->fulladdress;
                $row->address2 = $shift_details->owner_address;
            }
            $member_distances = getDistanceBetweenTwoAddresses($members);
        }
        if (!empty($members)) {
            $already_assigned = false;
            foreach ($members as $val) {                
                $row = $val;
                if (isset($row->selected) && !empty($row->selected)) {
                    $already_assigned = true;
                }
                $row->duration_label = $row->distance_label = '';
                $row->distance = $member_distances[$row->id][0];
                $row->duration = $member_distances[$row->id][1];

                $distance_vector = -1;
                if ($row->duration > 0)
                    $row->duration_label = " (" . get_hour_minutes_from_int($row->duration) . ")";

                if (@$row->distance && $row->distance >= 0) {
                    $row->distance_label = $row->distance . " km" . $row->duration_label;
                    $distance_vector = $row->distance;
                    $row->duration_label = " (".get_hour_minutes_from_int($row->duration).")";
                }

                # fetching member's skills set as comma separated
                $row->skills = $this->get_member_skills_as_string($row->id);

                # checking member availability using unavailability provided, overtime rules and shifts distance rules
                $member_available_det = $this->Schedule_model->is_member_available_between_datetimes($shift_details->id, $val->id, $shift_details->scheduled_start_datetime, $shift_details->scheduled_end_datetime, $shift_details->owner_address);

                $row->is_available = 1;
                $row->not_available_reason = '';
                if ($member_available_det['status'] == false) {
                    $row->is_available = 0;
                    $row->not_available_reason = $member_available_det['error'];
                }
                if ($row->distance > 0 && $row->max_dis_to_travel && $row->distance > $row->max_dis_to_travel) {
                    $row->is_available = 0;
                    $row->not_available_reason = "Distance to travel is greater than max distance travel by member";
                }
                $member_vector = $this->get_member_vector($reqData['mandatory_skills_only'],$row->is_available,intval($row->is_preferred),$distance_vector,$row->id,$reqData['shift_id'],$row->max_dis_to_travel, $reqData['male_only'], $reqData['female_only'], $reqData['age_range_only'], $reqData['age_from'], $reqData['age_to'], $row->age);
               
                // $row->rank = round($this->amazonlambda->lambdaShift($member_vector),4); Comment out lambda funciton going outside the environment is too slow.
                // Get manhattan distance
                $row1 = $member_vector;
                $row2 = array (1,1,0,1); // These are set to 1 as the vector is based on the weightings all adding up to 100% e.g. 
                $pairs = zip($row1, $row2);
                $row->rank = Distance::manhattan($row1, $row2);
                $row->member_vector = $member_vector;
                $row->distance_vector = $distance_vector;

                if (isset($reqData['available_only']) && $reqData['available_only']) {
                    if ($row->is_available)
                        $dataResult[] = $row;
                } else if (isset($reqData['within_distance']) && $reqData['within_distance']) {
                    if ($row->distance > 0 && $row->max_dis_to_travel && $row->distance <= $row->max_dis_to_travel)
                        $dataResult[] = $row;
                    else if (!$row->distance || !$row->max_dis_to_travel)
                        $dataResult[] = $row;
                } else {
                    $dataResult[] = $row;
                }
            }
        }
        usort($dataResult, $this->make_comparer('rank'));

        $return = array('data' => $dataResult, 'status' => true);
        return $return;
    }

    /**
     * fetching the member key pay id
     */
    public function get_member_key_pay_id($member_id) {
        $row = $this->basic_model->get_row('keypay_kiosks_emp_mapping_for_member', ['keypay_emp_id', 'id'], ['member_id' => $member_id, 'archive' => 0]);
        return ($row && isset($row->id)) ? $row->id : '';
    }

    /**
     * Form member vector  to find the rank
     */
    public function get_member_vector($is_mandatory_skills, $is_available, $is_preferred, $distance, $member_id, $shift_id, $max_travel, $is_male, $is_female, $is_age_range, $age_from, $age_to, $age)
    {
        # calculating skills vector
        $skill_vector_temp = 0;
        
        $this->db->select('count(ms.skill_id) as match_count');
        $this->db->from('tbl_member_skill as ms');
        if($is_mandatory_skills)
          $this->db->join('tbl_shift_skills sk', 'ms.skill_id = sk.skill_id and sk.condition = 1 and ms.start_date <= \''.date("Y-m-d H:i:s").'\' and ms.end_date >= \''.date("Y-m-d H:i:s").'\'', 'inner');
        else
            $this->db->join('tbl_shift_skills sk', 'ms.skill_id = sk.skill_id  and ms.start_date <= \''.date("Y-m-d H:i:s").'\' and ms.end_date >= \''.date("Y-m-d H:i:s").'\'', 'inner');
        $this->db->where('ms.member_id', $member_id);
        $this->db->where('ms.archive', 0);
        $this->db->where('sk.shift_id', $shift_id);
        $this->db->where('sk.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        $skill_match = !empty($result) ? $result[0]->match_count : 0;
        $this->db->select('count(skill_id) as skill_count');
        $this->db->from('tbl_shift_skills');
        $this->db->where('shift_id', $shift_id);
        $this->db->where('archive', 0);
        $query_skill = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result_skill = $query_skill->result();
        $skill_count = !empty($result) ? $result_skill[0]->skill_count : 0;
        $skill_count = intval($skill_count);
        if ($skill_count > 0)
            $skill_vector_temp = floatval($skill_match / $skill_count);
        
        $skill_vector_temp = $skill_vector_temp * SKILL_WEIGHT;
        $skill_vector = floatval($skill_vector_temp / 100);

        # calculating gender vector
        $igender_vector_temp = 0;
        if($is_female || $is_male) {
            $igender_vector_temp = 1 * GENDER_WEIGHT;
        }
        $gender_vector = floatval($igender_vector_temp / 100);

        # calculating age vector
        $age_vector_temp = 0;
        if($is_age_range && $age >= $age_from && $age <= $age_to) {
            $age_vector_temp = 1 * AGE_WEIGHT;
        }
        $age_vector = floatval($age_vector_temp / 100);

        # calculating distance vector
        $distance_vector = $distance;
        if ($distance > 0) {

            $distance_vector = floatval($distance / 50);
            if ($distance_vector > 1)
                $distance_vector = 1;
        } else if ($distance == 0)
            $distance_vector = 0;
        else if ($distance == -1)
            $distance_vector = 2;
        $distance_vector_temp = $distance_vector * DISTANCE_WEIGHT;
        $distance_vector_new = floatval($distance_vector_temp / 100);

        # calculating is preferred vector
        $is_preferred_temp = $is_preferred * PREFERRED_WEIGHT;
        $is_preferred_vector = floatval($is_preferred_temp / 100);

        # calculating is available vector
        $is_available_temp = $is_available * AVAILABLE_WEIGHT;
        $is_available_vector = floatval($is_available_temp / 100);
        return array($is_preferred_vector, round($skill_vector, 2), round($distance_vector_new, 4), $is_available_vector);
    }

    /**
     * fetching member's skills set as comma separated
     */
    public function get_member_skills_as_string($member_id)
    {
        $this->db->select(['GROUP_CONCAT(rf.display_name) as skills']);
        $this->db->from('tbl_member_skill as ms');
        $this->db->join('tbl_references rf', 'ms.skill_id = rf.id', 'inner');
        $this->db->where('ms.archive', 0);
        $this->db->where('ms.member_id', $member_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return (!empty($result) ? $result[0]->skills : '');
    }

    function make_comparer()
    {
        // Normalize criteria up front so that the comparer finds everything tidy
        $criteria = func_get_args();
        foreach ($criteria as $index => $criterion) {
            $criteria[$index] = is_array($criterion)
                ? array_pad($criterion, 3, null)
                : array($criterion, SORT_ASC, null);
        }



        return function ($first, $second) use (&$criteria) {
            foreach ($criteria as $criterion) {
                // How will we compare this round?
                list($column, $sortOrder, $projection) = $criterion;
                $sortOrder = $sortOrder === SORT_DESC ? -1 : 1;



                // If a projection was defined project the values now
                if ($projection) {
                    $lhs = call_user_func($projection, $first->$column);
                    $rhs = call_user_func($projection, $second->$column);
                } else {
                    $lhs = $first->$column;
                    $rhs = $second->$column;
                }



                // Do the actual comparison; do not return if equal
                if ($lhs < $rhs) {
                    return -1 * $sortOrder;
                } else if ($lhs > $rhs) {
                    return 1 * $sortOrder;
                }
            }



            return 0; // tiebreakers exhausted, so $first == $second
        };
    }

    /**
     * checks if any member's unavailability is falling between shift and its timings
     */
    function check_unavailability_provided($member_id, $start_datetime, $end_datetime, $shift_id)
    {
        $start_datetime = $this->db->escape_str($start_datetime);
        $end_datetime = $this->db->escape_str($end_datetime);
        
        $select_column = ["mu.id"];
        $this->db->select($select_column);
        $this->db->from('tbl_member_unavailability as mu');
        $this->db->where('mu.archive', 0);
        $this->db->where('mu.member_id', $member_id);
        $this->db->where("((mu.start_date >= '{$start_datetime}' and mu.start_date <= '{$end_datetime}') or (mu.end_date >= '{$start_datetime}' and mu.end_date <= '{$end_datetime}') or (mu.start_date <= '{$start_datetime}' and mu.end_date >= '{$end_datetime}'))");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if ($result) {
            return ["status" => false, "error" => "Unavailability provided"];
        }
        return ["status" => true, "msg" => "member available"];
    }
    /**
     * fetches list of member lists
     */
    public function members_list_new($reqData, $adminId, $filter_condition = '') {
        $limit = sprintf("%d", $reqData->pageSize) ?? 9999;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = 'm.id';
        $direction = 'DESC';
        $src_columns = array("m.id", "m.fullname","m.hours_per_week", "pe.email", "p.firstname", "p.lastname","CONCAT(p.firstname,' ',p.lastname)", 'DATE(m.created)');

        # text search
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];     
                    $formated_date = '';
                    if($column_search=='DATE(m.created)'){
                        $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $search_key)));
                        if (strstr($column_search, "as") !== FALSE) {
                            $serch_column = explode(" as ", $column_search);
                            if ($serch_column[0] != 'null')
                                $this->db->or_like($serch_column[0], $formated_date);
                        }
                        else if ($column_search != 'null') {
                            $this->db->or_like($column_search, $formated_date);
                        }
                    }else{
                        if (strstr($column_search, "as") !== FALSE) {
                            $serch_column = explode(" as ", $column_search);
                            if ($serch_column[0] != 'null')
                                $this->db->or_like($serch_column[0], $search_key);
                        }
                        else if ($column_search != 'null') {
                            $this->db->or_like($column_search, $search_key);
                        }
                    }   
                }
                $this->db->group_end();
            }
        }
        // get entity type value
        $select_column = array('m.id','m.uuid', 'm.fullname', 'm.hours_per_week', 'm.person_id', 'm.department', 'm.created','m.status','pe.email');

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("(case when m.person_id > 0 THEN
            (SELECT CONCAT(p.firstname,' ',p.lastname) from tbl_person p where p.id = m.person_id) ELSE '' END) as contact_name", false);
        $this->db->select("(
                CASE WHEN m.status = 1 THEN 'Active'
                ELSE 'Inactive' END) as status");

        
        $this->db->select("(
            CASE WHEN m.status = 1 and u.password_token is NULL THEN ' '
                 WHEN m.status = 1 and u.password_token is not NULL THEN 'Active'
                 WHEN m.status = 0 and u.password_token is NULL THEN ' '
                 WHEN m.status = 0  THEN 'Inactive'
                 ELSE ' ' END) as member_portal_status");

        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join('tbl_person p', 'm.person_id = p.id', 'inner');
        $this->db->join('tbl_person_email pe', 'pe.person_id = p.id', 'left');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"', 'inner');
        $this->db->join('tbl_users u', 'u.id = m.uuid', 'inner');

        $this->db->group_by('m.id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where("m.archive", "0");
       
        //list view filter condition
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
        return $return;
    }

    public function member_list($reqData)
    {
        if (!empty($reqData)) {
            $reqData = json_decode($reqData->data->request);

            $limit = sprintf("%d", $reqData->pageSize) ?? 0;
            $page = sprintf("%d", $reqData->page) ?? 0;
            $sorted = $reqData->sorted;
            $filter = $reqData->filtered;
            $orderBy = '';
            $direction = '';

            $src_columns = array("tbl_member.id", "tbl_member.firstname", "tbl_member.lastname", "tbl_member_phone.phone", "tbl_member_email.email");
            $available_column = array('OCS_id', 'firstname', 'lastname', 'street', 'city', 'postal', 'state', 'phone', 'gender');
            if (!empty($sorted)) {
                if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                    $orderBy = $sorted[0]->id == 'id' ? 'tbl_member.id' : $sorted[0]->id;
                    $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                }
            } else {
                $orderBy = 'tbl_member.id';
                $direction = 'DESC';
            }

            $where = '';
            // set here defualt department for show only field type admin
            $where = "tbl_member.archive = 0";
            $sWhere = $where;


            if (!empty($filter)) {
                if (isset($filter->include_inactive) && $filter->include_inactive) {
                    $status_con = "0,1";
                    $this->db->where_in('tbl_member.status', $status_con, false);
                } else {
                    $status_con = 1;
                    $this->db->where_in('tbl_member.status', $status_con);
                }

                if (isset($filter->filterByType) && $filter->filterByType == 1) {
                    $this->db->join('tbl_shift_member', 'tbl_shift_member.memberId = tbl_member.id AND tbl_shift_member.status = 3 AND tbl_shift_member.archive = 0', 'INNER');
                    $this->db->join('tbl_shift', 'tbl_shift.id = tbl_shift_member.shiftId', 'INNER');
                    $this->db->where_in('tbl_shift.status', array(1, 2, 3, 7));
                    $this->db->where('tbl_shift.shift_date >=', date("Y-m-d"));
                } else if (isset($filter->filterByType) && $filter->filterByType == 2) {
                    $this->db->where_not_in('tbl_member.id', "SELECT  tbl_member.id FROM tbl_member
                      LEFT JOIN tbl_shift_member ON tbl_shift_member.memberId = tbl_member.id AND tbl_shift_member.status = 1
                      LEFT JOIN tbl_shift ON tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift.status IN(1, 2, 3, 7)
                      WHERE tbl_member.status = 1 AND tbl_shift.shift_date = '" . date('Y-m-d') . "' AND tbl_member.archive = 0 GROUP BY tbl_member.id");
                } else if (isset($filter->filterByType) && $filter->filterByType == 3) {
                    $this->db->join('tbl_fms_case', 'tbl_fms_case.initiated_by = tbl_member.id AND tbl_fms_case.initiated_type = 1', 'right');
                    $this->db->where_in('tbl_fms_case.status', '0,1');
                } else if (isset($filter->filterByType) && $filter->filterByType == 4) {
                    $this->db->join('tbl_member_special_agreement', 'tbl_member_special_agreement.memberId = tbl_member.id AND tbl_member_special_agreement.archive = 0', 'right');
                } else if (isset($filter->filterByType) && $filter->filterByType == 5) {
                    $this->db->join('tbl_member_availability', "tbl_member_availability.memberId = tbl_member.id AND tbl_member_availability.archive = 0", 'right');
                } else if (isset($filter->filterByType) && $filter->filterByType == 6) {
                    $this->db->join('tbl_member_qualification', "tbl_member_qualification.memberId = tbl_member.id AND tbl_member_qualification.archive = 0 AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(UNIX_TIMESTAMP(expiry)), '%Y-%m-%d'),CURDATE()) < 30 AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(UNIX_TIMESTAMP(expiry)), '%Y-%m-%d'),CURDATE()) >=0", 'right');
                }
                
                if(!empty($filter->srch_box)) {
                    $search_value = $this->db->escape_str($filter->srch_box, TRUE);
                    if (!empty($search_value)) {
                        $where = $where . " AND (";
                        $sWhere = " (" . $where;
                        for ($i = 0; $i < count($src_columns); $i++) {
                            $column_search = $src_columns[$i];
                            if (strstr($column_search, "as") !== false) {
                                $serch_column = explode(" as ", $column_search);
                                $sWhere .= $serch_column[0] . " LIKE '%" . $this->db->escape_like_str($search_value) . "%' OR ";
                            } else {
                                $sWhere .= $column_search . " LIKE '%" . $this->db->escape_like_str($search_value) . "%' OR ";
                            }
                        }
                        $sWhere = substr_replace($sWhere, "", -3);
                        $sWhere .= '))';
                    }
                }
            } else {
                $status_con = 1;
                $this->db->where_in('tbl_member.status', $status_con);
            }

            $select_column = array('tbl_member.id as OCS_id', 'tbl_member.firstname', 'tbl_member.lastname', 'tbl_member_address.street', 'tbl_member_address.city', 'tbl_member_address.postal', 'tbl_member_address.state', 'tbl_member_phone.phone', 'tbl_member.gender');

            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            $this->db->select("case when tbl_member_address.state!=0 THEN
                (SELECT tbl_state.name as state_name FROM tbl_state where tbl_member_address.state=tbl_state.id AND tbl_state.archive=0) ELSE '' END as state_name", false);

            $this->db->from(TBL_PREFIX . 'member');
            $this->db->join('tbl_member_address', 'tbl_member_address.memberId = tbl_member.id AND tbl_member_address.primary_address = 1', 'left');
            $this->db->join('tbl_member_phone', 'tbl_member_phone.memberId = tbl_member.id AND tbl_member_phone.primary_phone = 1', 'left');
            $this->db->join('tbl_member_email', 'tbl_member_email.memberId = tbl_member.id AND tbl_member_email.primary_email = 1', 'left');
            $this->db->join('tbl_department as d', 'd.id = tbl_member.department AND d.short_code = "external_staff"', 'inner');

            $this->db->group_by('tbl_member.id');
            $this->db->order_by($orderBy, $direction);
            $this->db->limit($limit, ($page * $limit));

            $this->db->where($sWhere, null, false);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            #last_query();die;
            $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }

            $dataResult = array();
            if (!empty($query->result())) {
                foreach ($query->result() as $val) {
                    $row = array();
                    $row['ocs_id'] = $val->OCS_id;
                    $row['firstname'] = $val->firstname . ' ' . $val->lastname;

                    $street = isset($val->street) ? $val->street : '';
                    $city = isset($val->city) ? $val->city : '';
                    $postal = isset($val->postal) ? $val->postal : '';
                    $state = isset($val->state_name) ? $val->state_name : '';

                    $row['street'] = $street;
                    $row['city'] = $city;
                    $row['postal'] = $postal;
                    $row['state'] = $state;
                    $row['completeAddress'] = $street . ' ' . $city . ' ' . $postal . ' ' . $state;

                    $row['department'] = 'Department';
                    $row['phone'] = $val->phone;
                    $row['gender'] = isset($val->gender) && $val->gender == 1 ? 'Male' : 'Female';;
                    $dataResult[] = $row;
                }
            }

            $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true, 'total_count' => $total_count);
            return $return;
        }
    }

    /*
     * To fetch the members' skills list
     */
    public function get_member_skills_list($reqData)
    {

        $limit = sprintf("%d", $reqData->pageSize) ?? 0;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("r.display_name", "r1.display_name", "DATE_FORMAT(ms.start_date,'%d/%m/%Y')", "DATE_FORMAT(ms.end_date,'%d/%m/%Y')");
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== FALSE) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        $available_column = ["id", "member_id", "skill_id", "skill_level_id", "start_date", "end_date", "skill_name"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ms.id';
            $direction = 'DESC';
        }
        $select_column = ["ms.id", "ms.member_id", "ms.skill_id", "ms.skill_level_id", "ms.start_date", "ms.end_date", "r.display_name as skill_name", "'' as actions"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("r1.display_name as skill_level");

        $this->db->from('tbl_member_skill as ms');
        $this->db->join('tbl_references as r', 'r.id = ms.skill_id', 'inner');
        $this->db->join('tbl_references as r1', 'r1.id = ms.skill_level_id', 'left');
        $this->db->where('ms.archive', 0);

        $member_id = sprintf("%d",$reqData->member_id) ?? '';

        if ($member_id > 0)
            $this->db->where('ms.member_id', $member_id);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch Goals list successfully');
        return $return;
    }

    /*
     * Fetching members' unavailability list that are added through keypay leaves fetching
     */
    public function get_ext_ref_member_unavailability_ids() {

        $select_column = ["mu.*"];
        $this->db->select($select_column);
        $this->db->from('tbl_member_unavailability as mu');
        $this->db->join('tbl_member as m', 'm.id = mu.member_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = mu.type_id and r.archive = 0', 'left');
        $this->db->where('mu.keypay_ref_id > 0');
        $this->db->where('mu.archive', 0);
        $this->db->where('m.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $unavailability_ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
            $unavailability_ids[] = $row->id;
            }
        }
        return $unavailability_ids;
    }

    /*
     * To fetch the members' unavailability list
     */
    public function get_member_unavailability_list($reqData)
    {

        $limit = sprintf("%d", $reqData->pageSize) ?? 0;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("r.display_name", "DATE_FORMAT(mu.start_date,'%d/%m/%Y')", "DATE_FORMAT(mu.end_date,'%d/%m/%Y')");
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== FALSE) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        $available_column = ["id", "member_id", "type_id", "start_date", "end_date"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'mu.id';
            $direction = 'DESC';
        }
        $select_column = ["mu.id", "mu.member_id", "mu.type_id", "mu.start_date", "mu.end_date", "'' as actions"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("r.display_name as unavailability_type");

        $this->db->from('tbl_member_unavailability as mu');
        $this->db->join('tbl_references as r', 'r.id = mu.type_id', 'left');
        $this->db->where('mu.archive', 0);

        $member_id = sprintf("%d", $reqData->member_id);
        if ($member_id > 0)
            $this->db->where('mu.member_id', $member_id);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch unavailability list successfully');
        return $return;
    }

    /**
     * Fetching the timesheet line items from keypay which are payment processed
     */
    public function get_paid_keypay_timesheets($adminId) {

        # using the keypay class to perform OTP operations
        $obj = new KeyPay();
        $obj->set_admin_id($adminId);

        # authenticating keypay
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            return array('status' => false, 'error' => $obj->get_error());            
        }

        # Fetching the timesheet line items from keypay which are payrun finalized successfully
        $return = $obj->get_paid_keypay_timesheets_by_payrun();
       
        return ['status' => true, 'data' => $return];
    }

    /**
     * creating timesheet lines in keypay for each timesheet line items found in HCM
     */
    public function create_keypay_timesheet_line($adminId, $from_date, $to_date, $keypay_emp_line_items) {

        # using the keypay class to perform OTP operations
        $obj = new KeyPay();
        $obj->set_admin_id($adminId);

        # authenticating keypay
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # submitting timesheet lines into keypay
        $return = $obj->create_timesheet_line($from_date, $to_date, $keypay_emp_line_items);
        if(!$return) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        return ["status" => true];
    }

    /**
     * fetching leaves from keypay to mark them as unavailable in HCM
     */
    public function get_keypay_employee_leaves($adminId) {

        # using the keypay class to perform OTP operations
        $obj = new KeyPay();
        $obj->set_admin_id($adminId);

        # authenticating keypay
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # fetching leaves from keypay
        $return = $obj->get_keypay_employee_leaves();
        if(!$return) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # no leaves found but keypay fetch was successful?
        if(!is_array($return)) {
            $return = ["status" => true];
            return $return;
        }

        # fetching existing member unavailability ids
        $existing_member_unavailability_ids = [];
        $existing_member_unavailability_ids = $this->get_ext_ref_member_unavailability_ids();
        $selected_member_unavailability_ids = [];

        foreach($return as $leave_row) {

            # donot proceed if the status is not approved
            if(empty($leave_row['status']) || ($leave_row['status'] != "Approved" && $leave_row['status'] != "Pending"))
                continue;

            # finding the member id from keypay employee id
            $member_id = $this->get_member_id_from_keypay_emp_id($leave_row['employeeId']);
            if(empty($member_id))
                continue;

            # formatting leave type to match against the reference list of leaves
            $leave_type = $leave_row['leaveCategory'];
            $leave_type = str_replace(' ', '_', $leave_type);
            $leave_type = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', $leave_type));

            # finding the reference id from the leave type of keypay
            $ref_details = $this->Common_model->get_reference_data_row("unavailability_type", $leave_type);
            if(empty($ref_details))
                continue;

            $insdata = null;

            # check if the leave is already added or not for the same member
            $unavailability_id = $this->get_unavailability_id_from_keypay_ref_id($leave_row['id'], $member_id);
            if($unavailability_id) {
                $insdata['id'] = $unavailability_id;
                $selected_member_unavailability_ids[] = $unavailability_id;
            }
            $insdata['start_datetime'] = date_format(date_create($leave_row['fromDate']), "Y-m-d h:i A");
            $end_datetime = date_format(date_create($leave_row['toDate']), "Y-m-d H:i:s");
            $insdata['end_datetime'] = date('Y-m-d h:i A', strtotime($end_datetime . '+1 day'));
            $insdata['type_id'] = $ref_details['id'];
            $insdata['member_id'] = $member_id;
            $insdata['keypay_ref_id'] = $leave_row['id'];

            $insert_res = $this->create_update_member_unavailability($insdata, $adminId);
            if($insert_res['status'] == false)
            return $insert_res;
        }

        # any existing member unavailability ids that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_member_unavailability_ids, $selected_member_unavailability_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_sb_id) {
                $del_res = $this->archive_member_unavailability(['id' => $rem_sb_id], $adminId);
                if($del_res['status'] == false)
                    return $del_res;
            }
        }

        return ["status" => true];
    }

    /**
     * getting member id from keypay emp id
     */
    function get_member_id_from_keypay_emp_id($keypay_emp_id) {
        $select_column = ["ke.member_id"];
        $this->db->select($select_column);
        $this->db->from('tbl_keypay_kiosks_emp_mapping_for_member as ke');
        $this->db->join('tbl_member as m', 'm.id = ke.member_id', 'inner');
        $this->db->where('ke.archive', 0);
        $this->db->where('m.archive', 0);
        $this->db->where('ke.keypay_emp_id', $keypay_emp_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result_set = $query->result();
        return ($result_set && isset($result_set[0]->member_id)) ? $result_set[0]->member_id : '';
    }

    /**
     * getting unavailability id from keypay ref id and member id
     */
    function get_unavailability_id_from_keypay_ref_id($keypay_ref_id, $member_id) {
        $select_column = ["mu.id"];
        $this->db->select($select_column);
        $this->db->from('tbl_member_unavailability as mu');
        $this->db->join('tbl_member as m', 'm.id = mu.member_id', 'inner');
        $this->db->where('mu.archive', 0);
        $this->db->where('m.archive', 0);
        $this->db->where('mu.keypay_ref_id', $keypay_ref_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result_set = $query->result();
        return ($result_set && isset($result_set[0]->id)) ? $result_set[0]->id : '';
    }

    /**
     * creating employee record inside keypay
     */
    public function create_keypay_employee($member_id, $adminId) {
        # fetching member details
        $member_details = $this->get_member_details($member_id);
        $member_details = obj_to_arr($member_details['data']);

        # not adding into keypay if the keypay reference is already added
        if(isset($member_details['keypay_emp_id']) && !empty($member_details['keypay_emp_id']))
            return ["status" => true, "keypay_emp_id" => $member_details['keypay_emp_id']];

        # fetching member contact details
        $this->load->model('common/Contact_model');
        $contact_details = $this->Contact_model->get_contact_details($member_details['person_id']);

        # fetching address details
        $address_details = obj_to_arr($this->basic_model->get_row('person_address', ['street', 'suburb', 'postcode', '(select s.name from tbl_state as s where s.id = tbl_person_address.state) as state_label'], ['person_id' => $member_details['person_id'], 'archive' => 0, 'primary_address' => 1]));

        $combined_details = array_merge($contact_details, $address_details, $member_details);
        if(isset($combined_details['PhoneInput']) && isset($combined_details['PhoneInput'][0]) && isset($combined_details['PhoneInput'][0]->phone))
            $combined_details['phone'] = $combined_details['PhoneInput'][0]->phone;

        # using the keypay class to perform OTP operations
        $obj = new KeyPay();
        $obj->set_admin_id($adminId);

        # authenticating keypay
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # creating employee inside keypay
        $keypay_response = $obj->create_employee($combined_details);
        if(!$keypay_response) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # updating the external employee id data point of the member
        $this->insert_update_member_keypay_id($member_id, $keypay_response, $adminId);
        return ["status" => true];
    }

    /**
     * fetching a single member details
     */
    public function get_member_details($id)
    {
        if (empty($id)) return;

        $this->db->select("m.*, km.keypay_emp_id, m.username as uname");
        $this->db->select("(case when m.person_id > 0 THEN
        (SELECT CONCAT(p.firstname,' ',p.lastname) from tbl_person p where p.id = m.person_id) ELSE '' END) as contact_name, p.profile_pic as avatar, p.middlename, p.previous_name, p.firstname as person_firstname , p.lastname as person_lastname, p.date_of_birth, pa.unit_number, pa.is_manual_address, pa.manual_address", false);
       
        $this->db->select("(case when m.person_id > 0 THEN (SELECT CONCAT(pa.street, ', ', pa.suburb, ' ',(select s.name from tbl_state as s where s.id = pa.state), pa.postcode ) from tbl_person_address pa where pa.person_id = m.person_id and pa.primary_address=1 and pa.archive=0) ELSE '' End) as address, ", false);
        $this->db->select("(select tr.display_name from tbl_references as tr where tr.id = p.gender) as gender_label");
        $this->db->select("(select pe.email from tbl_person_email as pe where pe.person_id = p.id and pe.primary_email=1 and pe.archive=0) as person_email");
        $this->db->select("(select ph.phone from tbl_person_phone as ph where ph.person_id = p.id and ph.primary_phone=1 and ph.archive=0 ) as person_phone");
        
        $this->db->select("(CASE WHEN m.status = 1 THEN 'Active'
                ELSE 'Inactive' END) as status_label");
        $this->db->select("(select pe.email from tbl_person_email as pe where pe.person_id = p.id and pe.primary_email=1 and pe.archive=0) as person_email");
        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join('tbl_person p', 'm.person_id = p.id', 'inner');
        $this->db->join('tbl_users u', 'u.id = m.uuid', 'left');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"', 'inner');
        $this->db->join('tbl_keypay_kiosks_emp_mapping_for_member as km', 'km.member_id = m.id AND km.archive = 0', 'left');
        $this->db->join('tbl_person_address pa', 'pa.person_id = p.id and pa.primary_address=1 and pa.archive=0', 'left');

        $this->db->group_by('m.id');
        $this->db->where("m.id", $id);
        $this->db->where("m.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $ci = &get_instance();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Member not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # account person for pre-selection
            $account_person['label'] = $val->contact_name;
            $account_person['value'] = $val->person_id;
            $dataResult->account_person = $account_person;
        }

        # fetching references selected
        $dataResult->like_selection = $this->get_member_ref_data($id, 2);
        $dataResult->language_selection = $this->get_member_ref_data($id, 14);
        $dataResult->transport_selection = $this->get_member_ref_data($id, 19);

        $return = array('data' => $dataResult, 'status' => true);
        return $return;
    }

    /**
     * fetching a single member skill details
     */
    public function get_member_skill_details($id, $hourly_times)
    {
        if (empty($id)) return;

        $this->db->select("ms.*, m.id as value, CONCAT(p.firstname,' ',p.lastname) as label");

        $this->db->from('tbl_member_skill as ms');
        $this->db->join('tbl_member m', 'ms.member_id = m.id', 'inner');
        $this->db->join('tbl_person as p', 'p.id = m.person_id', 'inner');

        $this->db->group_by('ms.id');
        $this->db->where("ms.id", $id);
        $this->db->where("ms.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Member not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # for member pre-selection
            $member['label'] = $val->label;
            $member['value'] = $val->value;
            $dataResult->member = $member;

            # preselect start time and end time
            if (!empty($val->start_date) && $val->start_date != "0000-00-00 00:00:00") {
                list($d, $t) = explode(" ", $val->start_date);
                list($h, $m, $s) = explode(":", $t);
                // $dataResult->start_date = $d;
                $time_label = $h . ":" . $m;

                # finding the time id for hours:minutes
                $time_id = array_search($time_label, $hourly_times);
                if ($time_id !== NULL)
                    $dataResult->start_time_id = ($time_id + 1);
            }

            if (!empty($val->end_date) && $val->end_date != "0000-00-00 00:00:00") {
                list($d, $t) = explode(" ", $val->end_date);
                list($h, $m, $s) = explode(":", $t);
                // $dataResult->end_date = $d;
                $time_label = $h . ":" . $m;

                # finding the time id for hours:minutes
                $time_id = array_search($time_label, $hourly_times);
                if ($time_id !== NULL)
                    $dataResult->end_time_id = ($time_id + 1);
            } else {
                $dataResult->end_date = '';
                $dataResult->end_time_id = '';
            }
        }

        $return = array('data' => $dataResult, 'status' => true);
        return $return;
    }

    /**
     * fetching a single member unavailability details
     */
    public function get_member_unavailability_details($id)
    {
        if (empty($id)) return;

        $this->db->select("ms.*, m.id as value, m.fullname as label");

        $this->db->from('tbl_member_unavailability as ms');
        $this->db->join('tbl_member m', 'ms.member_id = m.id', 'inner');
        $this->db->join('tbl_person as p', 'p.id = m.person_id', 'inner');

        $this->db->group_by('ms.id');
        $this->db->where("ms.id", $id);
        $this->db->where("ms.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Member not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # for member pre-selection
            $member['label'] = $val->label;
            $member['value'] = $val->value;
            $dataResult->member = $member;

            # preselect start time and end time
            if(!empty($dataResult->start_date) && $dataResult->start_date != "0000-00-00 00:00:00")
                $dataResult->start_time = get_time_id_from_series($dataResult->start_date);
            else {
                $dataResult->start_date = '';
                $dataResult->start_time = '';
            }

            if(!empty($dataResult->end_date) && $dataResult->end_date != "0000-00-00 00:00:00")
                $dataResult->end_time = get_time_id_from_series($dataResult->end_date);
            else {
                $dataResult->end_date = '';
                $dataResult->end_time = '';
            }
        }

        $return = array('data' => $dataResult, 'status' => true);
        return $return;
    }

    /**
     * fetching member's reference data selected before
     */
    public function get_member_ref_data($member_id, $ref_type_id)
    {
        $this->db->select("r.id as id, r.display_name as label");
        $this->db->from('tbl_member_ref_data as mr');
        $this->db->join('tbl_references r', 'mr.ref_id = r.id', 'inner');
        $this->db->where("mr.member_id", $member_id);
        $this->db->where("r.type", $ref_type_id);
        $this->db->where("mr.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = [];
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $row = $val;
                $dataResult[] = $row;
            }
        }
        return $dataResult;
    }

    /*
     * fetching members name matching keyword provided
     */
    public function get_all_member_name_search($contactName = null, $member_id = null, $skip_ids = null)
    {

        $queryHaving = null;
        if (!$member_id) {
            $this->db->like('label', $contactName);
            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);
            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        }

        $this->db->select(["m.fullname as label", 'm.id as value']);
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_person p', 'p.id = m.person_id', 'inner');
        $this->db->where(['m.archive' => 0]);
        $this->db->where(['p.archive' => 0]);

        if (!empty($skip_ids) && is_array($skip_ids))
            $this->db->where("m.id not in (" . implode(',', $skip_ids) . ")");

        if ($member_id)
            $this->db->where(['m.id' => $member_id]);
        else
            $this->db->having($queryHaving);

        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    /**
     * fetching member from member email
     * TODO/FIXIT: we should use person_email table!
     */
    public function get_member_id_from_email($email)
    {
        $this->db->select('memberId');
        $this->db->where('tbl_member_email.email', $email);
        $this->db->where('tbl_member_email.archive', 0);
        $this->db->from('tbl_member_email');
        $query =  $this->db->get();
        $member_result = $query->row();
        return !empty($member_result->memberId) ? $member_result->memberId : '';
    }

    public function get_member_about($objMember)
    {
        $memberId = $objMember->getMemberid();

        if (!empty(request_handler())) {
            //$request = request_handler();
            //$memberId = $request->data->member_id;

            $tbl_1 = TBL_PREFIX . 'member';
            $tbl_2 = TBL_PREFIX . 'member_address';
            $tbl_3 = TBL_PREFIX . 'member_phone';
            $tbl_4 = TBL_PREFIX . 'member_email';
            $tbl_5 = TBL_PREFIX . 'member_kin';
            $tbl_6 = TBL_PREFIX . 'member_phone';

            $this->db->select("CONCAT($tbl_1.firstname,' ',$tbl_1.middlename, ' ', $tbl_1.lastname) AS user_name", FALSE);

            $dt_query = $this->db->select(array($tbl_1 . '.enable_app_access', $tbl_1 . '.id as ocs_id', $tbl_1 . '.firstname', $tbl_1 . '.lastname', $tbl_1 . '.companyId', $tbl_1 . '.middlename', $tbl_1 . '.profile_image', $tbl_1 . '.prefer_contact', $tbl_1 . '.dob', $tbl_1 . '.gender', $tbl_1 . '.status'));

            $this->db->from($tbl_1);
            #$this->db->join($tbl_2, 'tbl_member_address.memberId = tbl_member.id AND tbl_member_address.primary_address = 1', 'left');
            #$this->db->join($tbl_4, 'tbl_member_email.memberId = tbl_member.id AND tbl_member_email.primary_email = 1', 'left');
            #$this->db->join('tbl_state', 'tbl_state.id = tbl_member_address.state', 'left');
            $sWhere = array($tbl_1 . '.id' => $memberId);
            $this->db->where($sWhere, null, false);
            $query = $this->db->get();
            $x = $query->row_array();

            $user_ary = array();
            $user_ary['basic_detail']['full_name'] = $x['user_name'];
            $user_ary['basic_detail']['ocs_id'] = $x['ocs_id'];
            $user_ary['basic_detail']['first_name'] = $x['firstname'];
            $user_ary['basic_detail']['companyId'] = $x['companyId'];
            $user_ary['basic_detail']['prefer_contact'] = !empty($x['prefer_contact']) ? $x['prefer_contact'] : '';
            $user_ary['basic_detail']['profile_image'] = $x['profile_image'];
            #$user_ary['basic_detail']['email'] = $x['email'];
            $user_ary['basic_detail']['enable_app_access'] = $x['enable_app_access'];
            $user_ary['basic_detail']['gender'] = !empty($x['gender']) && $x['gender'] == 1 ? 'Male' : 'Female';
            $user_ary['basic_detail']['status'] = $x['status'];
            $user_ary['basic_detail']['dob'] = !empty($x['dob']) && $x['dob'] != '0000-00-00' ? date('d/m/Y', strtotime($x['dob'])) : '';

            $x = date('Y', strtotime($x['dob']));
            $y = date('Y');
            $user_ary['basic_detail']['age'] = $y - $x;

            $query_em = $this->db->select(array($tbl_4 . ".email", $tbl_4 . ".primary_email", $tbl_4 . ".id"));
            $this->db->from($tbl_4);
            $this->db->where(array('memberId' => $memberId, 'archive' => 0));
            $query_mail = $this->db->get();
            $user_ary['basic_detail']['email_ary'] = $query_mail->result();

            /* Address ary */
            $dt_query = $this->db->select(array($tbl_2 . '.street', $tbl_2 . '.city', $tbl_2 . '.postal', $tbl_2 . '.state', $tbl_2 . '.primary_address', 'tbl_state.name as statename'));
            $this->db->join('tbl_state', 'tbl_state.id = tbl_member_address.state', 'left');
            $this->db->from($tbl_2);
            $sWhere = array($tbl_2 . '.memberId' => $memberId, $tbl_2 . '.archive' => 0);
            $this->db->where($sWhere);
            $this->db->order_by($tbl_2 . '.primary_address', 'ASC');
            $query = $this->db->get();
            $addr_rows = $query->result_array();
            if (!empty($addr_rows)) {
                $user_ary['basic_detail']['address_ary'] = $addr_rows;
            }

            /* Get keen */
            $dt_query = $this->db->select(array($tbl_5 . '.relation', $tbl_5 . '.phone', $tbl_5 . '.email', $tbl_5 . '.primary_kin'));
            $this->db->select("CONCAT($tbl_5.firstname, ' ', $tbl_5.lastname) AS name", FALSE);

            $this->db->from($tbl_5);
            $sWhere = array($tbl_5 . '.memberId' => $memberId, $tbl_5 . '.archive' => 0);
            $this->db->where($sWhere, null, false);
            $query = $this->db->get();
            $y = $query->result_array();
            if (!empty($y)) {
                $user_ary['basic_detail']['kin_ary'] = $y;
            }

            /* Get phone no */
            $dt_query = $this->db->select(array($tbl_6 . '.phone', $tbl_6 . '.primary_phone'));
            $this->db->from($tbl_6);
            $sWhere = array($tbl_6 . '.memberId' => $memberId, $tbl_6 . '.archive' => 0);
            $this->db->where($sWhere, null, false);
            $query = $this->db->get();
            $z = $query->result_array();
            if (!empty($z)) {
                $user_ary['basic_detail']['phone_no_ary'] = $z;
            }
            return $user_ary;
        }
    }

    public function get_member_profile($objMember)
    {
        $memberId = $objMember->getMemberid();
        if (!empty(request_handler())) {
            $user_ary = array();

            $tbl_1 = TBL_PREFIX . 'member';
            $tbl_2 = TBL_PREFIX . 'member_phone';
            $tbl_3 = TBL_PREFIX . 'member_email';
            $tbl_4 = TBL_PREFIX . 'member_address';
            $tbl_5 = TBL_PREFIX . 'member_kin';

            $this->db->select("CONCAT($tbl_1.firstname,' ',$tbl_1.middlename, ' ', $tbl_1.lastname) AS user_name", FALSE);
            $dt_query = $this->db->select(array($tbl_1 . '.enable_app_access', $tbl_1 . '.id as ocs_id', $tbl_1 . '.firstname', $tbl_1 . '.lastname', $tbl_1 . '.companyId', $tbl_1 . '.middlename', $tbl_1 . '.profile_image', $tbl_1 . '.prefer_contact', $tbl_1 . '.dob', $tbl_1 . '.gender', $tbl_1 . '.status', $tbl_1 . '.dwes_confirm', $tbl_2 . '.phone'));

            $this->db->from($tbl_1);
            $this->db->join($tbl_2, 'tbl_member.id = tbl_member_phone.memberId', 'left');
            $sWhere = array($tbl_1 . '.id' => $memberId, $tbl_1 . '.archive' => 0);
            $this->db->where($sWhere, null, false);
            $query = $this->db->get();
            #last_query();
            $x = $query->row_array();

            #pr($x);

            if (!empty($x)) {
                $query_ph = $this->db->select(array($tbl_2 . ".phone", $tbl_2 . ".primary_phone", $tbl_2 . ".id"));
                $this->db->from($tbl_2);
                $this->db->order_by($tbl_2 . ".primary_phone");
                $this->db->where(array('memberId' => $memberId, 'archive' => 0));
                $query = $this->db->get();

                $user_ary['basic_detail']['phone_ary'] = $query->result();

                $query_em = $this->db->select(array($tbl_3 . ".email", $tbl_3 . ".primary_email", $tbl_3 . ".id"));
                $this->db->from($tbl_3);
                $this->db->order_by($tbl_3 . ".primary_email");
                $this->db->where(array('memberId' => $memberId, 'archive' => 0));
                $query = $this->db->get();
                $user_ary['basic_detail']['email_ary'] = $query->result();

                $query_adr = $this->db->select(array($tbl_4 . ".street", $tbl_4 . ".city", $tbl_4 . ".id", $tbl_4 . ".postal", $tbl_4 . ".state", $tbl_4 . ".primary_address", 'tbl_state.name as state_name'));
                $this->db->from($tbl_4);
                $this->db->join('tbl_state', 'tbl_state.id = tbl_member_address.state', 'left');
                $this->db->where(array('memberId' => $memberId, $tbl_4 . '.archive' => 0));
                $query = $this->db->get();
                $addr = $query->result();
                $completeAddress = array();
                if (!empty($addr)) {
                    foreach ($addr as $key => $value) {
                        $temp_ary['street'] = $value->street;
                        $temp_ary['id'] = $value->id;
                        #$temp_ary['city'] = $value->city;
                        $temp_ary['postal'] = $value->postal;
                        $temp_ary['statename'] = $value->state_name;
                        $temp_ary['primary_address'] = $value->primary_address;
                        $temp_ary['state'] = $value->state;
                        # $temp_ary['city'] = array('value' => $value->city, 'label' => $value->city);
                        $temp_ary['city'] = $value->city;
                        $temp_ary['suburb'] = $value->city;
                        $completeAddress[] = $temp_ary;
                    }
                }

                $query_kin = $this->db->select(array($tbl_5 . ".firstname", $tbl_5 . ".lastname", $tbl_5 . ".id", $tbl_5 . ".relation", $tbl_5 . ".phone", $tbl_5 . ".email as kin_email", $tbl_5 . ".primary_kin"));
                $this->db->select("CONCAT($tbl_5.firstname, ' ', $tbl_5.lastname) AS name", FALSE);
                $this->db->from($tbl_5);
                $this->db->where(array('memberId' => $memberId, 'archive' => 0));
                $kin_query = $this->db->get();

                $user_ary['basic_detail']['kin_ary'] = $kin_query->result();
                $user_ary['basic_detail']['completeAddress'] = $completeAddress;
                $user_ary['basic_detail']['full_name'] = $x['user_name'];
                $user_ary['basic_detail']['prefer_contact'] = $x['prefer_contact'];
                $user_ary['basic_detail']['ocs_id'] = $x['ocs_id'];
                $user_ary['basic_detail']['first_name'] = $x['firstname'];
                $user_ary['basic_detail']['companyId'] = $x['companyId'];
                $user_ary['basic_detail']['profile_image'] = $x['profile_image'];
                $user_ary['basic_detail']['enable_app_access'] = $x['enable_app_access'];
                $user_ary['basic_detail']['gender'] = $x['gender'];
                $user_ary['basic_detail']['status'] = $x['status'];
                $user_ary['basic_detail']['dwes_confirm'] = $x['dwes_confirm'];
                $user_ary['basic_detail']['dob'] = !empty($x['dob']) && $x['dob'] != '0000-00-00' ? date('d/m/Y', strtotime($x['dob'])) : '';

                $dob_year = date('Y', strtotime($x['dob']));
                $y = date('Y');
                $user_ary['basic_detail']['age'] = $y - $dob_year;

                return array('status' => true, 'data' => $user_ary);
            } else {
                return array('status' => false);
            }
        }
    }

    public function member_qualification($objMember)
    {
        if (!empty(request_handler())) {

            $memberId = $objMember->getMemberid();
            $view_by = $objMember->getArchive();

            $start_date = $objMember->getStart_date();
            $end_date = $objMember->getEnd_date();

            if (!empty($start_date)) {
                $this->db->where('mq.expiry >=', DateFormate($start_date, 'Y-m-d'));
            }
            if (!empty($end_date)) {
                $this->db->where('mq.expiry <=', DateFormate($end_date, 'Y-m-d'));
            }


            $this->db->where('archive', $view_by);



            $dt_query = $this->db->select(array('mq.id', 'mq.expiry', 'mq.title', 'mq.created', 'mq.can_delete', 'mq.filename', "DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(UNIX_TIMESTAMP(expiry)), '%Y-%m-%d'),CURDATE()) AS days_left", 'mq.type',));
            $sWhere = array('mq.memberId' => $memberId);
            $this->db->from('tbl_member_qualification as mq');


            $this->db->where($sWhere, null, false);
            $query = $this->db->get();

            $x = $query->result_array();
            $data_ary = array();
            if (!empty($x)) {
                foreach ($x as $key => $value) {
                    $temp_ary['id'] = $value['id'];
                    $temp_ary['type'] = $value['type'];
                    $temp_ary['days_left'] = $value['days_left'];
                    $temp_ary['expiry'] = date('d-m-Y', strtotime($value['expiry']));
                    $temp_ary['title'] = $value['title'];
                    $temp_ary['created'] = $value['created'];
                    $temp_ary['can_delete'] = $value['can_delete'];
                    $temp_ary['filename'] = $value['filename'];
                    if ($value['days_left'] < 0 || $value['days_left'] < 5)
                        $class_name = 'bg_dark_Pink';
                    else if ($value['days_left'] > 0 && $value['days_left'] < 30)
                        $class_name = 'bg_dark_yello';
                    else
                        $class_name = '';

                    $temp_ary['class_name'] = $class_name;
                    $data_ary[] = $temp_ary;
                }
            }

            if (!empty($data_ary))
                return $data_ary;
            else
                return array();
        }
    }

    public function get_member_preference($objMember)
    {
        $memberId = $objMember->getMemberid();
        $tbl_12 = TBL_PREFIX . 'place';
        $tbl_11 = TBL_PREFIX . 'member_place';

        $tbl_10 = TBL_PREFIX . 'member_activity';
        $tbl_13 = TBL_PREFIX . 'activity';

        $arr = array('placeFav' => '', 'placeLeastFav' => '', 'activityFav' => '', 'activityLeastFav' => '');
        // get places participant
        $dt_query = $this->db->select(array($tbl_12 . ".name as label", $tbl_12 . ".id", $tbl_11 . ".type"));
        $this->db->from($tbl_12);
        $this->db->join($tbl_11, $tbl_12 . '.id = ' . $tbl_11 . '.placeId AND memberId = ' . $memberId, 'left');
        $query = $this->db->get();
        $arr['places'] = $query->result();

        if (!empty($arr['places'])) {
            $placeNon = $fav = $leastFav = [];
            foreach ($arr['places'] as $val) {
                if ($val->type == 1) {
                    $fav[] = $val->label;
                } elseif ($val->type == 2) {
                    $leastFav[] = $val->label;
                } elseif ($val->type == 3) {
                    $placeNon[] = $val->label;
                }
                $val->type = ($val->type) ? $val->type : 3;
            }

            $arr['placeFav'] = implode(', ', $fav);
            $arr['placeLeastFav'] = implode(', ', $leastFav);
            $arr['placeNon'] = implode(', ', $placeNon);
        }

        // get activity participant
        $dt_query = $this->db->select(array($tbl_13 . ".name as label", $tbl_13 . ".id", $tbl_10 . ".type"));
        $this->db->from($tbl_13);
        $this->db->join($tbl_10, $tbl_13 . '.id = ' . $tbl_10 . '.activityId AND memberId = ' . $memberId, 'left');
        $query = $this->db->get();
        $arr['activity'] = $query->result();

        if (!empty($arr['activity'])) {
            $activityNon = $fav = $leastFav = [];
            foreach ($arr['activity'] as $val) {
                if ($val->type == 1) {
                    $fav[] = $val->label;
                } elseif ($val->type == 2) {
                    $leastFav[] = $val->label;
                } elseif ($val->type == 3) {
                    $activityNon[] = $val->label;
                }
                $val->type = ($val->type) ? $val->type : 3;
            }
            $arr['activityFav'] = implode(', ', $fav);
            $arr['activityLeastFav'] = implode(', ', $leastFav);
            $arr['activityNon'] = implode(', ', $activityNon);
        }

        $arr['skill'] = $this->get_member_skill($memberId, 'assistance');
        $arr['mobility_skill'] = $this->get_member_skill($memberId, 'mobility');

        return $arr;
    }

    public function member_shift_count($reqData)
    {
        $member_id = $reqData->data->member_id;

        $tbl_1 = TBL_PREFIX . 'shift';
        $tbl_2 = TBL_PREFIX . 'shift_member';

        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");


        /* Rejected Quator */
        $this->db->select("count($tbl_1.id) as rejected_count_Q");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 90 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_2 . '.status' => 2);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");

        $this->db->where($sWhere, null, false);
        $query = $this->db->get();

        $count = $query->row();
        $rejected_count_Q = $count->rejected_count_Q;

        /* Cancelled Quator */
        $this->db->select("count($tbl_1.id) as cancelled_count_Q");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 90 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_2 . '.status' => 4);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();

        $count = $query->row();
        $cancelled_count_Q = $count->cancelled_count_Q;

        /* Filled Quator */
        $this->db->select("count($tbl_1.id) as filled_count_Q");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 90 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_1 . '.status' => 7, $tbl_2 . '.status' => 3);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();
        $count = $query->row();
        $filled_count_Q = $count->filled_count_Q;

        /* Rejected Week */
        $this->db->select("count($tbl_1.id) as rejected_count_W");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_2 . '.status' => 2);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();
        $count = $query->row();
        $rejected_count_W = $count->rejected_count_W;

        /* Cancelled Week */
        $this->db->select("count($tbl_1.id) as cancelled_count_W");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_2 . '.status' => 4);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();
        $count = $query->row();
        $cancelled_count_W = $count->cancelled_count_W;

        /* Filled Week */
        $this->db->select("count($tbl_1.id) as filled_count_W");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_1 . '.status' => 7, $tbl_2 . '.status' => 3);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();
        $count = $query->row();
        $filled_count_W = $count->filled_count_W;

        /* Rejected Year */
        $this->db->select("count($tbl_1.id) as rejected_count_Y");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('YEAR(tbl_shift.created)=YEAR(CURDATE())');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_2 . '.status' => 2);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();
        $count = $query->row();
        $rejected_count_Y = $count->rejected_count_Y;

        /* Cancelled Year */
        $this->db->select("count($tbl_1.id) as cancelled_count_Y");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('YEAR(tbl_shift.created)=YEAR(CURDATE())');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 90 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_2 . '.status' => 4);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();
        $count = $query->row();
        $cancelled_count_Y = $count->cancelled_count_Y;

        /* Filled Year */
        $this->db->select("count($tbl_1.id) as filled_count_Y");
        $this->db->from($tbl_1);
        $this->db->join($tbl_2, 'tbl_shift.id = tbl_shift_member.shiftId AND tbl_shift_member.archive = 0', 'left');
        $this->db->where('YEAR(tbl_shift.created)=YEAR(CURDATE())');
        $this->db->where('tbl_shift.created > DATE_SUB(NOW(), INTERVAL 90 DAY)');
        $sWhere = array($tbl_2 . '.memberId' => $member_id, $tbl_1 . '.status' => 7, $tbl_2 . '.status' => 3);
        $this->db->where($sWhere, null, false);
        if (!empty($reqData->data->from_date))
            $this->db->where($tbl_1 . ".shift_date >= '" . date('Y-m-d', strtotime($reqData->data->from_date)) . "'");

        if (!empty($reqData->data->to_date))
            $this->db->where($tbl_1 . ".shift_date <= '" . date('Y-m-d', strtotime($reqData->data->to_date)) . "'");
        $query = $this->db->get();

        $count = $query->row();
        $filled_count_Y = $count->filled_count_Y;

        echo json_encode(array(
            'filled_count_Q' => $filled_count_Q,
            'cancelled_count_Q' => $cancelled_count_Q,
            'rejected_count_Q' => $rejected_count_Q,
            'filled_count_W' => $filled_count_W,
            'cancelled_count_W' => $cancelled_count_W,
            'rejected_count_W' => $rejected_count_W,
            'filled_count_Y' => $filled_count_Y,
            'cancelled_count_Y' => $cancelled_count_Y,
            'rejected_count_Y' => $rejected_count_Y,
        ));
        /* echo json_encode(array(
          'filled_count_Q'=>54354,
          'cancelled_count_Q'=>55434,
          'rejected_count_Q'=>55654,

          'filled_count_W'=>012,
          'cancelled_count_W'=>2,
          'rejected_count_W'=>1,

          'filled_count_Y'=>1,
          'cancelled_count_Y'=>0,
          'rejected_count_Y'=>111,
      )); */
    }

    public function get_member_shifts($reqData)
    {
        $this->load->model('schedule/Listing_model');
        $sub_query_shift_for = $this->Listing_model->common_user_name_for_shift_query();
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $member_id = isset($reqData->member_id) ? $reqData->member_id : '';

        $orderBy = '';
        $direction = '';

        $src_columns = array("s.id", "spell_condition");
        $available_column = array("id", "shift_date", "start_time", "end_time", "expenses", "status");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 's.id';
            $direction = 'ASC';
        }

        $shift_status = $filter->shift_status;

        if ($shift_status == 6) {
            $member_shift_status = 3;
        } else if ($shift_status == 5) {
            $member_shift_status = 4;
        } else if ($shift_status == 4) {
            $member_shift_status = 2;
        }

        if(!empty($filter->search_box)) {
            $search_key  = $this->db->escape_str($filter->search_box, true);
            if (!empty($search_key)) {
                $this->db->group_start();

                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search == 'spell_condition') {
                        $search = $search_key;
                        /* $this->db->or_where("CASE
                    WHEN s.booked_by = 1 THEN s.id IN (select sub_ss.shiftId from tbl_shift_site as sub_ss
                    INNER JOIN tbl_organisation_site as sub_os ON sub_os.id = sub_ss.siteId where sub_ss.shiftId = s.id  AND sub_os.site_name LIKE '%" . $search . "%')

                    WHEN s.booked_by = 2 THEN s.id IN (select sub_sp.shiftId from tbl_shift_participant as sub_sp
                    INNER JOIN tbl_participant as sub_p ON sub_p.id = sub_sp.participantId where sub_sp.shiftId = s.id AND concat_ws(' ',sub_p.firstname,sub_p.lastname) LIKE '%" . $search . "%')

                    WHEN s.booked_by = 3 THEN s.id IN (select sub_sp.shiftId from tbl_shift_participant as sub_sp
                    INNER JOIN tbl_participant as sub_p ON sub_p.id = sub_sp.participantId where sub_sp.shiftId = s.id  AND concat_ws(' ',sub_p.firstname,sub_p.lastname) LIKE '%" . $search . "%') else '' end", null, FALSE); */

                        $this->db->or_where("(" . $sub_query_shift_for . ") like  '%$search_key%'", null, false);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }

        if (!empty($filter->start_date)) {
            $this->db->where("s.shift_date >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
        }
        if (!empty($filter->end_date)) {
            $this->db->where("s.shift_date <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
        }

        $select_column = array("s.id", "s.shift_date", "s.start_time", "s.end_time", "s.expenses", "s.status");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select($sub_query_shift_for . ' as shift_for');
        $this->db->from('tbl_shift as s');

        $this->db->select("CONCAT(m.firstname,' ',m.middlename,' ',m.lastname ) as memberName");
        $this->db->select("CONCAT(MOD( TIMESTAMPDIFF(hour," . "s.start_time," . "s.end_time), 24), ':',MOD( TIMESTAMPDIFF(minute," . "s.start_time," . "s.end_time), 60), ' hrs') as duration");

        $this->db->join('tbl_shift_member as sm', 'sm.shiftId = s.id AND sm.archive = 0 AND sm.status = ' . $member_shift_status, 'INNER');
        $this->db->join('tbl_member as m', 'm.id = sm.memberId', 'INNER');
        if ($shift_status == 6) {
            $this->db->where('s.status', $shift_status);  //used in case of complete shift
        }
        $this->db->where('sm.memberId', $member_id);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();
        $total_duration = 0.00;
        if (!empty($dataResult)) {
            $time = array();
            foreach ($dataResult as $val) {
                //$val->memberNames = $this->get_preferred_member($val->id);
                //$val->address = $this->get_shift_location($val->id);

                $val->diff = (strtotime(date('Y-m-d h:i:sa', strtotime($val->start_time))) - strtotime(date('Y-m-d h:i:sa'))) * 1000;

                $time[] = str_replace('hrs', '', $val->duration);
                $val->shift_date = date('d/m/Y', strtotime($val->shift_date));
                $val->start_time = date('h:ia', strtotime($val->start_time));
                $val->end_time = date('h:ia', strtotime($val->end_time));
                $val->duration = $val->duration;
                $val->download_status = FALSE;
                $val->status = isset($val->status) && $val->status == 6 ? 'Complete' : ($val->status == 5 ? 'Cancelled' : 'Upcoming Shifts');
            }
            $total_duration = add_hour_minute($time);
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_duration' => $total_duration, 'all_count' => $all_count, 'status' => true);
        return $return;
    }

    public function get_member_upcoming_shifts($reqData)
    {
        $data = json_decode($reqData, true);
        $member_id = sprintf("%d", $data['member_id']) ?? 0;
        $default_date = $data['default_date'] ?? DATE_TIME;
        $month = date('m', strtotime($default_date));
        $year = date('Y', strtotime($default_date));


        $select_column = array("s.id", "s.shift_date", "s.status");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CONCAT(MOD( TIMESTAMPDIFF(hour,s.start_time,s.end_time), 24), ':',MOD( TIMESTAMPDIFF(minute,s.start_time,s.end_time), 60), ' hrs') as duration");
        $this->db->from("tbl_shift as s");
        $this->db->join("tbl_shift_member as sm", 'sm.shiftId = s.id AND sm.status IN (1,3) AND sm.archive = 0', 'left');
        $this->db->where_in("s.status", [2, 7]);
        $this->db->where("sm.memberId", $member_id);
        $this->db->where("MONTH(s.shift_date)", $month);
        $this->db->where("YEAR(s.shift_date)", $year);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $dataResult = $query->result();
        $total_duration = 0.00;
        if (!empty($dataResult)) {
            $time = array();
            foreach ($dataResult as $val) {
                $time[] = str_replace('hrs', '', $val->duration);
                $val->title = '';
                $val->description = '';
                $val->start = $val->shift_date;
                $val->end = $val->shift_date;
                $val->status = $val->status;
                //$val->start_time = date('h:ia', strtotime($val->start_time));
                //$val->end_time = date('h:ia', strtotime($val->end_time));
            }
            $total_duration = add_hour_minute($time);
        }
        $return = array('data' => $dataResult, 'status' => true, 'total_duration' => $total_duration);
        return $return;
    }

    public function get_previous_availiability($reqData)
    {
        $day_difference = 28;
        $member_id = sprintf("%d", $reqData->member_id) ?? 0;
        $start_date = date('Y-m-d', strtotime($reqData->start_date));

        if (isset($reqData->end_date)) {
            $end_date = date('Y-m-d', strtotime($reqData->end_date));
        } else {
            $end_date = date('Y-m-d', strtotime($start_date . " +$day_difference days"));
        }

        $tbl = TBL_PREFIX . 'member_availability_list';
        $this->db->from($tbl);
        $this->db->select("availability_type,availability_date,id");
        $where_ary = array('memberId' => $member_id);
        $this->db->where('availability_date >= ', $start_date);
        $this->db->where('availability_date <= ', $end_date);
        $this->db->where($where_ary, null, false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //echo $this->db->last_query();
        return $dataResult = $query->result_array();
    }

    public function check_availibility_already_exist($rosObj)
    {
        $start_date = $this->db->escape_str($rosObj->start_date);
        $end_date = $this->db->escape_str($rosObj->end_date);
        if (!empty($end_date)) {
            $this->db->select(array("tbl_member_availability.id"));
            $this->db->from('tbl_member_availability');

            $where = "is_default = 2 AND memberId = " . $rosObj->member_id . " AND
            (('" . $start_date . "' BETWEEN date(start_date) AND date(end_date))  or ('" . $end_date . "' BETWEEN date(start_date) AND date(end_date)) OR
            (date(start_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "')  or (date(end_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "')) ";
            $this->db->where($where);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $x = $query->row();
            return (!empty($x)) ? $x : array();
        } else {
            return array();
        }
    }

    public function get_shift_for_availibility($rosObj)
    {
        $member_id = sprintf("%d", $rosObj['member_id']);
        $shift_date = $this->db->escape_str($rosObj['shift_date']);

        $this->db->select(array("tbl_shift.id"));
        $this->db->from('tbl_shift');
        $this->db->join('tbl_shift_member', 'tbl_shift_member.shiftId = tbl_shift.id AND tbl_shift_member.archive=0', 'left');
        $where = "tbl_shift.shift_date = '" . $shift_date . "' AND tbl_shift_member.memberId = " . $member_id . " AND tbl_shift_member.status = '3' AND tbl_shift.status = '7'";
        $this->db->where($where);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        return $query->row();
    }

    public function get_member_fms($reqData, $adminId)
    {
        $limit = sprintf("%d", $reqData->pageSize) ?? 0;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $obj_permission = new classPermission\Permission();
        $incident_fms = $obj_permission->check_permission($adminId, 'incident_fms');

        if (!$incident_fms) {
            $this->db->where('fc.fms_type', '0');
        }
        $available_column = array("id", "event_date", "case_category");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fc.id';
            $direction = 'DESC';
        }

        /*
        if (!empty($filter->srch_box)) {

            $src_columns = array($tbl_fms . ".id as fmsid", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by", "CONCAT(tbl_member.firstname,' ',tbl_member.middlename,' ',tbl_member.lastname)", "CONCAT(tbl_member.firstname,' ',tbl_member.lastname)", "CONCAT(tbl_participant.firstname,' ',tbl_participant.middlename,' ',tbl_participant.lastname)", "CONCAT(tbl_participant.firstname,' ',tbl_participant.lastname)", "tbl_member.id as member_ocs_id", "tbl_participant.id as participant_ocs_id");

            $where = $where . " AND (";
            $sWhere = " (" . $where;
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $sWhere .= $serch_column[0] . " LIKE '%" . $this->db->escape_like_str($filter->srch_box) . "%' OR ";
                } else {
                    $sWhere .= $column_search . " LIKE '%" . $this->db->escape_like_str($filter->srch_box) . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= '))';
        }
        */

        $select_column = array("fc.id", "fc.event_date", "fcac.name as case_category");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        if (!empty($filter->status)) {
            $filter->status = isset($filter->status) && ($filter->status == 2 || $filter->status == -1) ? 0 : $filter->status;
            $this->db->where_in("fc.status", $filter->status);
        }

        $this->db->select("(select description from tbl_fms_case_reason where caseId = fc.id order by id asc limit 1) as short_description");
        $this->db->select("(CASE when fc.status = 0 THEN 'Ongoing' ELSE 'Completed' end) as fms_status", false);

        $this->db->from("tbl_fms_case as fc");
        $this->db->join('tbl_fms_case_category as fcc', 'fcc.caseId = fc.id', 'INNER');
        $this->db->join('tbl_fms_case_all_category as fcac', 'fcac.id = fcc.categoryId', 'INNER');
        $this->db->join('tbl_fms_case_against_detail as fcad', 'fcad.caseId = fc.id AND fcad.against_category = 2', 'left');

        if (!empty($filter->srch_box)) {
            $this->db->join('tbl_participant', 'tbl_participant.id =  tbl_fms_case.initiated_by AND tbl_fms_case.initiated_type = 2', 'left');
            $this->db->join('tbl_member', 'tbl_member.id =  tbl_fms_case.initiated_by AND tbl_fms_case.initiated_type = 1', 'left');
        }
        $memberId = sprintf("%d", $reqData->memberId);
        $this->db->group_by('fc.id');
        $this->db->where("((fc.initiated_by = " . $memberId . " AND fc.initiated_type = 1) OR (fcad.against_by = " . $memberId . ")) ");
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->event_date = date("d/m/Y", strtotime($val->event_date));
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    /**
     * checks if an entry exists for a member and skill
     */
    public function check_member_skill_already_exist($member_id, $skill_id, $id = 0)
    {
        $this->db->select(array('r.display_name'));
        $this->db->from('tbl_member_skill as ms');
        $this->db->join('tbl_references as r', 'r.id = ms.skill_id', 'inner');
        $this->db->where('ms.archive', 0);
        if ($id > 0)
            $this->db->where('ms.id != ', $id);

        $this->db->where("ms.member_id", $member_id);
        $this->db->where("ms.skill_id", $skill_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return $result;
    }

    /**
     * creates a new member skill record or updates an existing one
     */
    function create_update_member_skills($data, $adminId)
    {
        $member_skill_id = $data['id'] ?? 0;

        // echo "-".$data["skill_level_id"]."-";
        $skilldata = [
            "member_id" => $data['member_id'],
            "skill_id" => $data['skill_id'],
            "skill_level_id" => (!empty($data["skill_level_id"])) ? $data["skill_level_id"] : null,
            "start_date" => $data['start_date'],
            "end_date" => $data["end_date"] ?? null,
            "archive" => 0
        ];

        if ($member_skill_id) {
            $skilldata["updated_by"] = $adminId;
            $skilldata["updated"] = DATE_TIME;
            $this->basic_model->update_records("member_skill", $skilldata, ["id" => $member_skill_id]);
            return $member_skill_id;
        } else {
            $skilldata["updated"] = DATE_TIME;
            $skilldata["updated_by"] = $adminId;
            $skilldata["created"] = DATE_TIME;
            $skilldata["created_by"] = $adminId;
            return $this->basic_model->insert_records("member_skill", $skilldata, $multiple = FALSE);
        }
    }

    /**
     * creates a new member unavailability record or updates an existing one
     */
    function create_update_member_unavailability($data, $adminId)
    {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $member_unavailability_id = sprintf("%d",$data['id']) ?? 0;
        $member_id = sprintf("%d",$data['member_id']) ?? 0;

        # appending timings into date fields
        if(!isset($data['start_datetime']))
            $data['start_datetime'] = null;
        if(!isset($data['end_datetime']))
            $data['end_datetime'] = null;

        if(!empty($data['start_date']) || !empty($data['start_time']))
            $data['start_datetime'] = $data['start_date']." ".$data['start_time'];

        if(!empty($data['end_date']) || !empty($data['end_time']))
            $data['end_datetime'] = $data['end_date']." ".$data['end_time'];

        # validation rule
        $validation_rules = [
            array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required'),
            array(
                'field' => 'start_datetime', 'label' => 'Start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect Start date & time',
                ]
            )
        ];

        # checking end date & time
        if (!empty($data['end_date'])) {
            $validation_rules[] = array(
                'field' => 'end_datetime', 'label' => 'End date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect End date & time',
                ]
            );
        }

        # set data in libray for validate
        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        # appending timings into date fields
        if($data['start_datetime']) {
            $data['start_date'] = $data['start_datetime'];
        }
        if($data['end_datetime']) {
            $data['end_date'] = $data['end_datetime'];
        }

        # end date & time should be greater than start date & time
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $date1 = date_create(DateFormate($data['start_date'], 'Y-m-d H:i:s'));
            $date2 = date_create(DateFormate($data['end_date'], 'Y-m-d H:i:s'));
            $diff = date_diff($date1, $date2);
            // pr($diff);
            if (isset($diff->invert) && ($diff->invert > 0 || ($diff->invert == 0 && $diff->y == 0 && $diff->m == 0 && $diff->d == 0 && $diff->h == 0 && $diff->i == 0))) {
                $response = [
                    "status" => false,
                    "error" => "Start date-time: " . date("d/m/Y g:i A", strtotime($data['start_date'])) . " should be lower to end date-time: " . date("d/m/Y g:i A", strtotime($data['end_date']))
                ];
                return $response;
            }
        }

        # checking if the unavailability for member is not previously added
        $rows = $this->Member_model->check_member_unavailability_already_exist($data['member_id'], $data['start_date'], $data['end_date'],$member_unavailability_id);
        if(!empty($rows))
        {
            $errors = 'Unavailability overlaps with existing one(s) for this member';
            return array('status' => false, 'error' => $errors);
        }

        # checking if the unavailability for member is not overlapping the accepted shifts
        $CI = &get_instance();
        if ($CI->config->item('rules_service_enabled') == 'true') {
            $rulesResponse = $this->Member_model->check_member_unavailability_with_accepted_shift($member_unavailability_id, $data['member_id'], $data['start_date'], $data['end_date']);

            if (isset($rulesResponse->status)) {
                if ($rulesResponse->status == 'FAIL') {
                    $return = array('status' => false, 'error' => $rulesResponse->message);
                    return $return;
                }
            } else if (isset($rulesResponse->errorMessage)) {
                $return = array('status' => false, 'error' => $rulesResponse->errorMessage);
                return $return;
            } else {
                // e.g. timeout
                $return = array('status' => false, 'error' => 'API ERROR');
                return $return;
            }
        } else {
            $rows = $this->Member_model->check_member_unavailability_with_accepted_shift($member_unavailability_id, $data['member_id'], $data['start_date'], $data['end_date']);
            if ($rows) {
                $errors = 'Unavailability overlaps with existing scheduled shift(s), please choose other timings';
                $return = array('status' => false, 'error' => $errors);
                return $return;
            }
        }

        # call create/update member unavailability modal function
        $member_unavailability_id = $this->Member_model->populate_member_unavailability($data, $adminId);

        # check $member_unavailability_id is not empty
        if (!$member_unavailability_id) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Support worker unavailability has been updated successfully.';
        } else {
            $msg = 'Support worker unavailability has been created successfully.';
        }
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * checking if start/end dates of unavailability is not overlapping existing unavailability
     */
    function check_member_unavailability_already_exist($member_id, $start_datetime, $end_datetime, $member_unavailability_id = null) {
        $start_datetime = date("Y-m-d H:i:s", strtotime($start_datetime));
        if($end_datetime)
        $end_datetime = date("Y-m-d H:i:s", strtotime($end_datetime));
        $select_column = ["mu.id"];
        $this->db->select($select_column);
        $this->db->from('tbl_member_unavailability as mu');
        $this->db->join('tbl_member as m', 'm.id = mu.member_id', 'inner');
        $this->db->where('mu.archive', 0);
        $this->db->where('m.archive', 0);
        $this->db->where('mu.member_id', $member_id);
        if($member_unavailability_id)
            $this->db->where('mu.id != '.$member_unavailability_id);
        if($end_datetime) {
            $this->db->where("((mu.start_date >= '{$start_datetime}' and mu.start_date < '{$end_datetime}') or (mu.end_date > '{$start_datetime}' and mu.end_date <= '{$end_datetime}') or (mu.start_date <= '{$start_datetime}' and mu.end_date >= '{$end_datetime}'))");
        }
        else {
            $this->db->where("(mu.start_date >= '{$start_datetime}' or mu.end_date > '{$start_datetime}')");
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        // last_query();
        return ((isset($result) && count($result) > 0) ? 1 : 0);
    }

    /**
     * checking if start/end dates of unavailability is not overlapping accepted shifts
     */
    function check_member_unavailability_with_accepted_shift($member_unavailability_id, $member_id, $start_datetime, $end_datetime) {
        $start_datetime = date("Y-m-d H:i:s", strtotime($start_datetime));
        if($end_datetime)
        $end_datetime = date("Y-m-d H:i:s", strtotime($end_datetime));

        $CI = & get_instance();
        if ($CI->config->item('rules_service_enabled') == 'true') {
            $unavailabilityFact = new stdClass();
            $unavailabilityFact->memberId = $member_id;
            $unavailabilityFact->startDate = str_replace(" ", "T", $start_datetime) . '.000';
            if (isset($end_datetime) && strlen($end_datetime) > 0) {
                $unavailabilityFact->endDate = str_replace(" ", "T", $end_datetime) . '.000';
            }

            $fact = new stdClass();
            $fact->clazz = "Unavailability";
            $fact->json = json_encode($unavailabilityFact);

            $execution = new stdClass();
            $execution->persist =  false;
            $execution->facts = array($fact);

            $CI = &get_instance();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $CI->config->item('rules_service_url') . '/executions');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($execution));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);

            curl_close($ch);

            $return = json_decode($result);
            return $return;
        } else {
            $select_column = ["sm.id"];
            $this->db->select($select_column);
            $this->db->from('tbl_shift as s');
            $this->db->join('tbl_shift_member as sm', 'sm.id = s.accepted_shift_member_id', 'inner');
            $this->db->where('sm.archive', 0);
            $this->db->where('s.archive', 0);
            $this->db->where('sm.member_id', $member_id);
            if($end_datetime) {
                $this->db->where("((s.scheduled_start_datetime >= '{$start_datetime}' and s.scheduled_start_datetime < '{$end_datetime}') or (s.scheduled_end_datetime > '{$start_datetime}' and s.scheduled_end_datetime <= '{$end_datetime}') or (s.scheduled_start_datetime <= '{$start_datetime}' and s.scheduled_end_datetime >= '{$end_datetime}'))");
            }
            else {
                $this->db->where("(s.scheduled_start_datetime >= '{$start_datetime}' or s.scheduled_end_datetime > '{$start_datetime}')");
            }
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $result = $query->result();
            // last_query();
            return ((isset($result) && count($result) > 0) ? 1 : 0);
        }
    }

    /**
     * inserts or updates the member unavailability record in the database
     */
    function populate_member_unavailability($data, $adminId)
    {
        $member_unavailability_id = sprintf("%d",$data['id']) ?? 0;

        $unavailabilitydata = [
            "member_id" => $data['member_id'],
            "type_id" => (!empty($data["type_id"])) ? $data["type_id"] : null,
            "keypay_ref_id" => (!empty($data["keypay_ref_id"])) ? $data["keypay_ref_id"] : null,
            "start_date" => date("Y-m-d H:i:s", strtotime($data['start_date'])),
            "end_date" => date("Y-m-d H:i:s", strtotime($data['end_date'])),
            "archive" => 0
        ];

        if ($member_unavailability_id) {
            $unavailabilitydata["updated"] = DATE_TIME;
            $unavailabilitydata["updated_by"] = $adminId;
            $this->basic_model->update_records("member_unavailability", $unavailabilitydata, ["id" => $member_unavailability_id]);
            return $member_unavailability_id;
        } else {
            $unavailabilitydata["updated"] = DATE_TIME;
            $unavailabilitydata["created"] = DATE_TIME;
            $unavailabilitydata["created_by"] = $adminId;
            return $this->basic_model->insert_records("member_unavailability", $unavailabilitydata, $multiple = FALSE);
        }
    }

    /**
     * Fetching only the fullname of a given member
     */
    function get_member_fullname($id)
    {
        $row = $this->Basic_model->get_row('member', ['fullname'], ['id' => sprintf("%d",$id), 'archive' => 0]);
        return ($row && isset($row->fullname)) ? $row->fullname : '';
    }

    /**
     * it inserts a new member record into tbl_member table or updates an existing one
     * if the 'id' element is passed
     */
    function insert_update_member($data, $adminId, $is_applicant_created_as_member)
    {
        $member_id = sprintf("%d",$data['id']) ?? 0;
        $firstname = $lastname = $keypay_row = null;
        
        $email_row = $this->basic_model->get_row('person', ['username','uuid'], ['id' => $data['account_person']->value, 'archive' => 0]);
        // get applicant details
        $applicant_data = null;
        if (!empty($email_row) && !empty($email_row->uuid)) {
            $applicant_data = $this->basic_model->get_row('member', ['id','username','applicant_id'], ['uuid' => $email_row->uuid,"enable_app_access" => 1,"source_type" => 1,"is_new_member" => 1,"archive" =>0 ]);
        }

        $contact_row = $this->basic_model->get_row('person', ['firstname', 'lastname'], ['id' => $data['account_person']->value, 'archive' => 0]);        
        $firstname = $contact_row->firstname;
        $lastname = $contact_row->lastname;

        if (!@$data['hours_per_week'])
            $data['hours_per_week'] = NULL;
        if (!@$data['max_dis_to_travel'])
            $data['max_dis_to_travel'] = NULL;
        if (!@$data['mem_experience'])
            $data['mem_experience'] = NULL;

            $member_data = [
                "fullname" => $data['fullname'],
                "hours_per_week" => $data['hours_per_week'],
                "status" => $data["status"] ?? '0',
                "archive" => 0,
                "max_dis_to_travel" => $data['max_dis_to_travel'],
                "mem_experience" => $data['mem_experience'],
                'previous_name'=>isset($data['previous_name'])&&$data['previous_name']?$data['previous_name']:'',
                'middlename'=>isset($data['middlename'])&&$data['middlename']?$data['middlename']:''
            ];

            if(!$is_applicant_created_as_member){
                $member_data['person_id'] =$data['account_person']->value;
                $member_data['firstname'] =$firstname;
                $member_data['lastname'] =$lastname;
                $member_data['department'] =2;
                $member_data['enable_app_access'] =1;
                $member_data['source_type'] =1;
                $member_data['is_new_member'] =1;
            }

       

        if ($member_id) {
            $member_data["updated_by"] = $adminId;
            $member_data["updated_date"] = DATE_TIME;
            $this->basic_model->update_records("member", $member_data, ["id" => $member_id]);

            if(($data["status"]=='0' || $data["status"]=='1') && !empty($data["uuid"])){
                // remove token in user table to make as invalid req
            $this->basic_model->update_records('users', ['status' => $data["status"], 'token' => NULL, 'updated_at' => DATE_TIME], ['id' => $data["uuid"], 'user_type'=> MEMBER_PORTAL]);
            }
            
            $keypay_row = $this->basic_model->get_row('keypay_kiosks_emp_mapping_for_member', 
            ['keypay_emp_id'], ['member_id' => $member_id, 'archive' => 0]);

            if($keypay_row && isset($data['keypay_emp_id']) && empty($data['keypay_emp_id'])) {
                $this->basic_model->update_records("keypay_kiosks_emp_mapping_for_member",
                ['keypay_emp_id' => null], ["member_id" => $member_id]);
            }

        } else if ($applicant_data && $applicant_data->applicant_id) {
            $member_data["updated_date"] = DATE_TIME;
            $member_data["updated_by"] = $adminId;
            $this->basic_model->update_records("member", $member_data, ["applicant_id" => $applicant_data->applicant_id]);
            $member_id = $applicant_data->id;
        } else {

            $email_row = $this->basic_model->get_row('person_email', ['email'], ['person_id' => $data['account_person']->value, 'archive' => 0, 'primary_email' => 1]);
            $username = (isset($email_row) && isset($email_row->email)) ? $email_row->email : $firstname . "." . $lastname;
            $pin = random_genrate_password(6);

            $member_data["pin"] = password_hash($pin, PASSWORD_BCRYPT);
            $member_data["created"] = DATE_TIME;
            $member_data["created_by"] = $adminId;

            $createUserUuid = $this->basic_model->insert_records('users', ["username"=>$username,"status"=>1,"user_type"=>2], $multiple = FALSE);

            $member_data['uuid'] = $createUserUuid;
            $this->basic_model->update_records("person", ["uuid"=>$createUserUuid], ["id" => $data['account_person']->value]);
            $member_id = $this->basic_model->insert_records("member", $member_data, $multiple = FALSE);
        }

        # adding/updating member keypay id
        if(isset($data['keypay_emp_id']) && !empty($data['keypay_emp_id'])) { 
            $this->insert_update_member_keypay_id($member_id, $data['keypay_emp_id'], $adminId);
        }

        return $member_id;
    }

    /**
     * adding updating member's keypay reference id
     */
    function insert_update_member_keypay_id($member_id, $keypay_emp_id, $adminId) {
        $existing_keypay_id = $this->get_member_key_pay_id($member_id);
        $newdata['member_id'] = $member_id;
        $newdata['keypay_emp_id'] = $keypay_emp_id;

        if($existing_keypay_id) {
            $newdata["updated"] = DATE_TIME;
            $newdata["updated_by"] = $adminId;
            $this->basic_model->update_records("keypay_kiosks_emp_mapping_for_member", $newdata, ["member_id" => $member_id]);
        }
        else {
            $newdata["updated"] = DATE_TIME;
            $newdata["created"] = DATE_TIME;
            $newdata["created_by"] = $adminId;
            $this->basic_model->insert_records("keypay_kiosks_emp_mapping_for_member", $newdata, $multiple = FALSE);
        }
    }

    /**
     * archiving member unavailability
     */
    function archive_member_unavailability($data, $adminId)
    {
        $id = isset($data['id']) ? sprintf("%d",$data['id']) : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the member unavailability exist?
        $result = $this->Member_model->get_member_unavailability_details($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Member unavailability does not exist anymore."];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("member_unavailability", $upd_data, ["id" => $id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        $response = ['status' => true, 'msg' => "Successfully archived member unavailability"];
        return $response;
    }

    /**
     * creates a new member record or updates an existing one
     * also updates the reference data of languages, likes and transport selections
     */
    function create_member($data, $adminId, $is_applicant_created_as_member=false)
    {
        // pr($data);
        # create/update member first
        $member_id = $this->insert_update_member($data, $adminId, $is_applicant_created_as_member);

        # remove existing entries of references for current member
        $this->basic_model->update_records('member_ref_data', array('archive' => '1', "updated" => DATE_TIME, "updated_by" => $adminId), array('member_id' => $member_id));

        # adding languages
        if (!empty($data['language_selection'])) {
            $languages = null;
            foreach ($data['language_selection'] as $refobj) {
                $singarr['member_id'] = $member_id;
                $singarr['ref_id'] = $refobj->id;
                $singarr['archive'] = 0;
                $singarr["created"] = DATE_TIME;
                $singarr["created_by"] = $adminId;
                $languages[] = $singarr;
            }
            $this->basic_model->insert_records("member_ref_data", $languages, $multiple = TRUE);
        }

        # adding transport options
        if (!empty($data['transport_selection'])) {
            $transports = null;
            foreach ($data['transport_selection'] as $refobj) {
                $singarr['member_id'] = $member_id;
                $singarr['ref_id'] = $refobj->id;
                $singarr['archive'] = 0;
                $singarr["created"] = DATE_TIME;
                $singarr["created_by"] = $adminId;
                $transports[] = $singarr;
            }
            $this->basic_model->insert_records("member_ref_data", $transports, $multiple = TRUE);
        }

        # adding like options
        if (!empty($data['like_selection'])) {
            $likes = null;
            foreach ($data['like_selection'] as $refobj) {
                $singarr['member_id'] = $member_id;
                $singarr['ref_id'] = $refobj->id;
                $singarr['archive'] = 0;
                $singarr["created"] = DATE_TIME;
                $singarr["created_by"] = $adminId;
                $likes[] = $singarr;
            }
            $this->basic_model->insert_records("member_ref_data", $likes, $multiple = TRUE);
        }

        return $member_id;
    }

    public function get_imail_call_list_delete($reqData)
    {
        $memberId = sprintf("%d",$reqData->data->memberId);
        $view_by = $reqData->data->view_by;
        $msg_ary = array();
        $call_ary = array();
        $main_array = array();
        $csv_array = array();

        if ($view_by == 'iMail' || $view_by == '') {
            $tbl_external_message = TBL_PREFIX . 'external_message';
            $tbl_external_message_content = TBL_PREFIX . 'external_message_content';

            if (!empty($reqData->data->start_date)) {
                $this->db->where($tbl_external_message_content . ".created >= '" . date('Y-m-d', strtotime($reqData->data->start_date)) . "'");
            }
            if (!empty($reqData->data->end_date)) {
                $this->db->where($tbl_external_message_content . ".created <= '" . date('Y-m-d', strtotime($reqData->data->end_date)) . "'");
            }

            $sWhere = array('tbl_external_message_content.userId' => $memberId);
            $this->db->select(array($tbl_external_message . '.id', $tbl_external_message . '.title', "DATE_FORMAT(tbl_external_message_content.created,'%d/%m/%Y') as created_date", "'Imail'  as contact_type", $tbl_external_message . '.title as csv_title'));
            $this->db->from($tbl_external_message);
            $this->db->join($tbl_external_message_content, $tbl_external_message . '.id = ' . $tbl_external_message_content . '.messageId', 'left');
            $this->db->where($sWhere);
            $this->db->order_by($tbl_external_message . '.id', 'desc');
            $this->db->group_by($tbl_external_message . '.id');
            $query = $this->db->get();
            $obj_1 = $query->result();
            $main_array['msg_data'] = $obj_1;
        }

        if ($view_by == 'phone_call' || $view_by == '') {
            $tbl_member_phone = TBL_PREFIX . 'member_phone';
            $this->db->select(array($tbl_member_phone . '.phone'));
            $this->db->from($tbl_member_phone);
            $this->db->where($tbl_member_phone . '.memberId', $memberId);

            $get_ph_query = $this->db->get();
            $member_ph_data = $get_ph_query->result_array();
            $number = array();
            if (!empty($member_ph_data)) {
                foreach ($member_ph_data as $key => $value) {
                    $number[] = $value['phone'];
                }
            }

            $all_number = implode(',', $number);
            if ($all_number != '') {
                $tbl_call_log = TBL_PREFIX . 'call_log';
                $this->db->select(array($tbl_call_log . '.id', $tbl_call_log . '.audio_url', "DATE_FORMAT(tbl_call_log.created,'%d/%m/%Y') as created_date", "'Call'  as contact_type", $tbl_call_log . '.audio_url as csv_title'));
                $this->db->from($tbl_call_log);
                $this->db->group_start();
                $this->db->or_where_in($tbl_call_log . '.caller_number', $all_number);
                $this->db->or_where_in($tbl_call_log . '.receiver_number', $all_number);
                $this->db->group_end();
                if (!empty($reqData->data->start_date)) {
                    $this->db->where("Date(tbl_call_log.created) >= '" . date('Y-m-d', strtotime($reqData->data->start_date)) . "'");
                }
                if (!empty($reqData->data->end_date)) {
                    $this->db->where("Date(tbl_call_log.created) <= '" . date('Y-m-d', strtotime($reqData->data->end_date)) . "'");
                }
                $this->db->order_by($tbl_call_log . '.id', 'desc');
                $query = $this->db->get();

                $obj_2 = $query->result_array();
                $main_array['call_data'] = $obj_2;
            }
        }

        $main_array['call_data'] = isset($main_array['call_data']) && !empty($main_array['call_data']) ? $main_array['call_data'] : array();
        $main_array['msg_data'] = isset($main_array['msg_data']) && !empty($main_array['msg_data']) ? $main_array['msg_data'] : array();
        $main_array['csv_array'] = array_merge($main_array['call_data'], $main_array['msg_data']);
        return $main_array;
    }

    function create_member_address($member_address) {
        return $this->insert_records('member_address', $member_address->flat_array());
    }

    function update_member_address($member_address, $where) {
        return $this->update_records('member_address', $member_address->flat_array(), $where);
    }

    function delete_member_address($where) {
        return $this->update_records('member_address', array('archive' => '1', 'updated' => DATE_TIME), $where);
    }

    public function update_Member_profile($post_data) {
        //error_reporting(0);
        #pr($post_data);
        $create_user_mail = FALSE;
        if (!empty($post_data)) {
            $member_id = isset($post_data['ocs_id']) && $post_data['ocs_id'] > 0 ? sprintf("%d",$post_data['ocs_id']) : 0;

            $member_adr = array();
            $dob = date('Y-m-d', strtotime($post_data['dob']));
            $prefer_contact = $post_data['prefer_contact'];

            if ($member_id > 0) {
                $this->Basic_model->update_records('member', array('dob' => $dob, 'prefer_contact' => $prefer_contact, 'gender' => $post_data['gender'], 'updated_date' => DATE_TIME), array('id' => $member_id));
                /* First delete old record and then insert new record */
                $this->delete_member_address(array('memberId' => $member_id));
            } else {
                $create_user_mail = true;
                $fullname = $post_data['firstname'] . ' ' . $post_data['lastname'];
                $pin = rand(100000, 999999);

                $encrypted_pin = password_hash($pin, PASSWORD_BCRYPT);

                $save_data = array(
                    'firstname' => $post_data['firstname'],
                    'lastname' => $post_data['lastname'],
                    'gender' => $post_data['gender'],
                    'dob' => $post_data['dob'],
                    'prefer_contact' => $prefer_contact,
                    'created' => '',
                    'pin' => $encrypted_pin,
                    'department' => 2,
                    'created' => DATE_TIME,
                    'updated' => DATE_TIME
                );
                $member_id = $this->Basic_model->insert_records('member', $save_data);
            }

            if (!empty($post_data['completeAddress'])) {
               foreach ($post_data['completeAddress'] as $key => $value) {
                    $address = $value->street . ' ' . $value->city . ' ' . $value->statename . ' ' . $value->postal . ' ' . 'Australia';
                    $lat_long = getLatLong($address);

                    $this->CI->load->file(APPPATH.'Classes\member\MemberAddress.php');
                    $member_address = new MemberAddress();
                    $member_address->setMemberId($this->member_id);
                    $member_address->setStreet($value->street);
                    $member_address->setPrimaryAddress(($key == 0) ? 1 : 2);
                    $member_address->setCity(isset($value->city) ? $value->city : '');
                    $member_address->setPostal(isset($value->postal)?$value->postal:$value->postal_code);
                    $member_address->setState($value->state);
                    $member_address->setLat(isset($lat_long['lat']) ? $lat_long['lat'] : '');
                    $member_address->setLong(isset($lat_long['long']) ? $lat_long['long'] : '');
                    if (isset($value->id) && $value->id != '') {
                        $this->update_member_address($member_adr, array('id' => $value->id));
                    } else {
                        $this->create_member_address($member_address);
                    }

                }
            }

            if (!empty($post_data['phone_ary'])) {
                if ($member_id > 0) {
                    /* First delete old record and then insert new record */
                    $this->Basic_model->update_records('member_phone', array('archive' => '1', 'updated'=> DATE_TIME), array('memberId' => $member_id));
                }
                $member_ph = array();
                foreach ($post_data['phone_ary'] as $kk => $ph) {
                    $member_ph = array(
                        'memberId' => $member_id,
                        'phone' => $ph->phone,
                        'archive' => '0',
                        'primary_phone' => isset($kk) && $kk == 0 ? '1' : '2',
                        'updated' => DATE_TIME
                    );

                    if (isset($ph->id) && $ph->id != '') {
                        $this->basic_model->update_records('member_phone', $member_ph, array('id' => $ph->id));
                    } else {
                        #$member_ph['primary_phone'] = 2;
                        $member_ph['created'] = DATE_TIME;
                        $this->Basic_model->insert_records('member_phone', $member_ph);
                    }
                }
            }


            if (!empty($post_data['email_ary'])) {
                if ($member_id > 0) {
                    /* First delete old record and then insert new record */
                    $this->Basic_model->update_records('member_email', array('archive' => '1', 'updated' => DATE_TIME), array('memberId' => $member_id));
                }
                $member_mail = array();
                foreach ($post_data['email_ary'] as $ke => $email) {
                    $member_mail = array(
                        'memberId' => $member_id,
                        'email' => $email->email,
                        'primary_email' => isset($ke) && $ke == 0 ? '1' : '2',
                        'archive' => '0',
                        'updated' => DATE_TIME
                    );
                    if (isset($email->id) && $email->id != '') {
                        $this->basic_model->update_records('member_email', $member_mail, array('id' => $email->id));
                    } else {
                        #$member_mail['primary_email'] = 2;
                        $member_mail['created'] = DATE_TIME;
                        $this->Basic_model->insert_records('member_email', $member_mail);
                    }
                }
            }

            if (!empty($post_data['kin_ary'])) {
                $member_kin = array();
                if ($member_id > 0) {
                    /* First delete old record and then insert new record */
                    $this->Basic_model->update_records('member_kin', array('archive' => '1', 'updated' => DATE_TIME), array('memberId' => $member_id));
                }

                foreach ($post_data['kin_ary'] as $key => $value) {
                    $member_kin = array(
                        'firstname' => $value->firstname,
                        'lastname' => isset($value->lastname) ? $value->lastname : '',
                        'relation' => $value->relation,
                        'phone' => $value->phone,
                        'email' => $value->kin_email,
                        'memberId' => $member_id,
                        'archive' => '0',
                        'updated' => DATE_TIME
                    );

                    if (isset($value->id) && $value->id != '') {
                        $this->basic_model->update_records('member_kin', $member_kin, array('id' => $value->id));
                    } else {
                        $member_kin['primary_kin'] = $key == 0 ? 1 : 2;
                        $member_kin['created'] = DATE_TIME;
                        $this->Basic_model->insert_records('member_kin', $member_kin);
                    }
                }
            }

            // send welcome mail to member
            if ($create_user_mail) {
                $mail_param = array('fullname' => $fullname, 'userid' => $member_id, 'pin' => $pin);
                send_welcome_mail_to_member_user_hcm_created($mail_param);
            }
            return array('id' => $member_id);
        }
    }

    function get_member_position_award($responseAry)
    {
        $where_array = array('memberId' => sprintf("%d",$responseAry->member_id));
        $status = $responseAry->status;

        $where_array['mps.archive'] = ($status == 'Archive') ? '1' : '0';

        $columns = array('mps.id', 'mps.work_area', 'mps.pay_point', 'mps.archive', 'mps.award', 'mps.level', 'cp.point_name', 'cl.level_name', 'awa.work_area as title_work_area');
        $this->db->select($columns);
        $this->db->where($where_array);
        $this->db->from('tbl_member_position_award as mps');
        $this->db->join('tbl_classification_level as cl', 'cl.id = mps.level AND cl.archive=0', 'inner');
        $this->db->join('tbl_classification_point as cp', 'cp.id = mps.pay_point AND cp.archive=0', 'inner');
        $this->db->join('tbl_recruitment_applicant_work_area as awa', 'awa.id = mps.work_area AND awa.archived=0 AND awa.status = 1', 'inner');
        $res = $this->db->get();

        return $res->result_array();
    }

    public function get_member_skill($memberId, $type)
    {
        $this->load->model('Member_action_model');
        $skill_ary = $this->Member_action_model->get_all_skills($memberId, $type);
        $str = '';
        if (!empty($skill_ary)) {
            foreach ($skill_ary as $key => $skill) {
                if ($skill->key_name != 'other' && isset($skill->checked) && $skill->checked == 1)
                    $str .= $skill->name . ',';
                else if ($skill->key_name == 'other' && isset($skill->checked) && $skill->checked == 1)
                    $str .= $skill->other_title . ',';
            }
            $str = rtrim($str, ',');
        }
        return $str;
    }

    /**
     * Search for existing member name and return all matching results
     * @param $accountName string
     * @return Array
     */
    public function get_member_name_search($accountName = '')
    {
        $this->db->select(["id AS value", "fullname AS label"]);
        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->like('fullname', $accountName);
        $this->db->where(['archive' => 0]);
        $res = $this->db->get();
        return $res->result_array();
    }

    public function create_update_member_role($request, $bulk_import = NULL) {
        if(!$bulk_import) {
            $reqData = $this->request_handler();
            $adminId = $reqData->adminId;
            $data = (array) $request->data;
        } else {
            $data = $bulk_import;
            $adminId = $bulk_import['adminId'];
        }
        $start_time = date('Y-m-d', strtotime($data['start_date'])) . ' ' . $data['start_time'];
        $end_time = '';
        if (!empty($data['end_date']) && $data['end_date'] != 'Invalid date') {
            $end_date = date('Y-m-d', strtotime($data['end_date']));
            $eTime = isset($data['end_time']) && $data['end_time'] != 'Invalid date' ? $data['end_time'] : '00:00:00';
            $end_time = date('Y-m-d', strtotime($end_date)) . ' ' . $eTime;
        }
        //end date can't be lesser than start date
        if (!empty($end_time) && strtotime($end_time) <= strtotime(($start_time))) {
            return ['status' => false, 'error' => "End Date & Time can't be less than Start Date & Time"];
        }
        // check whether the same role exist for the same date range
        $this->db->select('id');
        $this->db->from(TBL_PREFIX . 'member_role_mapping AS mrm');
        $where = ['member_id' => $data['member_id'], 'member_role_id' => $data['role_id'], 'archive' => 0];
        if (!empty($data['member_role_id'])) {
            $where['id !='] = $data['member_role_id'];
        }
        $this->db->where($where);
        $this->db->group_start();
        $this->db->group_start();
        $this->db->where('start_time <', $start_time);
        $this->db->where('end_time >', $start_time);
        $this->db->group_end();

        if (!empty($end_time)) {
            $this->db->or_group_start();
            $this->db->where('start_time <', $end_time);
            $this->db->where('end_time >', $end_time);
            $this->db->group_end();
        }
        $this->db->or_where(['end_time' => '0000-00-00 00:00:00']);
        $this->db->group_end();
        $res = $this->db->get();
        $rows = $res->result_array();
        if (count($rows)) {
            return ['status' => false, 'error' => "Support worker Role overlapping with existing role"];
        }
        $role_data = [
            "member_id" => $data['member_id'],
            "member_role_id" => $data['role_id'],
            "start_time" => $start_time,
            "end_time" => $end_time,
            "pay_point" => $data["pay_point"],
            "level" => $data["level"],
            "employment_type" => $data['employment_type'],
            "updated" => date('Y-m-d H:i:s'),
            "updated_by" => $adminId

        ];
        if (!empty($data['member_role_id'])) {
            $member_role_id = $data['member_role_id'];
            $this->basic_model->update_records("member_role_mapping", $role_data, ["id" => $member_role_id]);
            $result = 1;
        } else {
            $role_data['created_by'] = $adminId;
            $role_data['created'] = date('Y-m-d H:i:s');
            $result = $this->basic_model->insert_records('member_role_mapping', $role_data);
        }
        if (!$result) {
            $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
        } else {
            $msg = "Support worker Role successfully updated";
            if (!empty($data['changed_member'])) {
                $msg = "Role is being created for " . $data['changed_member'];
            }
            $response = ['status' => true, 'msg' => $msg];
        }
        return $response;
    }

    public function get_member_roles($memberId = 0, $memberRoleId = 0, $limit = 0)
    {
        $columns = array('mrm.id AS member_role_id', 'mrm.member_id', 'mr.name AS role_name', 'mrm.member_role_id AS role_id', 'mrm.start_time AS role_start_time', ' mrm.end_time AS role_end_time', 'mrm.pay_point', 'mrm.level', 'mrm.employment_type', 'rf.display_name as employment_type_name');

        $where_array = array('mrm.archive' => 0, 'mr.archive' => 0);
        if (!empty($memberId)) {
            $where_array['mrm.member_id'] = $memberId;
        }
        if (!empty($memberRoleId)) {
            $where_array['mrm.id'] = $memberRoleId;
            $this->db->join(TBL_PREFIX . 'member AS m', 'mrm.member_id=m.id', 'left');
            $columns[] = 'm.fullname AS member_name';
        }
        $this->db->select($columns);
        $this->db->where($where_array);
        $this->db->from(TBL_PREFIX . 'member_role_mapping AS mrm');
        $this->db->join(TBL_PREFIX . 'member_role AS mr', 'mrm.member_role_id=mr.id', 'left');
        $this->db->join(TBL_PREFIX . 'references rf', 'mrm.employment_type = rf.id', 'left');
        if (!empty($limit)) {
            $this->db->limit($limit, 0);
        }
        $this->db->order_by('mrm.id', 'asc');
        $res = $this->db->get();
        return $res->result_array();
    }

    /**
     * fetching the active member role details (pay point, pay level and employment type)
     */
    public function get_member_active_roles($member_id = 0, $role_id = null, $check_date = null) {

        $columns = array('mrm.pay_point', 'mrm.level', 'mrm.employment_type');

        $where_array = ['mrm.archive' => 0, 'mr.archive' => 0];
        if (!empty($member_id))
            $where_array['m.id'] = sprintf("%d",$member_id);

        if(!empty($role_id))
            $where_array['mrm.member_role_id'] = sprintf("%d",$role_id);

        $this->db->select($columns);
        $this->db->from(TBL_PREFIX.'member_role_mapping AS mrm');
        $this->db->join(TBL_PREFIX.'member_role AS mr', 'mrm.member_role_id = mr.id', 'inner');
        $this->db->join(TBL_PREFIX.'references rf', 'mrm.employment_type = rf.id', 'inner');
        $this->db->join(TBL_PREFIX.'member AS m', 'mrm.member_id = m.id', 'inner');
        $this->db->where($where_array);
        if(!empty($check_date)) {
            $this->db->where("date(mr.start_date) <= '".$check_date."' and date(mr.end_date) >= '".$check_date."'");
            $this->db->where("mrm.start_time <= '".$check_date." 00:00:00' and (CASE WHEN mrm.end_time != '0000-00-00 00:00:00' THEN mrm.end_time >= '".$check_date." 00:00:00' ELSE 1= 1 END)", null, false);
        }
        $this->db->order_by('mrm.id', 'asc');
        $res = $this->db->get();
        return $res->result_array();
    }

    public function archive_member_role($memberRoleId = 0) {
        return $this->basic_model->update_records("member_role_mapping", ['archive' => 1, 'updated' => DATE_TIME], ["id" => $memberRoleId]);
    }

    public function request_handler()
    {
        return request_handler('access_member');
    }
    // Check the contact email already exist in member
    function check_contact_exist_in_member($data) {
        $this->db->select('m.id');
        $this->db->from('tbl_member as m');
        if(!empty($data['id'])){
            $this->db->where(['m.person_id'=> $data['account_person']->value, 'm.id !='=>$data['id'], 'm.archive'=>0]);
        }else{
            $this->db->where(['m.person_id'=> $data['account_person']->value, 'm.archive'=>0]);
        }
       

        $query = $this->db->get();
        $res = $query->result();
        if(!$res){
            $result =  ['status' => false];
        }else{
            $result = ['status' => true, 'msg' => "This contact already assigned to a member"];
        }
        return $result;
    }

    // Check the contact email already exist in member
    function check_the_email_is_applicant_or_contact($data, $adminId) {

        //get email from person
        $email_row = $this->basic_model->get_row('person_email', ['email'], ['person_id' => $data['account_person']->value, 'archive' => 0, 'primary_email' => 1]);
        if($email_row && $email_row->email){
            //Check the email is applicant and its Hired or not
            $applicant_result = $this->basic_model->get_row('recruitment_applicant_email', ['applicant_id'], ['email' => $email_row->email, 'archive' => 0]);
            if(!empty($applicant_result)){
                $appl_check = $this->Recruitment_applicant_move_to_hcm->check_applicant_already_active($applicant_result->applicant_id);

                if (!empty($appl_check)) {
                   $result = ['not_valid' =>'true','msg'=>'The Contact has been already assigned to a member'];
                }else{
                    // Check the applicant already hired
                    $check_applicant_status = $this->basic_model->get_record_where('recruitment_applicant_applied_application', ['id','applicant_id','application_process_status'], ['applicant_id' => $applicant_result->applicant_id, 'application_process_status'=>7]);   
                    
                    if(empty($check_applicant_status)){
                        $result = ['not_valid' =>'true','msg'=>'An existing applicant with the provided email id is yet to be hired'];
                    }else{
                        $applicant_details = $check_applicant_status[0];
                        require_once APPPATH . 'Classes/recruitment/ApplicantCreateAsMemberToHCM.php';
                            if($applicant_details->applicant_id && $applicant_details->id){
                                                        
                                $applicantMoveObj = new ApplicantCreateAsMemberToHCM();
                                $applicantMoveObj->setUser_type('external_staff');
                                $applicantMoveObj->setAdmin_User_type('0');
                                $applicantMoveObj->setApplicant_id($applicant_details->applicant_id);
                                $applicantMoveObj->setMemberStatus($data['status']);
            
                                if (isset($applicant_details->id) && !empty($applicant_details->id)) {
                                    $applicantMoveObj->setApplicationId((int) $applicant_details->id);
                                }
            
                           
                                $applicantMoveObj->create_applicant_as_member();
                                
                                $hired_as = 1;
                                $this->Recruitment_applicant_model->create_log_on_final_stage($applicant_details, $adminId,2);
                                $this->basic_model->update_records('recruitment_applicant', ['current_stage' => 0, 'hired_as' => $hired_as, 'hired_date' => DATE_TIME], ['id' => $applicant_details->applicant_id]);
        
                                // create log
                                $this->loges->setCreatedBy($adminId);
                                $this->loges->setUserId($applicant_details->applicant_id);
                                $this->loges->setDescription(json_encode($applicant_details));
        
                                $applicantName = $email_row->email;
                                $this->loges->setTitle('Applicant - ' . $applicantName . ' Created as member');  // set title in log
                                $this->loges->setSpecific_title("Converted as support worker Successfully");
                                $this->loges->createLog(); // create log
                               
                                $result = ['is_applicant_created_as_member'=> 'true', 'not_valid'=> 'false', 'valid' =>'true','msg'=>'Support worker converted to applicant and added'];
                            }
                    }
                }
            }else{
                $result = ['is_applicant_created_as_member'=> 'false', 'not_valid'=> 'false', 'valid' =>'true','msg'=>'There is no applicant'];
            }
        }else{
            $result = ['not_valid'=> 'true','msg'=>'Please add the email for contact'];
        }

        return $result;
    }

    /**
     * 1. Check key pay id already assigned to any user
     * 2. Check the key pay id is valid against keypay using keypay api
     * 
     * @param $adminId {int} admin id
     * @param $key_payid {int} key pay given by user
     * 
     * @see check_keypay_employee_id_availablity
     * 
     * @return $result {array} message with status
     */
    public function validate_keypay_employee_id($adminId, $key_payid, $member_id) {
        $result = [];

        $condition = ['keypay_emp_id' => $key_payid, 'archive' => 0];
        //Skip the the member for update screen
        if($member_id) {
            $member_details = $this->get_member_details($member_id);
            $member_details = obj_to_arr($member_details['data']);
            
            if($member_details['keypay_emp_id'] == $key_payid) {
                return ['status' => TRUE];
            }

            $condition = array_merge($condition, ['member_id!=' => $member_id]);
        }

        $row = $this->basic_model->get_row('keypay_kiosks_emp_mapping_for_member', 
            ['keypay_emp_id'], $condition);
        
        if ($row && isset($row->keypay_emp_id)) {
            $result = ['status' => FALSE, 'error' => 'External Employee ID already assigned to a member'];
        } else {
            $result = $this->check_keypay_employee_id_availablity($adminId, $key_payid);            
        }
        return $result;
    }

    /**
     * Helper function to check whether specified key pay id is
     * available or not available in key pay
     * 
     * @param $adminId {int} admin id
     * @param $key_payid {int} key pay given by user
     * 
     * @see set_admin_id
     * @see AuthenticateDetails()
     * @see check_keypay_employee_id_availablity()
     * 
     * @return $result {array} message with status
     */
    public function check_keypay_employee_id_availablity($adminId, $empid) {

        # Using the keypay class to perform OTP operations
        $obj = new KeyPay();
        $obj->set_admin_id($adminId);

        # Authenticating keypay
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            return array('status' => FALSE, 'error' => $obj->get_error());            
        }
       
        # Checking employee id availablity in keypay
        $return = $obj->check_keypay_employee_id_availablity($empid);
        if(!$return) {
            return array('status' => FALSE, 'error' => 'Invalid KeyPay Employee ID');
        }
        return ['status' => TRUE, 'data' => $return];
    }

    }
