<?php

class Make_shift_from_roster_model extends CI_Model {

    function make_roster_list_formattable($roster_list) {
        $roster_shift = [];
        if (!empty($roster_list)) {
            foreach ($roster_list as $key => $weekData) {
                $week_number = ($key + 1);
                $dayCounter = 1;
                foreach ($weekData as $day => $multipleShift) {
                    foreach ($multipleShift as $val) {
                        if ($val->is_active && $val->in_funding) {
                            $val->start_time = date('Y-m-d H:i:s', strtotime($val->start_time));
                            $val->end_time = date('Y-m-d H:i:s', strtotime($val->end_time));

                            $roster_shift[$week_number][numberToDay($dayCounter)][] = $val;
                        }
                    }
                    $dayCounter++;
                }
            }
        }

        return $roster_shift;
    }

    public function make_shift_from_roster($objRos) {
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        $collapse_shift = $objRos->getCollapse_shift();
        $roster_list = $objRos->getRosterList();

        $roster_shift = $this->make_roster_list_formattable($roster_list);

        $archive_shiftId = [];
        if (!empty($collapse_shift)) {
            foreach ($collapse_shift as $val) {

                $create_shift = true;
                if (!empty($val->is_collapse)) {
                    if ($val->status == 2) {
                        $create_shift = true;

                        $x = array_column(obj_to_arr($val->old), 'id');
                        $archive_shiftId = array_merge($x, $archive_shiftId);
                    }
                }
            }
        }

        if (!empty($archive_shiftId)) {
            $objShift->archive_shift($archive_shiftId);
        }

        if (!empty($collapse_shift)) {
            foreach ($collapse_shift as $val) {

                $create_shift = true;
                if (!empty($val->is_collapse)) {
                    if ($val->status == 2) {
                        $create_shift = true;
                    } else {
                        $create_shift = false;
                    }
                }

                if ($create_shift) {

                    $shift_details = $val->new;

                    $week_number = $shift_details->week_number;
                    $day = $shift_details->day;
                    $index = $shift_details->index;

                    $ros_shift = $roster_shift[$week_number][$day][$index];

                    $shift_rosterId = $ros_shift->shift_rosterId;

                    $this->create_shift_of_roster($shift_rosterId, $shift_details->shift_date);
                }
            }
        }
    }

    function check_have_fund_for_shift($shift_rosterId, $shift_date) {
        $this->db->select("upli.id");
        $this->db->select("(select count(id) from  tbl_roster_shift_line_item_attached where roster_shiftId = attach_item.roster_shiftId) as count_attach_item");
        $this->db->from("tbl_roster_shift_line_item_attached as attach_item");
        $this->db->join("tbl_user_plan_line_items as upli", "upli.id = attach_item.plan_line_itemId AND upli.archive = 0");
        $this->db->join("tbl_user_plan as up", "up.id = upli.user_planId AND up.archive = 0");
        $this->db->join('tbl_finance_line_item as fli', 'fli.id = attach_item.line_item', 'INNER');
        $this->db->where("date('" . $shift_date . "') BETWEEN up.start_date AND up.end_date");
        $this->db->where("attach_item.archive", 0);
        $this->db->where("attach_item.roster_shiftId", $shift_rosterId);
        $this->db->where("upli.fund_remaining >= (attach_item.sub_total + round(((attach_item.sub_total * 10 )/100), 2))", null, false);
        $this->db->having("count(upli.id) = count_attach_item", null, false);

        $this->db->where('date("' . $shift_date . '") BETWEEN fli.start_date and fli.end_date', null, false);

        $items = $this->db->get()->row();
        if (!empty($items)) {
            return true;
        } else {
            return false;
        }
    }

    function create_shift_of_roster($shift_rosterId, $shift_date) {
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        $fund_status = $this->check_have_fund_for_shift($shift_rosterId, $shift_date);

        if ($fund_status) {
            $this->db->select(["r.booked_by", "r.userId", "created_type", "created_by"]);
            $this->db->select(["rs.start_time", "rs.end_time", "rs.preferred_memberId", "rs.allocate_pre_member", "rs.autofill_shift", "rs.push_to_app", "rs.shift_note", "rs.sub_total"]);
            $this->db->select(["rsc.confirm_with", "rsc.confirm_by", "rsc.confirm_userId", "rsc.firstname", "rsc.lastname", "rsc.email", "rsc.phone"]);

            $this->db->select("(select GROUP_CONCAT(concat(plan_line_itemId,'@SUB_BRKR@',line_item, '@SUB_BRKR@', quantity, '@SUB_BRKR@', cost, '@SUB_BRKR@', sub_total) SEPARATOR '@MAIN_BRKR@') from tbl_roster_shift_line_item_attached where roster_shiftId = rs.id AND archive = 0) as attach_line_item");

            $this->db->select("(select GROUP_CONCAT(concat(address,'@SUB_BRKR@',suburb, '@SUB_BRKR@', state, '@SUB_BRKR@', postal) SEPARATOR '@MAIN_BRKR@') from tbl_roster_shift_location where roster_shiftId = rs.id AND archive = 0) as shift_location");

            $this->db->select("(select GROUP_CONCAT(concat(timeId) SEPARATOR '@MAIN_BRKR@') from tbl_roster_shift_time_category where roster_shiftId = rs.id AND archive = 0) as time_shift_category");

            $this->db->select("(select GROUP_CONCAT(concat(requirementId,'@SUB_BRKR@',requirement_other) SEPARATOR '@MAIN_BRKR@') from tbl_roster_shift_requirement where roster_shiftId = rs.id AND requirement_type = 2) as shift_asistance");
            $this->db->select("(select GROUP_CONCAT(concat(requirementId,'@SUB_BRKR@',requirement_other) SEPARATOR '@MAIN_BRKR@') from tbl_roster_shift_requirement where roster_shiftId = rs.id AND requirement_type = 1) as shift_mobility");

            $this->db->from("tbl_roster_shift as rs");
            $this->db->join("tbl_roster as r", "r.id = rs.rosterId");
            $this->db->join("tbl_roster_shift_confirmation as rsc", "rsc.roster_shiftId = rs.id");
            $this->db->where("rs.id", $shift_rosterId);
            $this->db->where("rs.archive", 0);
            $this->db->group_by("rs.id");
            $shift = $this->db->get()->row();

            if (!empty($shift)) {
                $start_time = DateFormate((DateFormate($shift_date, "Y-m-d") . DateFormate($shift->start_time, "H:i:s")), "Y-m-d H:i:s");
                $end_time = DateFormate((DateFormate($shift_date, "Y-m-d") . DateFormate($shift->end_time, "H:i:s")), "Y-m-d H:i:s");

                $objShift->setBookedBy($shift->booked_by);
                $objShift->setShiftDate($shift_date);
                $objShift->setStartTime($start_time);
                $objShift->setEndTime($end_time);
                $objShift->setUserId($shift->userId);
                $objShift->setAllocatePreMember($shift->allocate_pre_member);
                $objShift->setAutofillShift($shift->autofill_shift);
                $objShift->setPushToApp($shift->push_to_app);
                $objShift->setStatus(1);

                $check_shift_exist = $objShift->check_shift_exist();

                if (!empty($check_shift_exist)) {
                    $shift_exist = array('status' => false, 'error' => 'Shift already exist.', 'shift_already_exist' => true, 'shifts' => $check_shift_exist);
                    return false;
//                    echo json_encode($shift_exist);
//                    exit();
                }

                $gst = calculate_gst($shift->sub_total);
                $shift_ary = array(
                    'roster_shiftId' => $shift_rosterId,
                    'booked_by' => $objShift->getBookedBy(),
                    'shift_date' => $objShift->getShiftDate(),
                    'start_time' => $objShift->getStartTime(),
                    'end_time' => $objShift->getEndTime(),
                    'allocate_pre_member' => $objShift->getAllocatePreMember(),
                    'push_to_app' => $objShift->getPushToApp(),
                    'autofill_shift' => $objShift->getAutofillShift(),
                    'is_quoted' => 0,
                    'status' => 1,
                    'funding_type' => 1,
                    'gst' => $gst,
                    'sub_total' => $shift->sub_total,
                    'price' => custom_round($shift->sub_total + $gst),
                    'created' => DATE_TIME,
                );

                // create shift
                $shiftId = $this->Basic_model->insert_records('shift', $shift_ary, $multiple = FALSE);
                $objShift->setShiftId($shiftId);

                // create log
                $this->loges->setLogType('shift');
                $this->loges->setTitle('New shift created : Shift Id ' . $shiftId);
                $this->loges->setUserId($shiftId);
                $this->loges->setDescription(json_encode($shift));
                $this->loges->createLog();

                // add participant in shift
                $this->add_participant_in_shift($shift->userId, $shiftId);

                // add address in shift
                $this->add_address_in_shift($shift->shift_location, $shiftId);

                // add category in shift
                $this->add_shift_category_in_shift($shift->time_shift_category, $shiftId);

                // attach line item in shift
                $this->attach_line_item_in_shift($shift->attach_line_item, $shiftId);

                // add confirmation details in shift
                $this->add_shift_confirmation_details($shift, $shiftId);

                // add caller in shift
                $this->add_caller_in_shift($shift, $shiftId);

                // add shift requirement in shift
                $this->add_shift_requirement_in_shift($shift, $shiftId);

                // if have preferred member then add in shift
                $this->make_shift_preferred_member_and_allocate($shift, $shiftId);

                $adminId = ($shift->created_type == 1) ? $shift->created_by : 0;
                $this->Schedule_model->add_shift_note_in_shift($shift->shift_note, $shiftId, $adminId);

                // auto fill shift is yes then make it auto fill now
                if ($objShift->getAutofillShift() == 1) {
                    $this->load->model("schedule/Schedule_model");
                    $this->Schedule_model->make_auto_fill_shift($shiftId);
                }

                // send notification to booker
                $objShift->send_notification_to_booked_for();

                if ((int) $objShift->getPushToApp() === 1) {
                    #Send notification to all member near 40 KM of shift
                    #when shift move to app
                    $this->Listing_model->send_notification_to_all_member(array($shiftId));
                }

                // send mail to participant
                $objShift->shiftCreateMail();
            }
        }
    }

    function add_caller_in_shift($shift, $shiftId) {
        $shift_caller = array(
            'shiftId' => $shiftId,
            'firstname' => $shift->firstname,
            'lastname' => $shift->lastname ?? '',
            'email' => $shift->email,
            'phone' => $shift->phone,
            'booker_id' => $shift->confirm_userId ?? '',
            'booking_method' => $shift->confirm_by ?? ''
        );
        // insert caller data
        $this->Basic_model->insert_records('shift_caller', $shift_caller, $multiple = FALSE);
    }

    function add_shift_confirmation_details($shift, $shiftId) {
        $shift_confirmation = array(
            'shiftId' => $shiftId,
            'confirm_with' => $shift->confirm_with ?? '',
            'confirm_userId' => $shift->confirm_userId ?? 0,
            'confirm_by' => $shift->confirm_by,
            'firstname' => $shift->firstname,
            'lastname' => $shift->lastname,
            'email' => $shift->email,
            'phone' => $shift->phone,
        );

        // insert confirmation details
        $this->Basic_model->insert_records('shift_confirmation', $shift_confirmation, $multiple = FALSE);
    }

    function make_shift_preferred_member_and_allocate($shift, $shiftId) {
        $this->load->library('UserName');
        if (!empty($shift->preferred_memberId)) {

            $shift_member = array('shiftId' => $shiftId, 'memberId' => $shift->preferred_memberId);
            $allocated_member = array('shiftId' => $shiftId, 'memberId' => $shift->preferred_memberId, 'status' => 1, 'created' => DATE_TIME);

            if (!empty($shift_member)) {
                $this->Basic_model->insert_records('shift_preferred_member', $shift_member, $multiple = false);
            }

            if ($shift->allocate_pre_member == 1 && !empty($allocated_member)) {
                $this->load->model("schedule/Schedule_model");
                $this->Schedule_model->move_shift_to_unconfirmed($shiftId);
                $this->Basic_model->insert_records('shift_member', $allocated_member, $multiple = false);

                // create log
                $this->loges->setLogType('shift');

                $user_name = $this->username->getName('member', $shift->preferred_memberId);
                $this->loges->setTitle('Allocated shift to preferred member ' . $user_name . ' : Shift Id ' . $shiftId);
                $this->loges->setUserId($shiftId);
                $this->loges->setDescription(json_encode($shift));
                $this->loges->createLog();
            }
        }
    }

    function add_address_in_shift($location_string, $shiftId) {
        $multiple_str = explode("@MAIN_BRKR@", $location_string);

        if (!empty($multiple_str)) {
            $shift_location = [];
            foreach ($multiple_str as $single_loc_s) {
                $x = explode("@SUB_BRKR@", $single_loc_s);

                $address = [
                    'street' => $x[0] ?? '',
                    'suburb' => $x[1] ?? '',
                    'state' => $x[2] ?? '',
                    'postal' => $x[3] ?? '',
                ];

                $shift_location[] = (object) $address;
            }

            if (!empty($shift_location)) {
                $this->load->model("Schedule_model");
                $this->Schedule_model->make_shift_address($shift_location, $shiftId);
            }
        }
    }

    function add_shift_category_in_shift($category_string, $shiftId) {
        $multiple_str = explode("@MAIN_BRKR@", $category_string);

        if (!empty($multiple_str)) {
            foreacH ($multiple_str as $timeId) {
                $data[] = [
                    'shiftId' => $shiftId,
                    'timeId' => $timeId,
                    'created' => DATE_TIME,
                    'archive' => 0,
                ];
            }

            if (!empty($data)) {
                $this->basic_model->insert_records('shift_time_category', $data, TRUE);
            }
        }
    }

    function attach_line_item_in_shift($item_string, $shiftId) {
        require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';
        $multiple_str = explode("@MAIN_BRKR@", $item_string);

        if (!empty($multiple_str)) {
            foreach ($multiple_str as $single_s) {
                $x = explode("@SUB_BRKR@", $single_s);

                $plan_line_itemId = $x[0] ?? '';
                $qty = $x[2] ?? '';
                $item_cost = $x[3] ?? '';
                $item_sub_total = $x[4] ?? '';
                $item_gst = calculate_gst($item_sub_total);
                $item_total = custom_round($item_sub_total + $item_gst);

                $attach_line_item = array(
                    'shiftId' => $shiftId,
                    'plan_line_itemId' => $plan_line_itemId,
                    'line_item' => $x[1] ?? '',
                    'quantity' => $qty,
                    'cost' => $item_cost,
                    'sub_total' => $item_sub_total,
                    'gst' => $item_gst,
                    'total' => $item_total,
                    'xero_line_item_id' => '',
                    'created' => DATE_TIME,
                    'updated' => DATE_TIME,
                    'archive' => 0,
                );

                $attachId = $this->basic_model->insert_records('shift_line_item_attached', $attach_line_item, false);

                // create line item transaction history
                $objTran = new LineItemTransactionHistory();

                $objTran->setLine_item_fund_used($item_total);
                $objTran->setUser_plan_line_items_id($plan_line_itemId);
                $objTran->setLine_item_fund_used_type(1);
                $objTran->setLine_item_use_id($attachId);
                $objTran->setStatus(0);
                $objTran->setArchive(0);

                $objTran->create_history();
            }
        }

        // create new object line item transaction history
        $objTran = new LineItemTransactionHistory();
        $objTran->update_fund_blocked();
    }

    function add_participant_in_shift($participantId, $shiftId) {
        $data = [
            'participantId' => $participantId,
            'shiftId' => $shiftId,
            'status' => 1,
            'created' => DATE_TIME,
        ];

        $this->Basic_model->insert_records('shift_participant', $data, $multiple = false);
    }

    function add_shift_requirement_in_shift($shift, $shiftId) {
        $asistance = $shift->shift_asistance ? explode("@MAIN_BRKR@", $shift->shift_asistance) : [];
        $mobility = $shift->shift_mobility ? explode("@MAIN_BRKR@", $shift->shift_mobility) : [];

        $shift_requirement = [];
        if (!empty($asistance)) {
            foreach ($asistance as $val) {

                $x = explode("@SUB_BRKR@", $val);

                $shift_requirement[] = [
                    "shiftId" => $shiftId,
                    "requirementId" => $x[0],
                    "requirement_other" => $x[1] ?? '',
                    "requirement_type" => 2,
                ];
            }
        }

        if (!empty($mobility)) {
            foreach ($mobility as $val) {
                $x = explode("@SUB_BRKR@", $val);

                $shift_requirement[] = [
                    "shiftId" => $shiftId,
                    "requirementId" => $x[0],
                    "requirement_other" => $x[1] ?? '',
                    "requirement_type" => 1,
                ];
            }
        }

        if (!empty($shift_requirement)) {
            $this->basic_model->insert_records("shift_requirements", $shift_requirement, $multiple = true);
        }
    }

}
