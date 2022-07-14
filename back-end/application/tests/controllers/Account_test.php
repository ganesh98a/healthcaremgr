<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Account and related models of it
 */
class Account_test extends TestCase {

    protected $CI;
    protected $postdata = [
        "account_name" => "UT Parent Org",
        "abn" => "",
        "archive" => 0,
        "shipping_address" => "15 William Street, Melbourne VIC 3000",
        "dhhs" => "1",
        "fax" => "123456",
        "is_site" => "0",
        "org_type" => "",
        "otherName" => "",
        "parent_org" => null,
        "payable_email" => "pranavgajjar@gmail.com",
        "payable_phone" => "1212121212",
        "phone" => "0404441917",
        "role_id" => 22,
        "status" => 1,
        "website" => ""
    ];

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Account_model');
        $this->CI->load->model('item/Member_role_model');
        $this->CI->load->library('form_validation');
        $this->Account_model = $this->CI->Account_model;
        $this->basic_model = $this->CI->basic_model;
        $this->Member_role_model = $this->CI->Member_role_model;
    }

    /*
     * checking the mandatory service type
     */
    function test_create_organisation_parent_val1() {
        $postdata = $this->postdata;
        $postdata['role_id'] = null;
        $output = $this->Account_model->create_organisation($postdata, 1, false);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the mandatory shipping address
     */
    function test_create_organisation_parent_val2() {
        $result = $this->Member_role_model->get_role_list_by_search();
        $postdata = $this->postdata;
        $postdata['role_id'] = $result[0]['value'];
        $postdata['shipping_address'] = null;
        $output = $this->Account_model->create_organisation($postdata, 1, false);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the parent org insertion
     */
    function test_create_organisation_parent_suc1() {
        $result = $this->Member_role_model->get_role_list_by_search();
        $postdata = $this->postdata;
        $postdata['role_id'] = $result[0]['value'];
        $output = $this->Account_model->create_organisation($postdata, 1, false);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the parent org updation
     */
    function test_create_organisation_parent_suc2() {
        $result = $this->Member_role_model->get_role_list_by_search();
        $postdata = $this->postdata;

        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        $postdata['role_id'] = $result[0]['value'];
        $postdata['account_name'] = "UT Parent Org - Updated";
        $output = $this->Account_model->create_organisation($postdata, 1, false);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing add/edit parent org contacts, required primary contact
     */
    function test_save_account_contact_roles_val1() {

        $roles_result = $this->Account_model->get_account_roles(2);
        $roles_data = $roles_result['data'];

        $postdata['account_type'] = 2;
        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['account_id'] = $details->last_id;

        $contact_res = $this->basic_model->get_row('person', ["id", "concat_ws(' ',firstname,lastname) as contact_name"], ["id <=" => "10"]);

        $account_contacts[] = [
            "id" => null,
            "contact" => ['label' => $contact_res->contact_name, "value" => $contact_res->id],
            "is_primary" => 0,
            "role_id" => $roles_data[0]['value'],
        ];
        $postdata['account_contacts'] = $account_contacts;
        $output = $this->Account_model->save_account_contact_roles($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * testing add/edit parent org contacts, required contact
     */
    function test_save_account_contact_roles_val2() {

        $roles_result = $this->Account_model->get_account_roles(2);
        $roles_data = $roles_result['data'];

        $postdata['account_type'] = 2;
        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['account_id'] = $details->last_id;

        $contact_res = $this->basic_model->get_row('person', ["id", "concat_ws(' ',firstname,lastname) as contact_name"], ["username" => "Pranav.Gajjar@ampion.com.au"]);

        $account_contacts[] = [
            "id" => null,
            "contact" => null,
            "is_primary" => 1,
            "role_id" => $roles_data[0]['value'],
        ];
        $postdata['account_contacts'] = $account_contacts;
        $output = $this->Account_model->save_account_contact_roles($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * testing add/edit parent org contacts, successful addition
     */
    function test_save_account_contact_roles_suc1() {

        $roles_result = $this->Account_model->get_account_roles(2);
        $roles_data = $roles_result['data'];

        $postdata['account_type'] = 2;
        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['account_id'] = $details->last_id;

        $contact_res = $this->basic_model->get_row('person', ["id", "concat_ws(' ',firstname,lastname) as contact_name"], ["id <=" => 10]);

        $account_contacts[] = [
            "id" => null,
            "contact" => ['label' => $contact_res->contact_name, "value" => $contact_res->id],
            "is_primary" => 1,
            "role_id" => $roles_data[0]['value'],
        ];
        $postdata['account_contacts'] = $account_contacts;
        $output = $this->Account_model->save_account_contact_roles($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing add/edit parent org contacts, successful deletion
     */
    function test_archive_account_contact_suc1() {

        $fetch_arr['account_type'] = 2;
        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $fetch_arr['id'] = $details->last_id;

        $account_contacts_res = $this->Account_model->get_account_contacts_list((object) $fetch_arr);
        $postdata = $account_contacts_res['data'][0];
        $output = $this->Account_model->archive_account_contact($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the sub org addition
     */
    function test_create_organisation_suborg_suc1() {
        $result = $this->Member_role_model->get_role_list_by_search();
        $postdata = $this->postdata;

        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['parent_org']['value'] = $details->last_id;
        $postdata['role_id'] = $result[0]['value'];
        $postdata['account_name'] = "UT Sub Org";
        $output = $this->Account_model->create_organisation($postdata, 1, false);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the site addition
     */
    function test_create_organisation_site_suc1() {
        $result = $this->Member_role_model->get_role_list_by_search();
        $postdata = $this->postdata;

        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"), ["parent_org" => 0]);
        if($details->last_id)
            $postdata['parent_org']['value'] = $details->last_id;
        $postdata['role_id'] = $result[0]['value'];
        $postdata['account_name'] = "UT Site";
        $postdata['is_site'] = 1;
        $output = $this->Account_model->create_organisation($postdata, 1, false);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the site updation
     */
    function test_create_organisation_site_suc2() {
        $result = $this->Member_role_model->get_role_list_by_search();
        $postdata = $this->postdata;

        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"), ["parent_org" => 0]);
        if($details->last_id)
            $postdata['parent_org']['value'] = $details->last_id;
        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        $postdata['role_id'] = $result[0]['value'];
        $postdata['account_name'] = "UT Site - Updated";
        $postdata['is_site'] = 1;
        $output = $this->Account_model->create_organisation($postdata, 1, false);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the site deletion
     */
    function test_create_organisation_site_del1() {
        $id = null;
        $details = $this->basic_model->get_row('organisation', array("MAX(id) AS last_id"));
        if($details->last_id)
            $id = $details->last_id;
        $output = $this->Account_model->archive_account($id, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the mandatory organisation id while adding a new org member
     */
    function test_create_update_org_member_val1() {
        $postdata =[
            "org_id" => null,
            "member_id" => 2,
            "reg_date" => null,
            "ref_no" => null
        ];
        $output = $this->Account_model->create_update_org_member($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the mandatory member id while adding a new org member
     */
    function test_create_update_org_member_val2() {
        $postdata =[
            "org_id" => 1,
            "member_id" => null,
            "reg_date" => null,
            "ref_no" => null
        ];
        $output = $this->Account_model->create_update_org_member($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the registration date in correct format
     */
    function test_create_update_org_member_val3() {
        $postdata =[
            "org_id" => 1,
            "member_id" => 2,
            "reg_date" => "2002220-12-12",
            "ref_no" => null
        ];
        $output = $this->Account_model->create_update_org_member($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the successful insertion of organisation member
     */
    function test_create_update_org_member_insert() {
        $postdata =[
            "org_id" => 1,
            "member_id" => 2,
            "reg_date" => "2019-12-12",
            "ref_no" => "UT"
        ];
        $output = $this->Account_model->create_update_org_member($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful updating of organisation member
     */
    function test_create_update_org_member_update() {
        $postdata = null;
        $details = $this->basic_model->get_row('organisation_member', array("MAX(id) AS lastid"));
        if($details->lastid)
        $postdata['id'] = $details->lastid;

        $postdata["org_id"] = 1;
        $postdata["member_id"] = 2;
        $postdata["reg_date"] = null;
        $postdata["ref_no"] = null;
        $postdata["status"] = null;

        $output = $this->Account_model->create_update_org_member($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful archiving of organisation member
     */
    function test_archive_org_member() {
        $postdata = null;
        $details = $this->basic_model->get_row('organisation_member', array("MAX(id) AS lastid"));
        if($details->lastid)
        $postdata['id'] = $details->lastid;

        $output = $this->Account_model->archive_organisation_member($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful of organisation service type refrence data
     */
    function test_get_organization_service_type() {
        $output = $this->Account_model->get_organization_service_type();
        if (!empty($output)) {
            return $this->assertGreaterThanOrEqual(0, $output);
        }
    }
}
