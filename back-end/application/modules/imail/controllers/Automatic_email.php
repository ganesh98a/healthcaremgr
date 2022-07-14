<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * @property-read \Automatic_email_model $Templates_model
 */
class Automatic_email extends MX_Controller 
{

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation'); 
        $this->load->model('Automatic_email_model');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('imail');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    public function index()
    {

    }

    
	public function automatic_email_listing(){
		$reqData = request_handler();
			if (!empty($reqData->data)) {
				$reqData = $reqData->data;
				$result = $this->Automatic_email_model->automatic_email_listing($reqData);
				echo json_encode($result);
			}
	}
	
	function assign_template_to_email(){
		$reqData = request_handler();
		
		if ($reqData) {
            $current_admin = $reqData->adminId;
			$reqData = $reqData->data;
			
            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'automaticEmailId', 'label' => 'automaticEmailId', 'rules' => 'required'),
                array('field' => 'templateId', 'label' => 'templateId', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // compose new mail
                $this->Automatic_email_model->assign_template_to_email($reqData, $current_admin);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
	}

}

