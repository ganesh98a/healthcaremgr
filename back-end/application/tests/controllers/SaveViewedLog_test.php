<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Account and related models of it
 */
class SaveViewedLog_test extends TestCase {

    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->library('form_validation');
        $this->basic_model = $this->CI->basic_model;
    }

    /*
     * checking the save viewed log - success
     */
    function test_save_viewed_log() {
        $postdata = [
            "entity_type" => 'application',
            "entity_id" => 2,
            "adminId" => 20,
            "ref_no" => null
        ];
        $adminId = 20;
        $reqData = (object) $postdata;
        if (!empty($reqData)) {

            $entity_type = $reqData->entity_type;
            $entity_id = $reqData->entity_id;

            $this->CI->load->file(APPPATH.'Classes/common/ViewedLog.php');
            $viewedLog = new ViewedLog();
            // get entity type value
            $entity_type = $viewedLog->getEntityTypeValue($entity_type);

            // Set data
            $viewedLog->setEntityType($entity_type);
            $viewedLog->setEntityId($entity_id);
            $viewedLog->setViewedDate(DATE_TIME);
            $viewedLog->setViewedBy($adminId);

            // Create log
            $viewedLog = $viewedLog->createViewedLog();

            if ($viewedLog) {
                $reqData = $reqData;

                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $this->assertTrue($result);
    }

    /*
     * checking the save viewed log - failur
     */
    function test_save_viewed_log_case2() {
        $postdata = [];
        $adminId = 20;

        if (!empty($reqData)) {
            $reqData = (object) $postdata;
            $entity_type = $reqData->entity_type;
            $entity_id = $reqData->entity_id;

            $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
            $viewedLog = new ViewedLog();
            // get entity type value
            $entity_type = $viewedLog->getEntityTypeValue($entity_type);

            // Set data
            $viewedLog->setEntityType($entity_type);
            $viewedLog->setEntityId($entity_id);
            $viewedLog->setViewedDate(DATE_TIME);
            $viewedLog->setViewedBy($adminId);

            // Create log
            $viewedLog = $viewedLog->createViewedLog();

            if ($viewedLog) {
                $reqData = $reqData;

                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $this->assertFalse($result);
    }
}