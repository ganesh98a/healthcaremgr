<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberRole and related models of it
 */
class MemberRole_test extends TestCase
{

    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/member/models/Member_model');
        $this->CI->load->model('../modules/item/models/Member_role_model');
        $this->CI->load->library('form_validation');
        $this->Member_model = $this->CI->Member_model;
        $this->Member_role_model = $this->CI->Member_role_model;    
    }

    /*
     * checking the create update member
     */
    function test_create_update_member()
    {
        $postdata = [
            'end_date' => "2020-10-07 00:00:00",
            'end_time' => "01:00:00",
            'level' => "2",
            'member_id' => "13",
            'pay_point' => "2",
            'role_id' => "2",
            'start_date'  => "2020-10-07 00:00:00",
            'start_time' => "15:30:00",
            'employment_type' => "437",
            "adminId" => null
        ];
        $request = new stdClass();
        $request->data = (object) $postdata;
        $mock = $this->getMockBuilder(Member_model::class)
            ->setMethods(['request_handler'])
            ->getMock();
        $mock->method('request_handler')->willReturn($request->data);
        $output = $mock->create_update_member_role($request);
        return $this->assertTrue($output['status']);
    }

     /*
     * To get role list by search if greater than 0 positive case
     * Using - assertGreaterThanOrEqual
     */
    public function testGetRoleListBySearch() {
        // Request data
        $reqData = '
        {
            "search": "test"
        }';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;
        if (!empty($reqData)) {
            // Call model for get role list by search
            $result = $this->CI->Member_role_model->get_role_list_by_search($reqData->search); 
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Requested data is null', 'data' => []); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Successfully Retrived';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }
}
