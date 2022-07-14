<?php

class Listing_model extends CI_Model {

// get unfilled shift
    public function unfilled_shift($reqData) {
        $limit = sprintf("%d",$reqData->pageSize) ?? 0;
        $page = sprintf("%d",$reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $pageType = !empty($reqData->pageType) && $reqData->pageType == 'unfilled' ? 'unfilled' : 'app';
        $orderBy = '';
        $direction = '';

        $src_columns = array("id", "start_time", "end_time", "shift_for");
        $available_columns = array("id", "shift_date", "start_time", "end_time", 'booked_by', 'push_to_app');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_columns)) {
                $orderBy = $sorted[0]->id;
            }

            $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        } else {
            $orderBy = 's.shift_date';
            $direction = 'ASC';
        }
        if(!empty($filter->search_box)) {
            $search_key  = $this->db->escape_str($filter->search_box, TRUE);
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

                $queryHavingData = $this->db->get_compiled_select();
                $queryHavingData = explode('WHERE', $queryHavingData);
                $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
                $this->db->having($queryHaving, null, false);
            }
        }



        if (!empty($filter->shift_date)) {
            $this->db->where(array("DATE(s.start_time)" => date('Y-m-d', strtotime($filter->shift_date))));
        } else {
            if (!empty($filter->start_date)) {
                $this->db->where("DATE(s.start_time) >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
            }
            if (!empty($filter->end_date)) {
                $this->db->where("DATE(s.start_time) <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
            }
        }

        $this->db->where_in('push_to_app', ($pageType == 'unfilled') ? array('0', '2') : array(1));

        if (!empty($filter->shift_type)) {
            $this->db->join("tbl_shift_time_category as stc", "stc.shiftId = s.id AND stc.timeId = " . $this->db->escape_like_str($filter->shift_type));
        }


        $select_column = array("s.id", "s.shift_date", "s.start_time", "s.end_time", 's.booked_by', 's.push_to_app');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $sub_query_shift_for = $this->common_user_name_for_shift_query();
        $this->db->select($sub_query_shift_for . ' as shift_for');
        $this->db->from('tbl_shift as s');

        $this->db->select("CONCAT(LPAD(MOD( TIMESTAMPDIFF(hour, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 24), 2, 0), ':', LPAD(MOD( TIMESTAMPDIFF(minute, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 60), 2, 0), ' hrs') as duration");

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where('s.shift_date >=', date('Y-m-d'));

        $this->db->where('s.status', 1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->memberNames = $this->get_preferred_member($val->id);
                $val->address = $this->get_shift_location($val->id);

                $val->diff = (strtotime(date('Y-m-d H:i:s', strtotime($val->start_time))) - strtotime(date('Y-m-d H:i:s'))) * 1000;

                $val->duration = ($val->duration);
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count);
        return $return;
    }

// get unconfirmed shift
    public function unconfirmed_shift($reqData) {
        $limit = sprintf("%d", $reqData->pageSize) ?? 0;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $pageType = !empty($reqData->pageType) && $reqData->pageType == 'unconfirmed' ? 'unconfirmed' : 'quoted';
        $orderBy = '';
        $direction = '';

        $available_columns = array("id", "shift_date", "start_time", "end_time", 'booked_by');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_columns)) {
                $orderBy = $sorted[0]->id;
            }
            $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        } else {
            $orderBy = 's.id';
            $direction = 'ASC';
        }

        $src_columns = array("id", "start_time", "end_time", "shift_for");
        if(!empty($filter->search_box)) {
            $search_key  = $this->db->escape_str($filter->search_box, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];


                    if (strstr($column_search, "as") !== FALSE) {
                        $serch_column = explode(" as ", $column_search);
                        if (!empty($serch_column[0]))
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();

                $queryHavingData = $this->db->get_compiled_select();
                $queryHavingData = explode('WHERE', $queryHavingData);
                $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
                $this->db->having($queryHaving, null, false);
            }
        }

        if (!empty($filter->shift_date)) {
            $this->db->where(array("DATE(s.start_time)" => date('Y-m-d', strtotime($filter->shift_date))));
        } else {
            if (!empty($filter->start_date)) {
                $this->db->where("DATE(s.start_time) >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
            }
            if (!empty($filter->end_date)) {
                $this->db->where("DATE(s.start_time) <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
            }
        }

        if (!empty($filter->shift_type)) {
            $this->db->join("tbl_shift_time_category as stc", "stc.shiftId = s.id AND stc.timeId = " . $this->db->escape_like_str($filter->shift_type));
        }

        $select_column = array("s.id", "s.shift_date", "s.start_time", "s.end_time", 's.booked_by');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $sub_query_shift_for = $this->common_user_name_for_shift_query();
        $this->db->select($sub_query_shift_for . ' as shift_for');
        $this->db->from('tbl_shift as s');

        $this->db->select("CONCAT(LPAD(MOD( TIMESTAMPDIFF(hour, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 24), 2, 0), ':', LPAD(MOD( TIMESTAMPDIFF(minute, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 60), 2, 0), ' hrs') as duration");

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->where("s.status", (($pageType === 'unconfirmed') ? 2 : 3));
        $this->db->where('s.shift_date >=', date('Y-m-d'));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->address = $this->get_shift_location($val->id);
                $val->memberName = $this->get_allocated_member($val->id);

                $val->diff = (strtotime(date('Y-m-d H:i:s', strtotime($val->start_time))) - strtotime(date('Y-m-d H:i:s'))) * 1000;

                $val->duration = ($val->duration);
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count);
        return $return;
    }

// get cancelled and cancelled shift    
    public function rejected_and_cancelled_shift($reqData) {
        $limit = sprintf("%d",$reqData->pageSize) ?? 0;
        $page = sprintf("%d",$reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $pageType = !empty($reqData->pageType) && $reqData->pageType == 'rejected' ? 'rejected' : 'cancelled';
        $orderBy = '';
        $direction = '';

        $available_column = array("id", "shift_date", "start_time", "end_time", 'booked_by');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
            }
            $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        } else {
            $orderBy = 's.id';
            $direction = 'ASC';
        }


        $src_columns = array("id", "start_time", "end_time", "shift_for");
        if(!empty($filter->search_box)) {
            $search_key  = $this->db->escape_str($filter->search_box, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];


                    if (strstr($column_search, "as") !== FALSE) {
                        $serch_column = explode(" as ", $column_search);
                        if (!empty($serch_column[0]))
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();

                $queryHavingData = $this->db->get_compiled_select();
                $queryHavingData = explode('WHERE', $queryHavingData);
                $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
                $this->db->having($queryHaving, null, false);
            }
        }
        if (!empty($filter->shift_date)) {
            $this->db->where(array("s.shift_date" => date('Y-m-d', strtotime($filter->shift_date))));
        } else {
            if (!empty($filter->start_date)) {
                $this->db->where("s.shift_date >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
            }
            if (!empty($filter->end_date)) {
                $this->db->where("s.shift_date <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
            }
        }

        if (!empty($filter->shift_type)) {
            $this->db->join("tbl_shift_time_category as stc", "stc.shiftId = s.id AND stc.timeId = " . $this->db->escape_like_str($filter->shift_type));
        }

        $select_column = array("s.id", "s.shift_date", "s.start_time", "s.end_time", 's.booked_by');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $sub_query_shift_for = $this->common_user_name_for_shift_query();
        $this->db->select($sub_query_shift_for . ' as shift_for');
        $this->db->select("CONCAT(LPAD(MOD( TIMESTAMPDIFF(hour, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 24), 2, 0), ':', LPAD(MOD( TIMESTAMPDIFF(minute, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 60), 2, 0), ' hrs') as duration");
        $this->db->from('tbl_shift as s');


        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where("s.status", (($pageType == 'rejected') ? 4 : 5), false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->address = $this->get_shift_location($val->id);

                if ($pageType == 'rejected') {
                    $val->memberName = $this->get_rejected_member($val->id);
                } else {
                    $val->cancelled_data = $this->get_cancelled_details($val->id);
                }

                $val->diff = (strtotime(date('Y-m-d H:i:s', strtotime($val->start_time))) - strtotime(date('Y-m-d H:i:s'))) * 1000;

                $val->duration = ($val->duration);
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count);
        return $return;
    }

    public function get_shift_listing($reqData) {
        $limit = sprintf("%d", $reqData->pageSize) ?? 0;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $status = sprintf("%d", $reqData->status) ?? 6;
        $orderBy = '';
        $direction = '';
        
        $available_columns = array("id", "shift_date", "start_time", "end_time", "booked_by");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_columns) ) {
                $orderBy = $sorted[0]->id;
            }
            $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        } else {
            $orderBy = 's.id';
            $direction = 'ASC';
        }

        $src_columns = array("s.id", "s.start_time", "s.end_time", "shift_for");
        
        if(!empty($filter->search_box)) {
            $search_key  = $this->db->escape_str($filter->search_box, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];


                    if (strstr($column_search, "as") !== FALSE) {
                        $serch_column = explode(" as ", $column_search);
                        if (!empty($serch_column[0]))
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();

                $queryHavingData = $this->db->get_compiled_select();
                $queryHavingData = explode('WHERE', $queryHavingData);
                $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
                $this->db->having($queryHaving, null, false);
            }
        }

        if (!empty($filter->shift_date)) {
            $this->db->where(array("s.shift_date" => date('Y-m-d', strtotime($filter->shift_date))));
        } else {
            if (!empty($filter->start_date)) {
                $this->db->where("s.shift_date >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
            }
            if (!empty($filter->end_date)) {
                $this->db->where("s.shift_date <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
            }
        }

        if (!empty($filter->shift_type)) {
            $this->db->join("tbl_shift_time_category as stc", "stc.shiftId = s.id AND stc.timeId = " . $this->db->escape_like_str($filter->shift_type));
        }


        $select_column = array("s.id", "s.shift_date", "s.start_time", "s.end_time", 's.booked_by');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        if ($status == 6) {
            $this->db->select("(select sum(receipt_value) from tbl_shift_expenses_attachment where shiftId = s.id limit 1) as expenses");
        }
        $sub_query_shift_for = $this->common_user_name_for_shift_query();
        $this->db->select($sub_query_shift_for . ' as shift_for');

        $this->db->from('tbl_shift as s');
        $this->db->select("CONCAT(LPAD(MOD( TIMESTAMPDIFF(hour, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 24), 2, 0), ':', LPAD(MOD( TIMESTAMPDIFF(minute, DATE_FORMAT(s.start_time, '%Y-%m-%d %H:%i'), DATE_FORMAT(s.end_time, '%Y-%m-%d %H:%i')), 60), 2, 0), ' hrs') as duration");

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where("s.status", $status);
        if ($status == 7) {
            $this->db->where('s.shift_date >=', date('Y-m-d'));
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->address = $this->get_shift_location($val->id);
                $val->memberNames = $this->get_accepted_shift_member($val->id);

                $val->diff = (strtotime(date('Y-m-d H:i:s', strtotime($val->start_time))) - strtotime(date('Y-m-d H:i:s'))) * 1000;
                $val->duration = ($val->duration);
                $val->expenses = ($val->expenses) ?? 0;
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count, "status" => true);
        return $return;
    }

    public function get_preferred_member($shiftId) {
        $tbl_shift = TBL_PREFIX . 'shift';
        $tbl_shift_preferred_member = TBL_PREFIX . 'shift_preferred_member';
        $tbl_member = TBL_PREFIX . 'member';

        $this->db->select("CONCAT(" . $tbl_member . ".firstname,' '," . $tbl_member . ".middlename,' '," . $tbl_member . ".lastname ) as memberName");
        $this->db->select(array($tbl_shift_preferred_member . '.memberId'));
        $this->db->from($tbl_shift_preferred_member);
        $this->db->join($tbl_shift, $tbl_shift_preferred_member . '.shiftId = ' . $tbl_shift . '.id', 'left');
        $this->db->join($tbl_member, $tbl_member . '.id = ' . $tbl_shift_preferred_member . '.memberId', 'left');
        $this->db->where(array($tbl_shift_preferred_member . '.shiftId' => $shiftId));


        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
//        last_query();
        return $query->result();
    }

    public function create_shift_location($shift_location) 
    {
        if (is_array($shift_location)) {
            $this->basic_model->insert_records('shift_location', $shift_location, $multiple = true);
        }
        else {
            $this->basic_model->insert_records('shift_location', $shift_location);
        }
    }

    public function delete_shift_location($where) {
        $this->basic_model->delete_records('shift_location', $where);
    }

    public function get_shift_location($shiftId) {
        $tbl_shift_location = TBL_PREFIX . 'shift_location';

        $this->db->select("CONCAT(" . $tbl_shift_location . ".address,', ', " . $tbl_shift_location . ".suburb,', ', " . $tbl_shift_location . ".postal,', ', tbl_state.name) as site");
        $this->db->select(array($tbl_shift_location . ".suburb", $tbl_shift_location . '.address', $tbl_shift_location . '.state', $tbl_shift_location . '.postal'));
        $this->db->from($tbl_shift_location);
        $this->db->join('tbl_state', 'tbl_state.id = ' . $tbl_shift_location . '.state', 'left');
        $this->db->where(array($tbl_shift_location . '.shiftId' => $shiftId));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->result();
    }

    public function common_user_id_for_shift_query() {
        $query = "CASE 
                    when s.booked_by = 1 THEN (select siteId from tbl_shift_site where shiftId = s.id)
                    when s.booked_by = 2 THEN (select participantId from tbl_shift_participant where shiftId = s.id)
                    when s.booked_by = 7 THEN (select user_for from tbl_shift_users where user_type = 7 AND shiftId = s.id)
                    end";
        return $query;
    }

    public function common_user_name_for_shift_query() {
        $query = "CASE 
                    when s.booked_by = 1 THEN (select os.site_name from tbl_shift_site as ss INNER JOIN tbl_organisation_site as os ON os.id = ss.siteId where ss.shiftId = s.id)
                    when s.booked_by = 2 THEN (select REPLACE(concat_ws(' ',trim(p.firstname),trim(middlename),trim(lastname)), '  ', ' ') from tbl_shift_participant as sp INNER JOIN tbl_participant as p ON p.id = sp.participantId where sp.shiftId = s.id)
                    when s.booked_by = 7 THEN (select h.name from tbl_shift_users as su INNER JOIN tbl_house as h ON h.id = su.user_for AND su.user_type = 7 where su.shiftId = s.id)
                    end";

        return $query;
    }

    public function common_booked_for_shift_query() {
        $query = "CASE 
                    when s.booked_by = 1 THEN 'Site'
                    when s.booked_by = 2 THEN 'Participant'
                    when s.booked_by = 7 THEN 'House'
                    end";

        return $query;
    }

    public function get_shift_participant($shiftId) {
        $tbl_shift = TBL_PREFIX . 'shift';
        $tbl_shift_participnt = TBL_PREFIX . 'shift_participant';
        $tbl_participnt = TBL_PREFIX . 'participant';

        $this->db->select('CONCAT("\'",tbl_participant.firstname,"\' ",tbl_participant.middlename, " ", tbl_participant.lastname ) as participantName');
        $this->db->select(array($tbl_participnt . '.firstname', $tbl_participnt . '.id as participantId'));

        $this->db->from($tbl_shift_participnt);
        $this->db->join($tbl_participnt, $tbl_shift_participnt . '.participantId = ' . $tbl_participnt . '.id', 'left');
        $this->db->where(array($tbl_shift_participnt . '.shiftId' => sprintf("%d",$shiftId)));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
//        last_query();
        return $query->result_array();
    }

    public function get_shift_oganization($shiftId) {
        $tbl_shift_site = TBL_PREFIX . 'shift_site';
        $tbl_organisation_site = TBL_PREFIX . 'organisation_site';

        $this->db->select(array($tbl_organisation_site . '.site_name', $tbl_organisation_site . '.id as siteId'));

        $this->db->from($tbl_shift_site);
        $this->db->join($tbl_organisation_site, $tbl_organisation_site . '.id = ' . $tbl_shift_site . '.siteId', 'inner');
        $this->db->where(array($tbl_shift_site . '.shiftId' => sprintf("%d",$shiftId)));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
//        last_query();
        return $query->result();
    }

    public function get_shift_requirement($shiftId = 0, $booked_by = false) {

        $arr['requirement'] = [];
        $arr['requirement_mobility'] = [];
        $arr['org_shift_data'] = [];
        $arr['requirement_name'] = [];
        $arr['requirement_mobility_name'] = [];
        $arr['org_shift_data_name'] = [];
        if ($booked_by == 1 || $booked_by == 7) {
            $this->db->select(["pg.id", "pg.name", "pg.key_name", "pg.type", "(SELECT 1 FROM tbl_shift_requirements as sub_srs WHERE sub_srs.shiftId='" . $shiftId . "' AND pg.id=sub_srs.requirementId) as active"]);
            $case = "CASE WHEN pg.key_name='other' AND coalesce((SELECT 1 FROM tbl_shift_requirements as sub_srs WHERE sub_srs.shiftId='" . $shiftId . "' AND pg.id=sub_srs.requirementId LIMIT 1),0) =1 
            THEN (SELECT sub_srs.requirement_other FROM tbl_shift_requirements as sub_srs WHERE sub_srs.shiftId='" . $shiftId . "' AND pg.id=sub_srs.requirementId LIMIT 1)
            ELSE '' END";
            $this->db->select([$case . " as other_title", $case . " as other_value"]);
            $this->db->from('tbl_participant_genral pg');
            $this->db->where('pg.status', 1);
            $this->db->where('pg.type', "mobility");
            $this->db->order_by('pg.order', 'ASC');
            $query = $this->db->get();
            $reqDataMobilityName = $query->result_array();

            $arr['requirement_mobility'] = $reqDataMobilityName;

            $reqMobilityName = array_merge([], array_values(array_filter($reqDataMobilityName, function($val) {
                                return $val['active'] > 0;
                            })));
            $reqMobilityName = !empty($reqMobilityName) ? array_map(function($val) {
                        $val = (object) $val;
                        if ($val->key_name == 'other') {
                            $val->name = $val->name . ' (' . $val->other_title . ')';
                        }
                        return $val;
                    }, $reqMobilityName) : [];
            $arr['requirement_mobility_name'] = $reqMobilityName;

            $this->db->select(["pg.id", "pg.name", "pg.key_name", "pg.type", "(SELECT 1 FROM tbl_shift_requirements as sub_srs WHERE sub_srs.shiftId='" . $shiftId . "' AND pg.id=sub_srs.requirementId) as active"]);
            $case = "CASE WHEN pg.key_name='other' AND coalesce((SELECT 1 FROM tbl_shift_requirements as sub_srs WHERE sub_srs.shiftId='" . $shiftId . "' AND pg.id=sub_srs.requirementId LIMIT 1),0) =1 
            THEN (SELECT sub_srs.requirement_other FROM tbl_shift_requirements as sub_srs WHERE sub_srs.shiftId='" . $shiftId . "' AND pg.id=sub_srs.requirementId LIMIT 1)
            ELSE '' END";
            $this->db->select([$case . " as other_title", $case . " as other_value"]);
            $this->db->from('tbl_participant_genral pg');
            $this->db->where('pg.status', 1);
            $this->db->where('pg.type', "assistance");
            $this->db->order_by('pg.order', 'ASC');
            $query = $this->db->get();
            $reqDataName = $query->result_array();

            $arr['requirement'] = $reqDataName;

            $reqName = array_merge([], array_values(array_filter($reqDataName, function($val) {
                                return $val['active'] > 0;
                            })));
            $reqName = !empty($reqName) ? array_map(function($val) {
                        $val = (object) $val;
                        if ($val->key_name == 'other') {
                            $val->name = $val->name . ' (' . $val->other_title . ')';
                        }
                        return $val;
                    }, $reqName) : [];
            $arr['requirement_name'] = $reqName;

            if ($booked_by == 1) {
                $arr['org_shift_data'] = $this->get_organisation_requirements_by_site_id_or_shft_id('2', $shiftId);
                $arr['org_shift_data_name'] = $this->get_organisation_requirements_by_site_id_or_shft_id('3', $shiftId);
            } else if ($booked_by == 7) {
                $arr['org_shift_data'] = $this->get_organisation_requirements_by_house_id_or_shft_id('2', $shiftId);
                $arr['org_shift_data_name'] = $this->get_organisation_requirements_by_house_id_or_shft_id('3', $shiftId);
            }
        } else if ($booked_by == 2 || $booked_by == 3) {
            $this->load->model('Schedule_model');
            $query = $this->Schedule_model->get_shift_requirement_for_participant_by_participant_id_or_shift_id('2', $shiftId);
            $res = $query->result();
            $query = $this->Schedule_model->get_shift_requirement_for_participant_by_participant_id_or_shift_id('3', $shiftId);
            $resName = $query->result();

            $arr['requirement_mobility'] = array_values(array_filter($res, function($val) {
                        return strtolower($val->type) == 'mobility';
                    }));
            $arr['requirement'] = array_values(array_filter($res, function($val) {
                        return strtolower($val->type) == 'assistance';
                    }));
            $arr['requirement_mobility_name'] = array_values(array_filter($resName, function($val) {
                        return strtolower($val->type) == 'mobility';
                    }));
            $arr['requirement_name'] = array_values(array_filter($resName, function($val) {
                        return strtolower($val->type) == 'assistance';
                    }));
        }
        $tbl_requirement = TBL_PREFIX . 'shift_requirement';
        $tbl_shift_requirements = TBL_PREFIX . 'shift_requirements';

        $arr['shift_requirement'] = '';

        /*  $dt_query = $this->db->select(array($tbl_requirement . ".id", $tbl_requirement . ".name", $tbl_shift_requirements . ".requirementId as active"));
          $this->db->from($tbl_requirement);
          $this->db->join($tbl_shift_requirements, $tbl_shift_requirements . '.requirementId = ' . $tbl_requirement . '.id AND shiftId = ' . $shiftId, 'left');
          $query = $this->db->get();
          $arr['requirement'] = $query->result(); */

        /*   $requirement = [];
          $org_shift_data = []; */
        $requirementName = !empty($arr['requirement_name']) ? array_column($arr['requirement_name'], 'name') : [];
        $requirementMobilityName = !empty($arr['requirement_mobility_name']) ? array_column($arr['requirement_mobility_name'], 'name') : [];
        $orgShiftDataName = !empty($arr['org_shift_data_name']) ? array_column($arr['org_shift_data_name'], 'name') : [];
        /* if (!empty($arr['requirement_name'])) {
          foreach ($arr['requirement'] as $val) {
          if ($val->active > 0) {
          $requirement[] = $val->name;
          }
          }
          }

          if(!empty($arr['org_shift_data'])) {
          foreach ($arr['org_shift_data'] as $val) {
          $val = (object) $val;
          if ($val->active > 0) {
          $org_shift_data[] = $val->name;
          }
          }
          } */
        $textString = '';
        if ($booked_by == 1) {
            $textString = 'Mobility Requirements: ' . (!empty($requirementMobilityName) ? implode(', ', $requirementMobilityName) : 'N/A') . PHP_EOL . 'Assistance Requirement: ' . (!empty($requirementName) ? implode(', ', $requirementName) : 'N/A') . PHP_EOL . ' Org Requirements: ' . (!empty($orgShiftDataName) ? implode(', ', $orgShiftDataName) : 'N/A');
        } else if ($booked_by == 7) {
            $textString = 'Mobility Requirements: ' . (!empty($requirementMobilityName) ? implode(', ', $requirementMobilityName) : 'N/A') . PHP_EOL . 'Assistance Requirement: ' . (!empty($requirementName) ? implode(', ', $requirementName) : 'N/A') . PHP_EOL . ' House Requirements: ' . (!empty($orgShiftDataName) ? implode(', ', $orgShiftDataName) : 'N/A');
        } else if ($booked_by == 2 || $booked_by == 3) {
            $textString = 'Mobility Requirements: ' . (!empty($requirementMobilityName) ? implode(', ', $requirementMobilityName) : 'N/A') . PHP_EOL . ' Assistance Requirements: ' . (!empty($requirementName) ? implode(', ', $requirementName) : 'N/A');
        }
        $arr['shift_requirement'] = $textString;

        return $arr;
    }

    public function get_allocated_member($shiftId) {
        $this->db->select("(6371 * acos( 
                cos( radians(sl.lat) ) 
              * cos( radians(ma.lat) ) 
              * cos( radians(ma.long ) - radians(sl.long) ) 
              + sin( radians(sl.lat) ) 
              * sin( radians(ma.lat ) )
                ) ) as distance_km");
        $this->db->select(["CONCAT_ws(' ',m.firstname,m.middlename,m.lastname ) as memberName", "sm.memberId", "sm.created", "sm.confirmed_with_allocated"]);
        $this->db->select("(select memberId from tbl_shift_preferred_member where memberId = sm.memberId AND shiftId = sm.shiftId) as preferred");
        $this->db->select("(select phone from tbl_member_phone where memberId = sm.memberId AND primary_phone = 1 AND archive = 0) as member_phone");
        $this->db->from("tbl_shift_member as sm");
        $this->db->join("tbl_member as m", 'sm.memberId = m.id', 'INNER');

        $this->db->join("tbl_member_address as ma", 'ma.memberId = m.id AND ma.archive = 0', 'INNER');
        $this->db->join("tbl_shift_location as sl", 'sl.shiftId = sm.shiftId', 'INNER');

        $this->db->where(array('sm.shiftId' => $shiftId));
        $this->db->where(array('sm.status' => 1));
        $this->db->group_by('sm.shiftId');
        $this->db->where(array('sm.archive' => 0));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $val) {
                $val->confirmed_with_allocated = ($val->confirmed_with_allocated !== "0000-00-00 00:00:00") ? $val->confirmed_with_allocated : "";
                $val->preferred = ($val->preferred > 0) ? true : false;
                $val->allocate_on = date('d/m/Y - h:i A', strtotime($val->created));
                $val->remaning_time = ((strtotime($val->created) + $this->config->item('member_allocated_time')) - strtotime(DATE_TIME)) * 1000;
                $val->distance_km = number_format($val->distance_km, 2) . ' km';
            }
        }
       
        return $query->result();
    }

    public function get_accepted_shift_member($shiftId) {
        $this->db->select("(6371 * acos( 
                cos( radians(sl.lat) ) 
              * cos( radians(ma.lat) ) 
              * cos( radians(ma.long ) - radians(sl.long) ) 
              + sin( radians(sl.lat) ) 
              * sin( radians(ma.lat ) )
                ) ) as distance_km");

        $this->db->select("CONCAT_ws(' ',m.firstname,m.middlename,m.lastname ) as memberName");
        $this->db->select(array('sm.created', "m.id as memberId", "sm.confirm_on", "sm.confirmed_with_allocated"));
        $this->db->select("(select memberId from tbl_shift_preferred_member where memberId = sm.memberId AND shiftId = sm.shiftId) as preferred");
        $this->db->select("(select phone from tbl_member_phone where memberId = sm.memberId AND primary_phone = 1 AND archive = 0) as member_phone");

        $this->db->from('tbl_shift_member as sm');

        $this->db->join("tbl_member as m", 'sm.memberId = m.id', 'INNER');
        $this->db->join("tbl_member_address as ma", 'ma.memberId = m.id AND ma.archive = 0', 'INNER');
        $this->db->join("tbl_shift_location as sl", 'sl.shiftId = sm.shiftId', 'INNER');

        $this->db->where(array('sm.shiftId' => $shiftId));
        $this->db->where(array('sm.status' => 3));
        $this->db->where(array('sm.archive' => 0));
        $this->db->group_by('sm.shiftId');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $val) {
                $val->confirmed_with_allocated = ($val->confirmed_with_allocated !== "0000-00-00 00:00:00") ? $val->confirmed_with_allocated : "";
                $val->allocate_on = date('d/m/Y - h:ia', strtotime($val->created));
                $val->confirm_on = date('d/m/Y - h:ia', strtotime($val->created));
                $val->remaning_time = ((strtotime($val->created) + $this->config->item('member_allocated_time')) - strtotime(DATE_TIME)) * 1000;
                $val->distance_km = number_format($val->distance_km, 2) . ' km';
            }
        }
        return $query->result();
    }

    public function get_rejected_member($shiftId) {
        $tbl_shift = TBL_PREFIX . 'shift';
        $tbl_shift_member = TBL_PREFIX . 'shift_member';
        $tbl_shift_preferred_member = TBL_PREFIX . 'shift_preferred_member';
        $tbl_member = TBL_PREFIX . 'member';

        $this->db->select("CONCAT(" . $tbl_member . ".firstname,' '," . $tbl_member . ".middlename,' '," . $tbl_member . ".lastname ) as memberName");
        $this->db->from($tbl_shift_member);
        $this->db->join($tbl_member, $tbl_shift_member . '.memberId = ' . $tbl_member . '.id', 'left');
        $this->db->where(array($tbl_shift_member . '.shiftId' => $shiftId));
        $this->db->where(array($tbl_shift_member . '.status' => 2, $tbl_shift_member . '.archive' => 0));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return $result;
    }

    /* if any new column added on tbl_shift please updated on get_manual_member_look_up_for_create_shift() in modules\schedule\controllers\Shift_Schedule.php */

    public function get_shift_details($shiftId, $other = []) {
        $multiple = $other['multiple'] ?? false;
        $required_level_and_paypoint = $other['required_level_and_paypoint'] ?? false;
        $shift_for = $other['shift_for'] ?? true;
        $tblShift = $other['table_name_shift'] ?? 'tbl_shift';
        $tblShiftLineItem = $other['table_name_line_item'] ?? 'tbl_shift_line_item_attached';

        $select_column = ["s.id", "s.shift_date", "s.start_time", "s.end_time", "s.expenses", "s.created", "s.status", "s.booked_by"];
        $this->db->select($select_column);
        $this->db->select("CONCAT(MOD( TIMESTAMPDIFF(hour,s.start_time,s.end_time), 24), ':',MOD( TIMESTAMPDIFF(minute,s.start_time,s.end_time), 60), ' hrs') as duration");
        $this->db->from($tblShift . " as s");


        if ($shift_for) {
            $sub_query_shif_for = $this->common_user_name_for_shift_query();
            $this->db->select("(" . $sub_query_shif_for . ") as shift_for");

            $sub_query_booked_for = $this->common_booked_for_shift_query();
            $this->db->select("(" . $sub_query_booked_for . ") as booked_for");

            $sub_query_userId = $this->common_user_id_for_shift_query();
            $this->db->select("(" . $sub_query_userId . ") as userId");
        }

        if ($required_level_and_paypoint) {
            $this->db->select("substring_index( group_concat(cl.level_priority order by cl.level_priority desc, cp.point_priority desc SEPARATOR '@@__BREAKER__@@' ),'@@__BREAKER__@@',1) as required_level_priority", false);
            $this->db->select("substring_index( group_concat(cp.point_priority order by cl.level_priority desc, cp.point_priority desc SEPARATOR '@@__BREAKER__@@' ),'@@__BREAKER__@@',1) as required_point_priority", false);

            $this->db->join($tblShiftLineItem . " as slia", "slia.shiftId = s.id AND slia.archive = 0", "LEFT");
            $this->db->join("tbl_finance_line_item as fli", "fli.id = slia.line_item", "LEFT");
            $this->db->join("tbl_classification_level as cl", "cl.id = fli.levelId", "LEFT");
            $this->db->join("tbl_classification_point as cp", "cp.id = fli.pay_pointId", "LEFT");
        }

        if ($multiple) {
            $this->db->group_by("s.id");
            $this->db->where_in('s.id', $shiftId);
        } else {
            $this->db->where(array('s.id' => $shiftId));
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->result();

        $result = $mutliple_result = array();
        if (!empty($response)) {
            foreach ($response as $key => $val) {
                $result = (array) $val;

                $result['diff'] = (strtotime(date('Y-m-d H:i:s', strtotime($val->start_time))) - strtotime(date('Y-m-d H:i:s'))) * 1000;

                $result['duration'] = ($val->duration);

                if ($required_level_and_paypoint) {
                    $result['required_level_priority'] = $result['required_level_priority'] ?? 0;
                    $result['required_point_priority'] = $result['required_point_priority'] ?? 0;
                }

                if ($multiple) {
                    $mutliple_result[] = $result;
                }
            }
        }
        if ($multiple) {
            return $mutliple_result;
        } else {
            return $result;
        }
    }

    public function get_cancelled_details($shiftId) {
        $tbl_shift_cancelled = TBL_PREFIX . 'shift_cancelled';

        $this->db->select(array('reason', 'cancel_type', 'cancel_by', 'person_name'));
        $this->db->from($tbl_shift_cancelled);
        $this->db->where(array($tbl_shift_cancelled . '.shiftId' => $shiftId));

        $this->db->order_by($tbl_shift_cancelled . '.id', 'desc');
        $this->db->limit(1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $val) {
                $val->reinitiate = false;

                if ($val->cancel_type == 'member') { // 1 = member
                    $this->load->model('Schedule_model');
                    $val->reinitiate = true;
                    $x = $this->Schedule_model->get_member_name1($val->cancel_by);
                    $val->cancel_by = $x->memberName . ' (Member)';
                } elseif ($val->cancel_type == 'participant') {
                    $tbl_participant = TBL_PREFIX . 'participant';
                    $this->db->select("CONCAT(" . $tbl_participant . ".firstname,' '," . $tbl_participant . ".middlename,' '," . $tbl_participant . ".lastname ) as participantName");
                    $this->db->from($tbl_participant);
                    $this->db->where(array($tbl_participant . '.id' => $val->cancel_by));
                    $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
                    $participant_result = $query->row();

                    if (!empty($participant_result)) {
                        $val->cancel_by = $participant_result->participantName;
                    }
                } elseif ($val->cancel_type == 'kin') {
                    $clwn = array("concat(firstname,' ',lastname) as kinName");
                    $kin_res = $this->basic_model->get_row('participant_kin', $clwn, $where = array('id' => $val->cancel_by));

                    if (!empty($kin_res)) {
                        $val->cancel_by = $kin_res->kinName;
                    }
                } elseif ($val->cancel_type == 'booker') {
                    $clwn = array("concat(firstname,' ',lastname) as bookerName");
                    $kin_res = $this->basic_model->get_row('participant_booking_list', $clwn, $where = array('id' => $val->cancel_by));
                    if (!empty($kin_res)) {
                        $val->cancel_by = $kin_res->bookerName;
                    }
                } elseif ($val->cancel_type == 'org') {
                    $val->cancel_by = 'Oranization';
                } elseif ($val->cancel_type == 'site') {
                    $val->cancel_by = 'Site';
                } elseif ($val->cancel_type == 'house') {
                    $val->cancel_by = 'House';
                } else {
                    $val->cancel_by = 'N/A';
                }
            }
        }

        return $result;
    }

    public function get_shift_confirmation_details($shiftId) {
        $this->db->select(" 
            (CASE 
                WHEN sc.confirm_with = 1 THEN (select r.name from tbl_participant_booking_list as sub_booker INNER JOIN tbl_relations as r ON r.id = sub_booker.relation where sub_booker.id = sc.confirm_userId)
                else ''
            end) as confirmer_relation");

        $this->db->select("concat_ws(' ',firstname,lastname) as confirmer_name");
        $this->db->select(array('confirm_with', 'confirm_by', 'phone', 'email', "confirmed_with_booker"));
        $this->db->from("tbl_shift_confirmation as sc");
        $this->db->where(array('shiftId' => $shiftId));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->row();


        if (!empty($response)) {
            $response->confirmed_with_booker = ($response->confirmed_with_booker == '0000-00-00 00:00:00') ? '' : $response->confirmed_with_booker;
        }

        return $response;
    }

    public function get_shift_caller($shiftId) {
        $tbl_shift_caller = TBL_PREFIX . 'shift_caller';

        $this->db->select(array('firstname', 'lastname', 'phone', 'booking_method', 'email'));
        $this->db->from($tbl_shift_caller);
        $this->db->where(array('shiftId' => $shiftId));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->row();

        return (array) $response;
    }

    /* if any new column added on tbl_shift please updated on get_manual_member_look_up_for_create_shift() in modules\schedule\controllers\Shift_Schedule.php */

    public function get_available_member_by_city($objShift, $equal_and_greater = false) {
        if ($objShift->getRequired_level() == 0 && $objShift->getRequired_paypoint() == 0) {
            return array('available_members' => [], 'member_details' => []);
        }

        $limit = $objShift->getLimit();
        $pre_selected_member = $objShift->getPre_selected_member();

        //$shift_time = getAvailabilityType($objShift->getStartTime());
        $shiftTimeData = $this->get_shift_availabilityType_by_shift_id($objShift->getShiftId(), ['shift_table' => $objShift->getShiftTableName(true), 'shift_time_category_table' => $objShift->getShiftTimeCategoryTableName(true), 'alias_column' => 'availability_type_concat', 'get_having_condition' => 1]);
        
        $havingConditionData = $shiftTimeData['having_condition'] ?? '';
        $date = date('Y-m-d', strtotime($objShift->getShiftDate()));

        $contact_count_sub_query = $this->count_of_previous_shift_done_with_member_sub_query($objShift);
        $shared_count_place_sub_query = $this->count_of_preference_shared_places_sub_query($objShift);
        $shared_count_activity_sub_query = $this->count_of_preference_shared_activity_sub_query($objShift);
        $member_busy_sub_query = $this->get_member_who_busy_in_another_shift_at_day_sub_query($objShift);

        $this->db->select("(" . $contact_count_sub_query . ") as contact_count");
        $this->db->select("concat(cl.level_priority, cp.point_priority) as level_priority_concat");
        $this->db->select("(" . $shared_count_place_sub_query . ") as shared_place_count");
        $this->db->select("(" . $shared_count_activity_sub_query . ") as shared_activity_count");

        $this->db->select("GROUP_CONCAT(DISTINCT CASE WHEN DATE(s.start_time)=DATE(mal.availability_date) THEN mal.availability_type ELSE null END) as availability_type_concat");
        $this->db->select("GROUP_CONCAT(DISTINCT CASE WHEN DATE(s.end_time)=DATE(mal.availability_date) THEN mal.availability_type ELSE null END) as availability_type_data_last");

        $this->db->select("(CASE 
   WHEN mal.flexible_availability = 1 THEN (mal.travel_km + mal.flexible_km) 
   else mal.travel_km end) as member_can_travel");

        $this->db->select(array("ma.memberId", "concat_ws(' ',m.firstname, m.middlename, m.lastname) as member_name",
            "mal.flexible_availability", "mal.flexible_km", "mal.travel_km", "ma.city as suburb"));

        $this->db->select("(6371 * acos( 
                cos( radians(sl.lat) ) 
              * cos( radians(ma.lat) ) 
              * cos( radians(ma.long ) - radians(sl.long) ) 
              + sin( radians(sl.lat) ) 
              * sin( radians(ma.lat ) )
                ) ) as distance_km");

        $this->db->from($objShift->getShiftTableName(true) . ' as s');
        $this->db->join($objShift->getShiftLocationTableName(true) . ' as sl', "sl.shiftId = s.id ", "INNER");
        $this->db->join('tbl_member_address as ma', 'ma.city = sl.suburb', 'INNER');
        $this->db->join('tbl_member as m', 'm.id = ma.memberId AND m.enable_app_access = 1', 'INNER');
        $this->db->join('tbl_member_availability_list as mal', 'mal.memberId = ma.memberId AND mal.archive = 0', 'INNER');

        $this->db->join('tbl_member_position_award as mpa', 'mpa.memberId = ma.memberId AND mpa.archive = 0', 'INNER');
        $this->db->join('tbl_recruitment_applicant_work_area as work_area', 'work_area.id = mpa.work_area', 'INNER');

        $this->db->join('tbl_classification_level as cl', 'cl.id = mpa.level AND cl.archive = 0', 'INNER');
        $this->db->join('tbl_classification_point as cp', 'cp.id = mpa.pay_point AND cp.archive = 0', 'INNER');

        if ($objShift->getRequest_for() === "create_roster") {
            $this->db->select("group_concat(distinct s.id order by s.id asc) as coming_shiftId");
//            $this->db->having("coming_shiftId = (select id from " . $objShift->getShiftTableName(true) . "  order by id asc)");
        } else {
            $this->db->where(array("sl.shiftId" => $objShift->getShiftId()));
        }

        $this->db->where("sl.shiftId = s.id", null, false);
        $this->db->where(array("m.status" => 1, "m.archive" => 0));
        //$this->db->where("date(mal.availability_date) = date(s.shift_date)",null,false);
        $this->db->where("date(mal.availability_date) between  date(s.start_time) and date(s.end_time)", null, false);
        //$this->db->where_in("mal.availability_type", $shift_time);
        $this->db->having("member_can_travel > distance_km", null, false);
        if (!empty($havingConditionData)) {
            $this->db->having($havingConditionData, null, false);
        }

        $this->db->group_by("m.id");

        if ($limit > 0) {
            $this->db->limit($limit);
        }
        if ($objShift->getAvailable_member_order() === "most_contact") {
            $this->db->order_by("contact_count desc, cl.level_priority asc, cp.point_priority asc, shared_activity_count desc, shared_place_count desc", null, false);
        } elseif ($objShift->getAvailable_member_order() === "most_shared_preferences") {
            $this->db->order_by("shared_activity_count desc, shared_place_count desc, contact_count desc, cl.level_priority asc, cp.point_priority asc", null, false);
        } else {
            $this->db->order_by("cl.level_priority asc, cp.point_priority asc, contact_count desc, shared_activity_count desc, shared_place_count desc", null, false);
        }

        $this->db->where("m.id NOT IN (" . $member_busy_sub_query . ")", null, false);

        if ($equal_and_greater) {
            $this->db->having("level_priority_concat>=" . $objShift->getRequired_level() . $objShift->getRequired_paypoint(), null, false);
        } else {
            $this->db->where("cl.level_priority = " . $objShift->getRequired_level(), null, false);
            $this->db->where("cp.point_priority = " . $objShift->getRequired_paypoint(), null, false);
        }

        if (!empty($pre_selected_member)) {
            $this->db->where_not_in('mal.memberId', $pre_selected_member);
        }

        $memberNameSearch = $objShift->getMemeberNameSearch();
        if (!empty($memberNameSearch)) {
            $this->db->like("concat_ws(' ', m.firstname, m.middlename, m.lastname)", $memberNameSearch);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());


        $available_members = array();
        $result = $query->result();

        $memberIdWithDistance = array();
        if (!empty($result)) {
            foreach ($result as $val) {

                $memberIdWithDistance[$val->memberId]['distance_km'] = number_format($val->distance_km, 2);
                $memberIdWithDistance[$val->memberId]['contact_count'] = $val->contact_count;
                $memberIdWithDistance[$val->memberId]['member_name'] = $val->member_name;
                $memberIdWithDistance[$val->memberId]['suburb'] = $val->suburb;
                $memberIdWithDistance[$val->memberId]['memberId'] = $val->memberId;
                $memberIdWithDistance[$val->memberId]['value'] = $val->memberId;
                $memberIdWithDistance[$val->memberId]['label'] = $val->member_name;
                $available_members[] = $val->memberId;
            }
        }

        return array('available_members' => $available_members, 'member_details' => $memberIdWithDistance);
    }

    public function get_available_member_by_preferences($memberIds, $particpantID) {
        $tbl_participant_place = TBL_PREFIX . 'participant_place';
        $tbl_member_place = TBL_PREFIX . 'member_place';

        $this->db->select(array("count(tbl_participant_place.placeId) as cnt", "tbl_member_place.memberId"));
        $this->db->from($tbl_participant_place);

        $this->db->where('participantId', $particpantID);
        $this->db->join($tbl_member_place, $tbl_member_place . '.placeId = ' . $tbl_participant_place . '.placeId AND ' . $tbl_member_place . '.memberId IN (' . implode(', ', $memberIds) . ')', 'inner');
        // $this->db->join($tbl_place, $tbl_member_place . '.placeId = ' . $tbl_place . '.id', 'inner');
        $this->db->group_by("tbl_member_place.memberId");
        $this->db->order_by("cnt", 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $place_member = $query->row();

        if (!empty($place_member)) {
            return $place_member->memberId;
        } else {
            return false;
        }
    }

    public function get_prefference_activity_places($memberId, $participantId) {
        $tbl_place = TBL_PREFIX . 'place';
        $tbl_participant_place = TBL_PREFIX . 'participant_place';
        $tbl_member_place = TBL_PREFIX . 'member_place';

        $this->db->select(array($tbl_place . '.name'));
        $this->db->from($tbl_participant_place);
        $this->db->join($tbl_member_place, $tbl_member_place . '.placeId = ' . $tbl_participant_place . '.placeId', 'inner');
        $this->db->join($tbl_place, $tbl_member_place . '.placeId = ' . $tbl_place . '.id', 'inner');
        $this->db->where($tbl_member_place . '.memberId', $memberId);
        $this->db->where('participantId', $participantId);
        $query = $this->db->get();

        $result['shared_place'] = $query->result_array();


        $tbl_activity = TBL_PREFIX . 'activity';
        $tbl_participant_activity = TBL_PREFIX . 'participant_activity';
        $tbl_member_activity = TBL_PREFIX . 'member_activity';

        $this->db->select(array($tbl_activity . '.name'));
        $this->db->from($tbl_participant_activity);
        $this->db->join($tbl_member_activity, $tbl_member_activity . '.activityId = ' . $tbl_participant_activity . '.activityId', 'inner');
        $this->db->join($tbl_activity, $tbl_participant_activity . '.activityId = ' . $tbl_activity . '.id', 'inner');
        $this->db->where($tbl_member_activity . '.memberId', $memberId);
        $this->db->where('participantId', $participantId);
        $query = $this->db->get();

        $result['shared_activity'] = $query->result_array();

        return $result;
    }

    function get_member_details($memberId) {
        $tbl_member = TBL_PREFIX . 'member';
        $tbl_member_address = TBL_PREFIX . 'member_address';

        $this->db->select("CONCAT(" . $tbl_member . ".firstname,' '," . $tbl_member . ".middlename,' '," . $tbl_member . ".lastname ) as memberName");
        $this->db->select(array($tbl_member . '.id as memberId', $tbl_member_address . '.city as suburb'));
        $this->db->from($tbl_member);
        $this->db->join($tbl_member_address, $tbl_member_address . '.memberId = ' . $tbl_member . '.id', 'left');
        $this->db->where($tbl_member . '.id', $memberId);
        $query = $this->db->get();

        return $result = $query->row();
    }

    public function count_of_previous_shift_done_with_member_sub_query($objShift) {
        $this->db->select(array('count(sub_sm.memberId)'));
        $this->db->from('tbl_shift_member as sub_sm');
        $this->db->join('tbl_shift as sub_s', 'sub_sm.shiftId = sub_s.id and sub_s.status = 6', 'inner');

        if ($objShift->getBookedBy() == 1) {
            $this->db->join("tbl_shift_site as sub_ss", "sub_ss.shiftId = sub_s.id AND sub_ss.siteId = " . $objShift->getUserId(), "inner");
        } elseif ($objShift->getBookedBy() == 2) {
            $this->db->join("tbl_shift_participant as sub_sp", "sub_sp.shiftId = sub_s.id AND sub_sp.participantId = " . $objShift->getUserId(), "inner");
        } elseif ($objShift->getBookedBy() == 7) {
            $this->db->join("tbl_shift_users as sub_u", "sub_u.shiftId = sub_s.id AND sub_u.user_type = 7 AND sub_u.user_for = " . $objShift->getUserId(), "inner");
        }

        $this->db->where_in('sub_sm.memberId = m.id');
        $this->db->where(array('sub_sm.status' => 3, 'sub_sm.archive' => 0));
        $this->db->where("sub_sm.memberId = m.id", null, false);
        return $this->db->get_compiled_select();
    }

    public function count_of_preference_shared_places_sub_query($objShift) {
        if ($objShift->getBookedBy() == 1) {
            return "0";
        } elseif ($objShift->getBookedBy() == 2) {
            $this->db->select("count(sub_pp.placeId) as cnt");
            $this->db->from("tbl_participant_place as sub_pp");
            $this->db->join("tbl_member_place as sub_mp", "sub_mp.placeId = sub_pp.placeId AND sub_mp.type = sub_pp.type", "INNER");
            $this->db->where("sub_pp.participantId", $objShift->getUserId());
            $this->db->where("sub_mp.memberId = m.id", null, false);

            return $this->db->get_compiled_select();
        } elseif ($objShift->getBookedBy() == 7) {
            return "0";
        }
    }

    public function count_of_preference_shared_activity_sub_query($objShift) {
        if ($objShift->getBookedBy() == 1) {
            return "0";
        } elseif ($objShift->getBookedBy() == 2) {
            $this->db->select("count(sub_pa.activityId) as cnt");
            $this->db->from("tbl_participant_activity as sub_pa");
            $this->db->join("tbl_member_activity as sub_ma", "sub_ma.activityId = sub_pa.activityId AND sub_ma.type = sub_pa.type", "INNER");
            $this->db->where("sub_pa.participantId", $objShift->getUserId());
            $this->db->where("sub_ma.memberId = m.id", null, false);

            return $this->db->get_compiled_select();
        } elseif ($objShift->getBookedBy() == 7) {
            return "0";
        }
    }

    public function get_member_who_busy_in_another_shift_at_day_sub_query() {
        $this->db->select("sm.memberId");
        $this->db->from("tbl_shift as sub_s");
        $this->db->join("tbl_shift_member as sm", "sub_s.id = sm.shiftId AND sm.status IN (1, 3) and sub_s.status IN (2,7) AND sm.archive = 0", "INNER");
        //$this->db->where("sub_s.shift_date = s.shift_date", null, false);
        $this->db->where("((sub_s.start_time between s.start_time AND s.end_time) OR  (sub_s.end_time between s.start_time AND s.end_time) OR (s.start_time between sub_s.start_time AND sub_s.end_time) OR (s.end_time between sub_s.start_time AND sub_s.end_time))", null, false);

        return $this->db->get_compiled_select();
    }

    function get_organisation_requirements_by_site_id_or_shft_id($type = 1, $Id = 0) {

        $this->db->select(["or.name as label", "or.id as value"]);
        $this->db->from('tbl_organisation_requirement as or');
        if ($type == 1 || $type == 2) {
            $this->db->join('tbl_organisation_requirements as ors', "ors.requirementId=or.id", "inner");
            $this->db->join('tbl_organisation_site as os', 'os.organisationId=ors.organisationId');
        }
        if ($type == 1) {
            $this->db->where('os.id', $Id);
        } else if ($type == 2) {
            $this->db->select(["or.name", "or.id", "(SELECT 1 FROM tbl_shift_org_requirements as sub_sor where ss.shiftId=sub_sor.shiftId AND ors.requirementId=sub_sor.requirementId) as active"], false);
            $this->db->join("tbl_shift_site as ss", "ss.siteId=os.id AND ss.shiftId='" . $Id . "'", "inner");
        } elseif ($type == 3) {
            $this->db->select(["or.name"]);
            $this->db->join("tbl_shift_org_requirements as sor", "sor.requirementId=or.id AND sor.shiftId='" . $Id . "'", "inner");
        }
        $qeury = $this->db->get();
        return $qeury->num_rows() > 0 ? $qeury->result_array() : [];
    }

    function get_organisation_requirements_by_house_id_or_shft_id($type = 1, $Id = 0) {
        $this->db->select(["or.name as label", "or.id as value"]);
        $this->db->from('tbl_organisation_requirement as or');
        if ($type == 1 || $type == 2) {
            $this->db->join('tbl_house_and_site_requirement as hsr', "hsr.requirementId=or.id AND hsr.user_type=2", "inner");
            $this->db->join('tbl_house as h', 'h.id=hsr.siteId');
        }
        if ($type == 1) {
            $this->db->where('h.id', $Id);
        } else if ($type == 2) {
            $this->db->select(["or.name", "or.id", "(SELECT 1 FROM tbl_shift_org_requirements as sub_sor where su.shiftId=sub_sor.shiftId AND hsr.requirementId=sub_sor.requirementId) as active"], false);
            $this->db->join("tbl_shift_users as su", "su.user_for=h.id  AND su.user_type=7 AND su.shiftId='" . $Id . "'", "inner");
        } elseif ($type == 3) {
            $this->db->select(["or.name"]);
            $this->db->join("tbl_shift_org_requirements as sor", "sor.requirementId=or.id AND sor.shiftId='" . $Id . "'", "inner");
        }
        $qeury = $this->db->get();
        return $qeury->num_rows() > 0 ? $qeury->result_array() : [];
    }

    /*
     * Send notification to all member near 40 KM of shift
     * when shift move to app
     * @param array of shift ids
     */

    function send_notification_to_all_member($shift_ids) {
        if (!empty($shift_ids)) {
            $this->db->select("(6371 * acos( 
                cos( radians(sl.lat) ) 
              * cos( radians(ma.lat) ) 
              * cos( radians(ma.long ) - radians(sl.long) ) 
                + sin( radians(sl.lat) ) 
              * sin( radians(ma.lat ) )
            ) ) as distance_km");

            $this->db->select(["m.id", "sl.shiftId"]);
            $this->db->from("tbl_member as m");
            $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"', 'inner');
            $this->db->join("tbl_member_address as ma", 'ma.memberId = m.id AND ma.archive = 0', 'INNER');

            $str = implode(',', $shift_ids);
            $this->db->join("tbl_shift_location as sl", "sl.shiftId IN($str)", 'INNER');

            $this->db->where(array('m.status' => 1, 'm.archive' => 0));
            $this->db->having('distance_km < 40');
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            #last_query();
            $result = $query->result();
            #pr($result);
            $temp_ary = $notify_ary = [];
            if (!empty($result)) {
                foreach ($result as $val) {
                    $temp_ary['userId'] = $val->id;
                    $temp_ary['user_type'] = 1;
                    $temp_ary['title'] = 'New Shift is registered in your area.';
                    $temp_ary['shortdescription'] = "New Shift is registered in your area (shift id = $val->shiftId).";
                    $temp_ary['created'] = DATE_TIME;
                    $temp_ary['sender_type'] = 2;
                    $notify_ary[] = $temp_ary;
                }
                #pr($notify_ary);
                if (!empty($notify_ary)) {
                    $this->basic_model->insert_update_batch($action = 'insert', $table_name = 'notification', $notify_ary, $update_base_column_key = '');
                }
            }
        }
        return true;
    }

    public function get_shift_availabilityType_by_shift_id(int $shiftId, $other = []) {
        $tblShiftTimeCategory = $other['shift_time_category_table'] ?? 'tbl_shift_time_category';
        $tblShift = $other['shift_table'] ?? 'tbl_shift';
        $aliasColumnHavingCondition = $other['alias_column'] ?? 'availability_type_data';
        $aliasColumnLastHavingCondition = $other['alias_column_last'] ?? 'availability_type_data_last';
        $getHavingCondition = $other['get_having_condition'] ?? 0;
        $this->db->select(["CASE 
        WHEN ftotd.key_name='daytime' THEN 'am'
        WHEN ftotd.key_name='a_o' THEN 'ao'
        WHEN ftotd.key_name='s_o' THEN 'so'
        WHEN ftotd.key_name='evening' THEN 'pm'
        ELSE '' 
        END as availability_type
        ",
            "CASE
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  DATE_FORMAT(s.start_time,'%H:%i') between '00:00' AND '06:00' and ftotd.key_name='a_o' THEN  'ao'
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  DATE_FORMAT(s.start_time,'%H:%i') between '00:00' AND '06:00' and ftotd.key_name='s_o' THEN 'so'
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  ((DATE_FORMAT(s.start_time,'%H:%i') between '06:00' AND '20:00') OR DATE_FORMAT(s.start_time,'%H:%i')<'06:00')  and ftotd.key_name='daytime' THEN 'am'
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  ((DATE_FORMAT(s.start_time,'%H:%i') between '20:00' AND '23:59') OR DATE_FORMAT(s.start_time,'%H:%i')<'20:00')  and ftotd.key_name='evening' THEN 'pm'
        ELSE '' 
        END as first_day_availability_type
        ",
            "CASE
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  ((DATE_FORMAT(s.end_time,'%H:%i') between '00:00' AND '06:00') OR DATE_FORMAT(s.end_time,'%H:%i')>'06:00') and ftotd.key_name='a_o' THEN  'ao'
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  ((DATE_FORMAT(s.end_time,'%H:%i') between '00:00' AND '06:00') OR DATE_FORMAT(s.end_time,'%H:%i')>'06:00') and ftotd.key_name='s_o' THEN 'so'
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  ((DATE_FORMAT(s.end_time,'%H:%i') between '06:00' AND '20:00') OR DATE_FORMAT(s.end_time,'%H:%i')>'20:00') and ftotd.key_name='daytime' THEN 'am'
        WHEN DATE(s.start_time)!=DATE(s.end_time) AND  ((DATE_FORMAT(s.end_time,'%H:%i') between '20:00' AND '23:59') )  and ftotd.key_name='evening' THEN 'pm'
        ELSE '' 
        END as last_day_availability_type",
            "CASE
        WHEN DATE(s.start_time)!=DATE(s.end_time) THEN '1' 
        ELSE '0' 
        END as shift_in_diffrent_date"
        ]);
        $this->db->select([]);
        $this->db->from($tblShiftTimeCategory . ' as stc');
        $this->db->where('stc.shiftId', $shiftId);
        $this->db->join('tbl_finance_time_of_the_day as ftotd', "ftotd.id=stc.timeId AND stc.archive=ftotd.archive AND stc.archive=0", "inner");
        $this->db->join($tblShift . ' as s', "s.id=stc.shiftId", "inner");
        $this->db->where('stc.shiftId', $shiftId);
        $this->db->where_in('ftotd.key_name', ['daytime', 'a_o', 's_o', 'evening']);
        $this->db->having("availability_type !=''", null, false);
        $query = $this->db->get();
        #last_query(1);
        $res = $query->num_rows() > 0 ? $query->result_array() : [];
        $resFirstDay = array_column($res, 'availability_type');
        $resLastDay = array_column($res, 'availability_type');
        $shiftDiffrentDate = 0;
        if (!empty($res) && $res[0]['shift_in_diffrent_date'] == 1) {
            $shiftDiffrentDate = 1;
            $resFirstDay = array_filter(array_column($res, 'first_day_availability_type'));
            $resLastDay = array_filter(array_column($res, 'last_day_availability_type'));
        }
        $response = ['data' => $res];
        if ($getHavingCondition == 1) {
            $this->db->select('a');
            $this->db->from('a');
            $this->db->group_start();
            $this->db->group_start();
            $r = "FIND_IN_SET('all'," . $aliasColumnHavingCondition . ")";
            $this->db->where($r, null, false);
            if (!empty($resFirstDay)) {
                $this->db->or_group_start();
                foreach ($resFirstDay as $row) {
                    if (empty($row)) {
                        continue;
                    }
                    $r = "FIND_IN_SET('" . $row . "'," . $aliasColumnHavingCondition . ")";
                    $this->db->where($r, null, false);
                }
                $this->db->group_end();
            }
            $this->db->group_end();

            if ($shiftDiffrentDate == 1) {
                $this->db->group_start();
                $r = "FIND_IN_SET('all'," . $aliasColumnLastHavingCondition . ")";
                $this->db->where($r, null, false);
                if (!empty($resLastDay)) {
                    $this->db->or_group_start();
                    foreach ($resLastDay as $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $r = "FIND_IN_SET('" . $row . "'," . $aliasColumnLastHavingCondition . ")";
                        $this->db->where($r, null, false);
                    }
                    $this->db->group_end();
                }
                $this->db->group_end();
            }
            $this->db->group_end();
            $query = $this->db->get_compiled_select();
            $query = str_replace('IS NULL', '', $query);
            $queryData = explode('WHERE', $query);
            $response = ['having_condition' => $queryData[1]];
        }
        return $response;
    }

}
