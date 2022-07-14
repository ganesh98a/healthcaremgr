<?php

class Roster_model_old extends CI_Model {

    // get unfilled shift
    public function get_active_roster($reqData, $participantId = false) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $src_columns = ['pr.id', 'pr.title', 'p.firstname', 'p.middlename', 'p.lastname', 'CONCAT(p.firstname," ",p.middlename," ",p.lastname)', 'CONCAT(p.firstname," ",p.lastname)'];

        if (!empty($sorted)) {
            if (!empty($sorted[0]->id)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'pr.id';
            $direction = 'ASC';
        }

        if (!empty($filter->search_box)) {
            //Search by all site table
            $this->db->group_start();

            for ($i = 0; $i < count($src_columns); ++$i) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, 'as') !== false) {
                    $serch_column = explode(' as ', $column_search);
                    $this->db->or_like($serch_column[0], $filter->search_box);
                } else {
                    $this->db->or_like($column_search, $filter->search_box);
                }
            }
            $this->db->group_end();
        }

        if (!empty($participantId)) {
            $this->db->where('userId', $participantId);
        }

        if (!empty($filter->start_date) || !empty($filter->end_date)) {
            if (!empty($filter->start_date)) {
                $this->db->where("pr.start_date >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
            }
            if (!empty($filter->end_date)) {
                $this->db->where("pr.start_date <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
            }
        }

        if (!empty($filter->roster_type)) {
            if ($filter->roster_type == 'default') {
                $this->db->where('pr.is_default', 2);
            } elseif ($filter->roster_type == 'temporary') {
                $this->db->where('pr.is_default', 1);
            }
        }

        $select_column = ['pr.id', 'pr.title', 'pr.start_date', 'pr.end_date'];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("CASE pr.is_default
            WHEN 1 THEN (pr.title)
            WHEN 2 THEN ('Default')
            ELSE NULL
            END as title");

        $this->db->select("CONCAT( p.firstname,' ',p.middlename,' ',p.lastname) as participantName");
        $this->db->from('tbl_roster as pr');

        // join with participant
        $this->db->join('tbl_participant as p', 'p.id = pr.userId', 'left');
        $this->db->where("pr.booked_by", 2);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where_in('pr.status', explode(',', $filter->status));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        // last_query();
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $total_count = $dt_filtered_total;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->end_date = ($val->end_date == '0000-00-00' || !$val->end_date) ? '' : $val->end_date;
            }
        }

        $return = ['count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => (int) $total_count];

        return $return;
    }

    function get_roster_shift_address($roster_shiftIds) {
        $this->db->select(["roster_shiftId", "address as street", "suburb", "state", "postal"]);
        $this->db->from("tbl_roster_shift_location as rsl");
        $this->db->where("rsl.archive", 0);
        $this->db->where_in("rsl.roster_shiftId", $roster_shiftIds);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $address = $query->result();

        $roster_address = [];
        if (!empty($address)) {
            foreach ($address as $val) {
                $roster_address[$val->roster_shiftId][] = $val;
            }
        }

        return $roster_address;
    }

    function get_roster_shift_line_item($roster_shiftIds) {
        $this->db->select(["r_attach_item.roster_shiftId", "r_attach_item.cost", "r_attach_item.plan_line_itemId", "upli.fund_remaining", "upli.line_itemId"]);
        $this->db->select("(select count(sub_s.id) from tbl_shift as sub_s where sub_s.status IN (1,2,7) AND sub_s.start_time > CURDATE() AND sub_s.roster_shiftId = rs.id) as remaining_shift_count");
        $this->db->from("tbl_roster_shift_line_item_attached as r_attach_item");
        $this->db->join("tbl_user_plan_line_items as upli", "upli.id = r_attach_item.plan_line_itemId", "INNER");
        $this->db->join("tbl_roster_shift as rs", "rs.id = r_attach_item.roster_shiftId AND rs.archive = 0", "INNER");
        $this->db->join("tbl_user_plan as up", "up.id = upli.user_planId", "INNER");
        $this->db->join('tbl_finance_line_item as fli', 'fli.id = r_attach_item.line_item', 'INNER');


        $this->db->where('CURRENT_DATE BETWEEN fli.start_date and fli.end_date', null, false);
        $this->db->where_in('r_attach_item.roster_shiftId', $roster_shiftIds);
        $this->db->group_by("r_attach_item.id");

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $line_item = $query->result();


        $items = [];

        if (!empty($line_item)) {
            foreach ($line_item as $val) {
                $gst = calculate_gst($val->cost);
                $val->will_use_fund = custom_round($val->cost + $gst);

                $items[$val->roster_shiftId][$val->line_itemId] = $val;
            }
        }

        return $items;
    }

    // get roster week details
    function get_roster_shift_week_data_old($rosterId, $userId) {
        $this->db->select(["rs.id as roster_shiftId", "rs.week_day", "rs.start_time", "rs.end_time", "rs.week_number", "rs.allocate_pre_member", "rs.autofill_shift", "rs.push_to_app", "rs.shift_note"]);
        $this->db->select(["rsc.confirm_with", "rsc.confirm_by", "rsc.confirm_userId", "rsc.confirm_userId as confirmPerson", "rsc.firstname as confirm_with_f_name", "rsc.lastname as confirm_with_l_name", "rsc.email as confirm_with_email", "rsc.phone as confirm_with_mobile"]);
        $this->db->select("(select count(id) from tbl_shift as sub_s where sub_s.roster_shiftId = rs.id AND sub_s.status IN (1,2,7)) as in_funding");

        $this->db->select("(select GROUP_CONCAT(timeId SEPARATOR '@MAIN_SPRTR@') from tbl_roster_shift_time_category as sub_time_category where sub_time_category.roster_shiftId = rs.id AND sub_time_category.archive = 0) as shift_cateogry_item");

        $this->db->select("(select GROUP_CONCAT(concat(requirementId) SEPARATOR '@MAIN_BRKR@') from tbl_roster_shift_requirement where roster_shiftId = rs.id AND requirement_type = 2) as shift_asistance");
        $this->db->select("(select GROUP_CONCAT(concat(requirementId) SEPARATOR '@MAIN_BRKR@') from tbl_roster_shift_requirement where roster_shiftId = rs.id AND requirement_type = 1) as shift_mobility");

        $this->db->from('tbl_roster_shift as rs');
        $this->db->join('tbl_roster_shift_confirmation as rsc', "rsc.roster_shiftId = rs.id", "INNER");
        $this->db->where('rs.rosterId', $rosterId);
        $this->db->where('rs.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $rosterData = $query->result();

        $x = $this->Schedule_model->get_shift_requirement_for_participant($userId);

        $assistance = $x["assistance"];
        $mobility = $x["mobility"];

        $shift_category = $this->basic_model->get_record_where('finance_time_of_the_day', ['id', 'short_name as name', 'key_name'], '');
        $roster_shiftIds = array_column($rosterData, "roster_shiftId");
        $shift_address = $this->get_roster_shift_address($roster_shiftIds);
        $attach_items = $this->get_roster_shift_line_item($roster_shiftIds);

        if (!empty($rosterData)) {
            foreach ($rosterData as $val) {
                $___shift_category = $shift_category;
                $___assistance = $assistance;
                $___mobility = $mobility;
                $val->in_funding = ($val->in_funding > 0) ? true : false;
                $val->end_time = ($val->end_time == '0000-00-00 00:00:00') ? '' : $val->end_time;
                $val->allocate_pre_member = ($val->allocate_pre_member == 1) ? 1 : 2;
                $val->autofill_shift = ($val->autofill_shift == 1) ? 1 : 2;
                $val->push_to_app = ($val->push_to_app == 1) ? 1 : 2;
                $val->disable_confirmer = ($val->confirm_userId > 0) ? true : false;

                $val->shift_cateogry_item = ($val->confirm_userId > 0) ? true : false;
                $s_x = explode("@MAIN_SPRTR@", $val->shift_cateogry_item);

                if (!empty($___shift_category)) {
                    foreach ($___shift_category as $cat) {
                        $cat->checked = in_array($cat->id, $s_x) ? true : false;
                    }
                }

                $s_x = explode("@MAIN_SPRTR@", $val->shift_asistance);
                if (!empty($___assistance)) {
                    foreach ($___assistance as $cat) {
                        $cat->checked = in_array($cat->value, $s_x) ? true : false;
                    }
                }

                $s_x = explode("@MAIN_SPRTR@", $val->shift_mobility);
                if (!empty($___mobility)) {
                    foreach ($___mobility as $cat) {
                        $cat->checked = in_array($cat->value, $s_x) ? true : false;
                    }
                }

                $val->time_of_days = $___shift_category;
                $val->assistance = $___assistance;
                $val->mobility = $___mobility;
                $val->completeAddress = $shift_address[$val->roster_shiftId];
                $val->selected_line_item_id_with_data = $attach_items[$val->roster_shiftId];
            }
        }

        return $rosterData;
    }

    // get roster week details
    function get_roster_shift_week_data($rosterId, $userId) {
        $this->db->select(["rs.id as roster_shiftId", "rs.week_day", "rs.start_time", "rs.end_time", "rs.week_number"]);
        $this->db->select("(select count(id) from tbl_shift as sub_s where sub_s.roster_shiftId = rs.id AND sub_s.status IN (1,2,7)) as existing_shift_count");
        $this->db->select("(select count(id) from tbl_roster_shift_line_item_attached as sub_attach_item where sub_attach_item.roster_shiftId = rs.id AND sub_attach_item.archive = 0) as line_item_count");
        $this->db->from('tbl_roster_shift as rs');

        $this->db->where('rs.rosterId', $rosterId);
        $this->db->where('rs.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $rosterData = $query->result();

        $roster_shiftIds = array_column($rosterData, "roster_shiftId");
        $attach_items = $this->get_roster_shift_line_item($roster_shiftIds);

        $user_plan_item = $this->get_participant_plan_line_item($userId);


        if (!empty($rosterData)) {
            foreach ($rosterData as $val) {
                $val->line_item_count = (int) $val->line_item_count;
                $val->existing_shift_count = (int) $val->existing_shift_count;

                $val->in_funding = ($val->existing_shift_count > 0) ? true : false;
                $val->some_item_expire = (count($attach_items[$val->roster_shiftId] ?? []) !== $val->line_item_count) ? true : false;

                if ($val->existing_shift_count === 0 && !$val->some_item_expire) {
                    $temp_fund = $user_plan_item;
                    $shift_line_item = $attach_items[$val->roster_shiftId];
                    $in_funding = true;

                    if (!empty($shift_line_item)) {
                        foreach ($shift_line_item as $val_x) {
                            if (!empty($temp_fund[$val_x->plan_line_itemId])) {
                                $remaining_fund = (float) $temp_fund[$val_x->plan_line_itemId]['have_fund'] - $val_x->will_use_fund;

                                if ($remaining_fund < 0) {
                                    $in_funding = false;
                                }
                            }
                        }
                    }

                    if ($in_funding) {
                        $user_plan_item = $temp_fund;
                    }

                    $val->in_funding = $in_funding;
                }
            }
        }

        return $rosterData;
    }

    public function get_roster_details($rosterId) {
        // get roster main data
        $this->db->select(['r.id as rosterId', 'r.title', 'r.start_date', 'r.end_date', 'r.shift_round', 'r.is_default', 'r.userId', 'r.status']);
        $this->db->select("(CASE WHEN r.booked_by = 2 THEN (select concat_ws(' ',firstname,middlename,lastname) from tbl_participant where id = r.userId) ELSE '' END) as participant_name");
        $this->db->from('tbl_roster as r');

        $this->db->where('r.id', $rosterId);
        $roster = $this->db->get()->row_array();

        $date1 = date_create(DateFormate($roster['start_date'], 'Y-m-d'));
        $date2 = date_create(DATE_TIME);

        $diff = date_diff($date1, $date2);
        $roster['day_count'] = $diff->days;

        // $roster['update_disabled'] = ($diff->days > 57) ? false : true;
        $roster['update_disabled'] = true;

        $roster['participant'] = ['value' => $roster['userId'], 'label' => $roster['participant_name']];
        $roster['end_date'] = (($roster['end_date'] == '0000-00-00') || !$roster['end_date']) ? '' : $roster['end_date'];

        $rosterData = $this->get_roster_shift_week_data($rosterId, $roster['userId']);

        // create initial per day shift is not active (mean blank shift)
        $tempList = [['is_active' => false]];
        $tempRoster = [];

        if (!empty($rosterData)) {
            $cnt = 0;
            foreach ($rosterData as $key => $val) {
                $weekKey = $val->week_number - 1;
                $dynamicIndex = 0;
                $dayName = numberToDay($val->week_day);

                if (array_key_exists($weekKey, $tempRoster)) {
                    if (array_key_exists($dayName, $tempRoster[$weekKey])) {
                        foreach ($tempRoster[$weekKey][$dayName] as $check) {
                            if ($check['is_active']) {
                                $dynamicIndex++;
                            }
                        }
                    }
                }

                $tempRoster[$weekKey][$dayName][$dynamicIndex] = (array) $val;
                $tempRoster[$weekKey][$dayName][$dynamicIndex]['is_active'] = true;

                $tempRoster[$weekKey][$dayName][++$dynamicIndex]['is_active'] = false;
                ++$cnt;
            }
        }

        $resosterList = [];

        $shift_round = json_decode($roster['shift_round']);

        $weekKeyArray = array_keys($shift_round);
        foreach ($weekKeyArray as $weekNumber) {
            foreach (numberToDay() as $weekDayNum => $weekDay) {
                $status = true;

                if (array_key_exists($weekNumber, $tempRoster)) {
                    if (array_key_exists($weekDay, $tempRoster[$weekNumber])) {
                        $status = false;
                        $dynamicIndex = 0;

                        $resosterList[$weekNumber][$weekDay] = $tempRoster[$weekNumber][$weekDay];
                    }
                }

                if ($status) {
                    $resosterList[$weekNumber][$weekDay] = $tempList;
                }
            }
        }

        $roster['rosterList'] = $resosterList;

        return $roster;
    }

    public function getRosterTempData($rosterId) {
        $tbl_participant_roster = TBL_PREFIX . 'participant_roster';
        $tbl_participant_roster_temp_data = TBL_PREFIX . 'participant_roster_temp_data';

        // get roster main data
        $this->db->select([$tbl_participant_roster . '.is_default', $tbl_participant_roster . '.id', $tbl_participant_roster . '.participantId', $tbl_participant_roster_temp_data . '.rosterData']);

        $this->db->from($tbl_participant_roster);
        $this->db->join($tbl_participant_roster_temp_data, $tbl_participant_roster_temp_data . '.rosterId = ' . $tbl_participant_roster . '.id', 'left');

        $this->db->where($tbl_participant_roster . '.id', $rosterId);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->row();
    }

    public function check_roster_start_end_date_already_exist($rosObj) {
        $this->db->select(['r.id']);
        $this->db->from("tbl_roster as r");

        $where = "(('" . $rosObj->getStart_date() . "' BETWEEN date(r.start_date) AND date(r.end_date))  or ('" . $rosObj->getEnd_date() . "' BETWEEN date(r.start_date) AND date(r.end_date)) OR
        (date(r.start_date) BETWEEN '" . $rosObj->getStart_date() . "' AND '" . $rosObj->getEnd_date() . "')  or (date(r.end_date) BETWEEN '" . $rosObj->getStart_date() . "' AND '" . $rosObj->getEnd_date() . "')) ";

        if ($rosObj->getRosterId() > 0) {
            $this->db->where('id !=', $rosObj->getRosterId());
        }
        $this->db->where($where);
        $this->db->where("is_default", 1);
        $this->db->where("booked_by", 2);
        $this->db->where("userId", $rosObj->getParticipantId());
        $this->db->where_in('status', [1, 2]);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->row();
    }

    function get_roster_logs($reqData) {

        $colowmn = array('lg.title', "DATE_FORMAT(lg.created, '%d/%m/%Y') as created", "DATE_FORMAT(lg.created, '%H:%i %p') as time");
        $this->db->select($colowmn);
        $this->db->from('tbl_logs as lg');
        $this->db->join('tbl_module_title as mt', 'mt.id = lg.sub_module', 'INNER');
        $this->db->where('lg.userId', $reqData->rosterId);
        $this->db->where('mt.key_name', 'roster');

        $query = $this->db->get();
        return $query->result_array();
    }

    function get_roster_requirements_for_participant($reqData) {
        $participantId = $reqData->participantId ?? 0;
    }

    function get_participant_plan_line_item($participantId) {
        $this->db->select(["upli.line_itemId", "upli.id as plan_line_itemId", "upli.fund_remaining as have_fund", "upli.fund_remaining as total_have_fund"]);
        $this->db->from("tbl_user_plan_line_items as upli");
        $this->db->join("tbl_user_plan as up", "up.id = upli.user_planId");
        $this->db->where("upli.archive", 0);
        $this->db->where("up.userId", $participantId);
        $this->db->where("up.user_type", 2);
        $this->db->where("CURRENT_DATE() < up.end_date ", null, false);

        $query = $this->db->get();
        $result = $query->result();

        $res = [];
        if (!empty($result)) {
            foreach ($result as $val) {
                $res[$val->plan_line_itemId] = (array) $val;
                $res[$val->plan_line_itemId]["current_used"] = 0;
            }
        }

        return $res;
    }

    public function get_previous_shifts_who_collapse_by_date_time($objRos, $shift_dates) {
        $select_column = array("s.id", "s.shift_date", "s.start_time", "s.end_time", "s.status");
       // $this->db->select("CONCAT(MOD( TIMESTAMPDIFF(hour,s.start_time,s.end_time), 24), ':',MOD( TIMESTAMPDIFF(minute,s.start_time,s.end_time), 60), ' hrs') as duration");
        $this->db->select("CONCAT(LPAD(MOD( TIMESTAMPDIFF(hour, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 24), 2, 0), ':', LPAD(MOD( TIMESTAMPDIFF(minute, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 60), 2, 0), ' hrs') as duration");
        $this->db->select($select_column);
        $this->db->from("tbl_shift as s");
        $this->db->join("tbl_shift_participant as sp", 'sp.shiftId = s.id', 'inner');
        $this->db->where_in('shift_date', $shift_dates);
        $this->db->where_in('s.status', array(1, 2, 3, 4, 7));
        $this->db->where('sp.participantId', $objRos->getParticipantId());
        if (!empty($objRos->getRosterId())) {
            $this->db->where("NOT EXISTS (select sub_rs.id from tbl_roster_shift as sub_rs where sub_rs.rosterId = " . $objRos->getRosterId() . " and sub_rs.archive = 0 AND sub_rs.id = s.roster_shiftId)");
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->result_array();

        if (!empty($response)) {
            foreach ($response as $key => $val) {
                $response[$key] = $val;
                $response[$key]['duration'] = ($val['duration']);
            }
        }
        return $response;
    }

    function create_roster($objRos, $reqData) {
        $roster = array(
            'start_date' => $objRos->getStart_date(),
            'booked_by' => 2,
            'userId' => $objRos->getParticipantId(),
            'status' => 1,
            'created' => DATE_TIME,
            'shift_round' => $objRos->getShift_round(),
            'is_default' => $objRos->getIs_default(),
            'created_type' => $objRos->getIs_default(),
            'created_by' => $objRos->getIs_default(),
            'created' => DATE_TIME,
            'updated' => DATE_TIME,
        );

        if ($objRos->getIs_default() == 1) {
            $roster['end_date'] = $objRos->getEnd_date();
            $roster['title'] = $objRos->getTitle();
        }

        $rosterId = $this->basic_model->insert_records('roster', $roster, $multiple = FALSE);
        $objRos->setRosterId($rosterId);

        $objRos->save_shift_who_added_in_roster();

        return $rosterId;
    }

    function save_shift_who_added_in_roster($objRos) {
        $rosster_shift = $objRos->getRosterList();
        $week_arr = array_flip(numberToDay());

        $will_archive_roster_shirtId = [];
        if (!empty($rosster_shift)) {
            foreach ($rosster_shift as $day) {
                foreach ($day as $multiple_shift) {
                    foreach ($multiple_shift as $shift) {
                        $updated_shift = (!empty($shift->updated_shift)) ? true : false;
                        $r_shiftId = $shift->roster_shiftId ?? '';

                        if (!empty($shift->is_active) && (!$r_shiftId || $updated_shift)) {
                            if ($r_shiftId) {
                                $will_archive_roster_shirtId[] = $r_shiftId;
                            }

                            $preferred_member = 0;
                            if (!empty($shift->preferred_member_ary)) {
                                foreach ($shift->preferred_member_ary as $key => $vall) {
                                    if (isset($vall->name->value)) {
                                        $preferred_member = $vall->name->value;
                                    }
                                }
                            }

                            $week_number = ++$shift->main_index;

                            $roster_shift = [
                                'rosterId' => $objRos->getRosterId(),
                                'week_day' => $week_arr[$shift->day],
                                'start_time' => DateFormate($shift->start_time),
                                'end_time' => DateFormate($shift->end_time),
                                'week_number' => $week_number,
                                'preferred_memberId' => $preferred_member,
                                'allocate_pre_member' => $shift->allocate_pre_member == 1 ? 1 : 0,
                                'autofill_shift' => $shift->autofill_shift == 1 ? 1 : 0,
                                'push_to_app' => $shift->push_to_app == 1 ? 1 : 0,
                                'shift_note' => $shift->shift_note,
                                'created' => DATE_TIME,
                                'updated' => DATE_TIME,
                                'archive' => 0,
                            ];


                            $roster_shiftId = $this->basic_model->insert_records('roster_shift', $roster_shift, $multiple = FALSE);

                            $shift->shift_rosterId = $roster_shiftId;

                            $this->save_location_in_shift_roster($shift->completeAddress, $roster_shiftId);

                            $this->save_confirmation_details_in_shift_roster($shift, $roster_shiftId);

                            $this->save_time_category_in_shift_roster($shift->time_of_days, $roster_shiftId);

                            $this->save_attached_line_item_in_shift_roster($shift, $roster_shiftId);

                            $this->save_shift_requirement_to_roster($shift, $roster_shiftId);
                        }
                    }
                }
            }
        }

        if (!empty($will_archive_roster_shirtId)) {
            $objRos->archive_roster_shift($will_archive_roster_shirtId);
        }

        $objRos->setRosterList($rosster_shift);
        $objRos->make_shift_from_roster();
    }

    function save_location_in_shift_roster($shift_location, $roster_shiftId) {
        if (!empty($shift_location)) {
            foreach ($shift_location as $val) {
                $location[] = [
                    'roster_shiftId' => $roster_shiftId,
                    'address' => $val->street,
                    'suburb' => $val->suburb,
                    'state' => $val->state,
                    'postal' => $val->postal,
                    'created' => DATE_TIME,
                    'updated' => DATE_TIME,
                    'archive' => 0,
                ];
            }

            if (!empty($location)) {
                $this->basic_model->insert_records('roster_shift_location', $location, $multiple = TRUE);
            }
        }
    }

    function save_confirmation_details_in_shift_roster($shift, $roster_shiftId) {

        $data = [
            "roster_shiftId" => $roster_shiftId,
            "confirm_with" => $shift->confirm_with,
            "confirm_by" => $shift->confirm_by,
            "confirm_userId" => $shift->confirm_userId,
            "firstname" => $shift->confirm_with_f_name,
            "lastname" => $shift->confirm_with_l_name,
            "email" => $shift->confirm_with_email,
            "phone" => $shift->confirm_with_mobile,
            "created" => DATE_TIME,
        ];

        if (!empty($data)) {
            $this->basic_model->insert_records('roster_shift_confirmation', $data, $multiple = false);
        }
    }

    function save_time_category_in_shift_roster($time_category, $roster_shiftId) {
        if (!empty($time_category)) {
            foreach ($time_category as $val) {
                if (!empty($val->checked)) {
                    $data[] = [
                        "roster_shiftId" => $roster_shiftId,
                        "timeId" => $val->id,
                        "archive" => 0,
                        "created" => DATE_TIME,
                    ];
                }
            }
        }

        if (!empty($data)) {
            $this->basic_model->insert_records('roster_shift_time_category', $data, $multiple = true);
        }
    }

    function save_attached_line_item_in_shift_roster($shift, $roster_shiftId) {
        $line_item_list = $shift->selected_line_item_id_with_data;
        $shift_locations = $shift->completeAddress;

        if (!empty($line_item_list)) {

            $line_itemIds = array_keys(obj_to_arr($line_item_list));

            $this->load->model('finance/Finance_quote_model');
            $line_item_cost = $this->Schedule_model->getLineItemCost($line_itemIds);

            $attach_item = [];
            $sub_total = 0;
            foreach ($line_item_list as $val) {
                $qty = calculate_qty_using_date($shift->start_time, $shift->end_time);
                $postcode = $shift_locations[0]->postcode ?? 0;

                $price_type = get_price_type_on_base_postcode($postcode);
                $item_cost = $line_item_cost[$val->line_itemId][$price_type];
                $item_sub_total = custom_round(($item_cost * $qty), 2);

                $attach_item[] = [
                    "roster_shiftId" => $roster_shiftId,
                    "plan_line_itemId" => $val->plan_line_itemId,
                    "line_item" => $val->line_itemId,
                    "quantity" => $qty,
                    "cost" => $item_cost,
                    "sub_total" => $item_sub_total,
                    "created" => DATE_TIME,
                    "updated" => DATE_TIME,
                    "archive" => 0,
                ];

                $sub_total += $item_sub_total;
            }

            if (!empty($attach_item)) {
                $this->basic_model->insert_records('roster_shift_line_item_attached', $attach_item, true);

                $this->basic_model->update_records('roster_shift', ['sub_total' => $sub_total], ['id' => $roster_shiftId]);
            }
        }
    }

    function update_roster($objRos, $reqData) {
        $roster = array(
            'start_date' => $objRos->getStart_date(),
            'booked_by' => 2,
            'userId' => $objRos->getParticipantId(),
            'status' => 1,
            'shift_round' => $objRos->getShift_round(),
            'is_default' => $objRos->getIs_default(),
            'created_type' => $objRos->getIs_default(),
            'created_by' => $objRos->getIs_default(),
            'updated' => DATE_TIME,
        );

        if ($objRos->getIs_default() == 1) {
            $roster['end_date'] = $objRos->getEnd_date();
            $roster['title'] = $objRos->getTitle();
        } else {
            $roster['end_date'] = "";
            $roster['title'] = "";
        }

        $this->basic_model->update_records('roster', $roster, $where = array('id' => $objRos->getRosterId()));

        // archive roster shift who is removed by admin
        $objRos->archive_roster_shift($reqData->will_remove_roster_shiftId ?? []);

        $objRos->save_shift_who_added_in_roster();
    }

    function archive_roster_shift($roster_shiftIds) {
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        if (!empty($roster_shiftIds)) {
            // archive roster shift
            $this->db->where_in("id", $roster_shiftIds);
            $this->db->update("tbl_roster_shift", ["archive" => 1, "updated" => DATE_TIME]);

            $this->db->flush_cache();

            // get those shift ids whos created by archive roster ids
            $this->db->select("s.id");
            $this->db->from("tbl_shift as s");
            $this->db->where_in("s.status", [1, 2, 7]);
            $this->db->where("date(s.start_time) > date(CURDATE())");
            $this->db->where_in("s.roster_shiftId", $roster_shiftIds);
            $result = $this->db->get()->result();

            if (!empty($result)) {
                $shiftIds = array_column($result, "id");
                $objShift->archive_shift($shiftIds);
            }
        }
    }

    function save_shift_requirement_to_roster($shift, $roster_shiftId) {
        $roster_req = [];
        if (!empty($shift->assistance)) {
            foreach ($shift->assistance as $val) {
                if (!empty($val->checked)) {
                    $roster_req[] = [
                        'roster_shiftId' => $roster_shiftId,
                        'requirementId' => $val->value,
                        'requirement_type' => 2,
                        'created' => DATE_TIME,
                    ];
                }
            }
        }
        if (!empty($shift->mobility)) {
            foreach ($shift->mobility as $val) {
                if (!empty($val->checked)) {
                    $roster_req[] = [
                        'roster_shiftId' => $roster_shiftId,
                        'requirementId' => $val->value,
                        'requirement_type' => 1,
                        'created' => DATE_TIME,
                    ];
                }
            }
        }

        if (!empty($roster_req)) {
            $this->basic_model->insert_records("roster_shift_requirement", $roster_req, $multiple = true);
        }
    }

}
