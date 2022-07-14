<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller AdminDashboard and related models of it
 */
class AdminDashboard_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/admin/models/Admin_model');
        $this->CI->load->library('form_validation');
        $this->Admin_model = $this->CI->Admin_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /**
     * validating the required fields in creating access role
     */
    function test_create_update_access_role_val1() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['name'] = null;

        $module_rows = $this->Admin_model->get_module_objects(null);
        if($module_rows['status'] == true)
            $postdata['module_objects_list'] = $module_rows['data'];

        $output = $this->Admin_model->create_update_access_role($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating the required fields in creating access role
     */
    function test_create_update_access_role_val2() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['name'] = "test1";

        $module_rows = $this->Admin_model->get_module_objects(null);
        if($module_rows['status'] == true)
            $postdata['module_objects_list'] = $module_rows['data'];

        $output = $this->Admin_model->create_update_access_role($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * testing successful creation of access role
     */
    function test_create_update_access_role_success1() {
        $postdata = null;
        $postdata['id'] = null;
        $postdata['name'] = "test1";

        $module_rows = $this->Admin_model->get_module_objects(null);
        if($module_rows['status'] == true) {
            $module_objects_list = $module_rows['data'];
            for($i=0;$i<count($module_objects_list);$i++) {
                # ticking all read permission of module objects
                $module_objects_list[$i]['read_checked'] = 1;
            }
        }
        $postdata['module_objects_list'] = $module_objects_list;

        $output = $this->Admin_model->create_update_access_role($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful updating of access role
     */
    function test_create_update_access_role_success2() {
        $postdata = null;
        $postdata['id'] = null;
        $details = $this->basic_model->get_row('access_role', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        $postdata['name'] = "test1_updated";

        $module_rows = $this->Admin_model->get_module_objects(null);
        if($module_rows['status'] == true) {
            $module_objects_list = $module_rows['data'];
            for($i=0;$i<count($module_objects_list);$i++) {
                # ticking all read and create permission of module objects
                $module_objects_list[$i]['read_checked'] = 1;
                $module_objects_list[$i]['create_checked'] = 1;
            }
        }
        $postdata['module_objects_list'] = $module_objects_list;

        $output = $this->Admin_model->create_update_access_role($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful deletion of access role
     */
    function test_archive_access_role_success1() {
        $postdata = null;
        $postdata['id'] = null;
        $details = $this->basic_model->get_row('access_role', array("MAX(id) AS last_id"));
        if($details->last_id)
        $postdata['id'] = $details->last_id;

        $output = $this->Admin_model->archive_access_role($postdata, 1);
        return $this->assertTrue($output['status']);
    }
}
