<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Create note activity for shift and related models of it
 */

require_once './_ci_phpunit_test/autoloader.php';

use PHPUnit\Framework\TestCase;

class ShiftCreateNoteActivity_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Contact_model');
        $this->CI->load->library('form_validation');
        $this->Contact_model = $this->CI->Contact_model;
        $this->basic_model = $this->CI->basic_model;
        $this->CI->db->trans_start(true);
    }

	public function tearDown(): void { 
        $this->CI->db->trans_complete(); 
    }

    /*
     * create note activity for shift
     */
    function test_create_notes_activity_for_shift(){
        $adminId = '20';

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_id"));
        if($details->last_id)
        $salesId = $details->last_id;

        $postdata = '{
            "title" : "Test notes",
            "sales_type" : "shift",
            "salesId" : '.$salesId.',
            "related_type" : "",
            "related_to" : "pranav",
            "description" : "Add notes for shift"
            }';
       
        $postdata = json_decode($postdata);
        $output = $this->Contact_model->create_note_for_activity($postdata,$adminId);

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Note added successfully'];
        }
        return $this->assertTrue($status['status']);
    }
}