<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller FinanceDashboard and related models of it
 */
class FinanceDashboard_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/finance/models/Finance_model');
        $this->CI->load->model('../modules/schedule/models/Schedule_model');
        $this->CI->load->library('form_validation');
        $this->Finance_model = $this->CI->Finance_model;
        $this->Schedule_model = $this->CI->Schedule_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /**
     * validating adding new timesheet failure
     * should not create a timesheet record
     */
    function test_create_update_timesheet_val1() {

        $output = $this->Finance_model->get_next_timesheet_no();
        if($output['data'])
        $postdata['timesheet_no'] = $output['data'];

        $postdata['shift_id'] = 198;
        $postdata["member_id"] = null;
        $postdata["status"] = 1;
        $postdata["amount"] = 0.00;
        $output = $this->Finance_model->create_update_timesheet($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating adding new timesheet failure
     * should not create a timesheet record
     */
    function test_create_update_timesheet_val2() {

        $output = $this->Finance_model->get_next_timesheet_no();
        if($output['data'])
        $postdata['timesheet_no'] = $output['data'];

        $postdata['shift_id'] = 198;
        $postdata["member_id"] = 23;
        $postdata["status"] = 0;
        $postdata["amount"] = null;
        $output = $this->Finance_model->create_update_timesheet($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating when shift is changed to other status then completed
     * should not create a timesheet record
     */
    function test_shift_completion_val1() {

        $prev_total = $cur_total = null;
        $details = $this->basic_model->get_row('finance_timesheet', array("count(id) AS total"));
        if($details->total)
        $prev_total = $details->total;

        $postdata['id'] = 198;
        $postdata["status"] = 3;
        $output = $this->Schedule_model->update_shift_status($postdata, 1);
        
        $details = $this->basic_model->get_row('finance_timesheet', array("count(id) AS total"));
        if($details->total)
        $cur_total = $details->total;

        $test_pass = ($prev_total == $cur_total) ? true : false;
        return $this->assertTrue($test_pass);
    }

    /**
     * validating when shift is changed to completed
     * there should be a timesheet record
     */
    function test_shift_completion_suc1() {
        $postdata['id'] = 770;
        $postdata["status"] = 5;
        $output = $this->Schedule_model->update_shift_status($postdata, 1);
        
        $ts_details = $this->Finance_model->get_timesheet_details(null, 770);
        
        $test_pass = (isset($ts_details['data']) && !empty($ts_details['data']->id)) ? true : false;
        return $this->assertTrue($test_pass);
    }

    /**
     * validating a timesheet's line items addition/updating
     * units should be numeric
     */
    function test_timesheet_line_items_update_fal1() {
        $ts_details_res = $this->Finance_model->get_timesheet_details(null, 770);
        if (!$ts_details_res['status']) {
            return $this->assertFalse(1);
        }
        $ts_details = object_to_array($ts_details_res['data']);

        $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "timesheet_id" => $ts_details['id']];
        $line_items_res = $this->Finance_model->get_timesheet_line_items_list((object) $reqData);
        $line_items = object_to_array($line_items_res['data']);
        $line_items[0]['units'] = "asda";

        $postdata['timesheet_id'] = $ts_details['id'];
        $postdata['shift_id'] = $ts_details['shift_id'];
        $postdata['timesheet_line_items'] = $line_items;
        $output = $this->Finance_model->add_update_timesheet_line_items($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating a timesheet's line items addition/updating
     * units should be units
     */
    function test_timesheet_line_items_update_fal2() {
        $ts_details_res = $this->Finance_model->get_timesheet_details(null, 770);
        if (!$ts_details_res['status']) {
            return $this->assertFalse(1);
        }
        $ts_details = object_to_array($ts_details_res['data']);

        $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "timesheet_id" => $ts_details['id']];
        $line_items_res = $this->Finance_model->get_timesheet_line_items_list((object) $reqData);
        $line_items = object_to_array($line_items_res['data']);
        $line_items[] = $line_items[0];

        $postdata['timesheet_id'] = $ts_details['id'];
        $postdata['shift_id'] = $ts_details['shift_id'];
        $postdata['timesheet_line_items'] = $line_items;
        $output = $this->Finance_model->add_update_timesheet_line_items($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating a timesheet's line items addition/updating
     */
    function test_timesheet_line_items_update_suc1() {
        $ts_details_res = $this->Finance_model->get_timesheet_details(null, 770);
        if (empty($ts_details_res['status'])) {
            return $this->assertTrue(0);
        }
        $ts_details = object_to_array($ts_details_res['data']);

        $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "timesheet_id" => $ts_details['id']];
        $line_items_res = $this->Finance_model->get_timesheet_line_items_list((object) $reqData);
        $line_items = object_to_array($line_items_res['data']);
        $line_items[0]['units'] = "20";
        $line_items[0]['unit_rate'] = "1.00";

        $postdata['timesheet_id'] = $ts_details['id'];
        $postdata['shift_id'] = $ts_details['shift_id'];
        $postdata['timesheet_line_items'] = $line_items;
        $output = $this->Finance_model->add_update_timesheet_line_items($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating successful archiving of timesheet line item
     */
    function test_timesheet_line_items_archive_suc1() {
        $ts_details_res = $this->Finance_model->get_timesheet_details(null, 770);
        if (empty($ts_details_res['status'])) {
            return $this->assertTrue(0);
        }
        $ts_details = object_to_array($ts_details_res['data']);        
        $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "timesheet_id" => $ts_details['id']];
        $line_items_res = $this->Finance_model->get_timesheet_line_items_list((object) $reqData);
        $line_items = object_to_array($line_items_res['data']);

        $postdata['id'] = $line_items[0]['id'];
        $output = $this->Finance_model->archive_timesheet_line_item($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating when shift is changed to completed
     * there should be a timesheet record
     */
    function test_update_timesheet_status_suc1() {
        $ts_details = $this->Finance_model->get_timesheet_details(null, 770);
        $timesheet_id = null;
        if(isset($ts_details['data']) && !empty($ts_details['data']->id))
            $timesheet_id = $ts_details['data']->id;
        
        $postdata["id"] = $timesheet_id;
        $postdata["status"] = 4; # paid
        $output = $this->Finance_model->update_timesheet_status($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating successful submission of timesheet line items into keypay
     * there should be a timesheet record
     */
    function test_create_keypay_timesheet_suc1() {
        $ts_details = $this->Finance_model->get_timesheet_details(null, 770);
        if (empty($ts_details['status'])) {
            $this->assertTrue(0);
        }
        $timesheet_id = null;
        if(isset($ts_details['data']) && !empty($ts_details['data']->id))
            $timesheet_id = $ts_details['data']->id;
        
        $output = $this->Finance_model->create_bulk_keypay_timesheet([$timesheet_id], 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating the required fields in creating pay rate
     * total of 9 fields are required
     */
    function test_create_update_pay_rate_val1() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['amount'] = null;
        $postdata['description'] = "some desc";
        $postdata['employment_type_id'] = null;
        $postdata['pay_level_id'] = null;
        $postdata['pay_rate_award_id'] = null;
        $postdata['pay_rate_category_id'] = null;
        $postdata['role_id'] = null;
        $postdata['skill_level_id'] = null;
        $postdata['start_date'] = null;
        $postdata['end_date'] = null;

        $output = $this->Finance_model->create_update_pay_rate($postdata, 1);
        return $this->assertContains("The Category field is required., The Award field is required., The Work Type field is required., The Pay Level field is required., The Skill Level field is required., The Employment Type field is required., The Start Date field is required., The End Date field is required., The Amount field is required", $output['error']);
    }

    /**
     * validating the correct format of start & end dates and amount
     */
    function test_create_update_pay_rate_val2() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['amount'] = "asdas.asd";
        $postdata['description'] = "some desc";
        $postdata['employment_type_id'] = 395;
        $postdata['pay_level_id'] = 399;
        $postdata['pay_rate_award_id'] = 392;
        $postdata['pay_rate_category_id'] = 389;
        $postdata['role_id'] = 3;
        $postdata['skill_level_id'] = 433;
        $postdata['start_date'] = "adasd";
        $postdata['end_date'] = "asdasdas";

        $output = $this->Finance_model->create_update_pay_rate($postdata, 1);
        return $this->assertContains("Incorrect Start Date, Incorrect End Date, The Amount field must contain only numbers.", $output['error']);
    }

    /**
     * testing successful creation of pay rate
     */
    function test_create_update_pay_rate_success1() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['amount'] = "450.00";
        $postdata['description'] = "some desc";
        $postdata['employment_type_id'] = 423;
        $postdata['pay_level_id'] = 430;
        $postdata['pay_rate_award_id'] = 420;
        $postdata['pay_rate_category_id'] = 411;
        $postdata['role_id'] = 3;
        $postdata['skill_level_id'] = 433;
        $postdata['start_date'] = "2021-02-01";
        $postdata['end_date'] = "2021-03-01";
        $postdata['status'] = "1";
        $postdata['external_reference'] = "keypay_default3";

        $output = $this->Finance_model->create_update_pay_rate($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful updating of pay rate
     */
    function test_create_update_pay_rate_success2() {
        $postdata = null;
        $postdata['id'] = null;
        $details = $this->basic_model->get_row('finance_pay_rate', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        
        $postdata['amount'] = "450.00";
        $postdata['description'] = "updated desc";
        $postdata['employment_type_id'] = 423;
        $postdata['pay_level_id'] = 430;
        $postdata['pay_rate_award_id'] = 420;
        $postdata['pay_rate_category_id'] = 411;
        $postdata['role_id'] = 3;
        $postdata['skill_level_id'] = 433;
        $postdata['start_date'] = "2021-02-01";
        $postdata['end_date'] = "2021-03-01";
        $postdata['status'] = "1";
        $postdata['external_reference'] = "keypay_default3";

        $output = $this->Finance_model->create_update_pay_rate($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful deletion of pay rate
     */
    function test_archive_pay_rate_success1() {
        $postdata = null;
        $postdata['id'] = null;
        $details = $this->basic_model->get_row('finance_pay_rate', array("MAX(id) AS last_id"));
        if($details->last_id)
        $postdata['id'] = $details->last_id;

        $output = $this->Finance_model->archive_pay_rate($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating the required fields in creating charge rate
     * total of 9 fields are required
     */
    function test_create_update_charge_rate_val1() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['amount'] = null;
        $postdata['description'] = "some desc";
        $postdata['cost_book_id'] = null;
        $postdata['pay_level_id'] = null;
        $postdata['charge_rate_category_id'] = null;
        $postdata['role_id'] = null;
        $postdata['skill_level_id'] = null;
        $postdata['start_date'] = null;
        $postdata['end_date'] = null;

        $output = $this->Finance_model->create_update_charge_rate($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating the correct format of start & end dates and amount
     */
    function test_create_update_charge_rate_val2() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['amount'] = "asdas.asd";
        $postdata['description'] = "some desc";
        $postdata['cost_book_id'] = 454;
        $postdata['pay_level_id'] = 399;
        $postdata['charge_rate_category_id'] = 389;
        $postdata['role_id'] = 3;
        $postdata['skill_level_id'] = 433;
        $postdata['start_date'] = "adasd";
        $postdata['end_date'] = "asdasdas";

        $output = $this->Finance_model->create_update_charge_rate($postdata, 1);
        return $this->assertContains("Incorrect Start Date, Incorrect End Date, The Amount field must contain only numbers.", $output['error']);
    }

    /**
     * testing successful creation of charge rate
     */
    function test_create_update_charge_rate_success1() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['amount'] = "450.00";
        $postdata['description'] = "some desc";
        $postdata['cost_book_id'] = 454;
        $postdata['pay_level_id'] = 430;
        $postdata['charge_rate_category_id'] = 411;
        $postdata['role_id'] = 3;
        $postdata['skill_level_id'] = 433;
        $postdata['start_date'] = "2021-02-01";
        $postdata['end_date'] = "2021-03-01";
        $postdata['status'] = "1";
        $postdata['external_reference'] = "EXT1";

        $output = $this->Finance_model->create_update_charge_rate($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful updating of charge rate
     */
    function test_create_update_charge_rate_success2() {
        $postdata = null;
        $postdata['id'] = null;
        $details = $this->basic_model->get_row('finance_charge_rate', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        
        $postdata['amount'] = "450.00";
        $postdata['description'] = "updated desc";
        $postdata['cost_book_id'] = 454;
        $postdata['pay_level_id'] = 430;
        $postdata['charge_rate_category_id'] = 411;
        $postdata['role_id'] = 3;
        $postdata['skill_level_id'] = 433;
        $postdata['start_date'] = "2021-02-01";
        $postdata['end_date'] = "2021-03-01";
        $postdata['status'] = "1";
        $postdata['external_reference'] = "EXT1";

        $output = $this->Finance_model->create_update_charge_rate($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful deletion of charge rate
     */
    function test_archive_charge_rate_success1() {
        $postdata = null;
        $postdata['id'] = null;
        $details = $this->basic_model->get_row('finance_charge_rate', array("MAX(id) AS last_id"));
        if($details->last_id)
        $postdata['id'] = $details->last_id;

        $output = $this->Finance_model->archive_charge_rate($postdata, 1);
        return $this->assertTrue($output['status']);
    }
}
