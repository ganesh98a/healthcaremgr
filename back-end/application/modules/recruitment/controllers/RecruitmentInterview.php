<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Group booking controller
 *
 * @property-read \Recruitment_interview_model $Recruitment_interview_model
 */
class RecruitmentInterview extends \MX_Controller {

    public function __construct() {
        parent::__construct();

        // tell form validation library to point to this
        // subclass of MX_Controller when using callback validations
        // @see https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc/src/codeigniter-3.x/
        $this->load->library('Form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
        $this->load->helper(['array']);
        $this->load->model('Recruitment_interview_model');
        $this->load->model('common/List_view_controls_model');
        $this->loges->setLogType('recruitment_interview');
        $this->load->helper('i_pad');
        $this->load->model('sales/Feed_model');
        $this->load->library('Asynclibrary');
    }

    private function sendResponse($data, $succes_msg = '') {
        if ($succes_msg) {
            $response = ['status' => true, 'data' => $data, 'msg' => $succes_msg];
        } else {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        }
        echo json_encode($response);
        exit();
    }

    /**
     * post parameters: interview_type
     * result: array of all the interview type data
     */
    public function get_all_interview_type() {
        $request = request_handler('access_recruitment');
        $data = json_decode(json_encode($request->data), true);
        $result = $this->Recruitment_interview_model->get_all_interview_type($data);
        $response = ['status' => true, 'interview_types' => $result ];
        echo json_encode($response);
    }

    /**
     * post parameters: recruitment location
     * result: array of all the recruitment location data
     */
    public function get_all_recruitment_location() {
        $request = request_handler('access_recruitment');
        $data = json_decode(json_encode($request->data), true);
        $result = $this->Recruitment_interview_model->get_all_recruitment_location($data);
        echo json_encode($result);
    }

    public function get_interview_by_id(){
        $reqData = request_handler('access_recruitment_admin');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get interview by id;
                $result = $this->Recruitment_interview_model->get_interview_by_id($reqData->data->interview_id);
                //pass key name for type which type option need
                $this->load->model("Common/common_model");
                $result["unsuccessful_reason_option"] = $this->common_model->get_central_reference_data_option("unsuccessful_group_booking_reason");
                if($result['data']->interview_stage_status === "4"){
                    $result["unsuccessful_reason"] = $this->Recruitment_interview_model->get_unsuccessful_reason_of_notes($reqData->data->interview_id);
                }
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }

     /**
     * create and update interview
     */
    public function create_update_interview() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData)) {
            $data = (array) $reqData->data;


            # appending timings into date fields
            $data['interview_start_datetime'] = $data['interview_start_date']." ".$data['interview_start_time'];
            $data['interview_end_datetime'] = $data['interview_end_date']." ".$data['interview_end_time'];


            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array(
                    'field' => 'interview_start_datetime', 'label' => 'Group Booking start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect Group Booking start date & time',
                    ]
                ),
                array(
                    'field' => 'interview_end_datetime', 'label' => 'Group Booking end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect Group Booking end date & time',
                    ]
                    ),
            ];

            if(empty($data['meeting_link']) && empty($data['location_id'])) {
                $validation_rules[] = array('field' => 'location_id', 'label' => 'Location', 'rules' => 'required');
            }

            if($data['invite_type']==1){
                $validation_rules[] = array('field' => 'form_id', 'label' => 'Quiz Template', 'rules' => 'required');
            }

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Call create interview model
                $interview = $this->Recruitment_interview_model->create_update_interview($data, $adminId);
                // According to that interview will be created
                if ($interview['status'] == true) {
                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    if($data['interview_id']){
                        $msg_title = "Group Booking updated for " . $data['title'];
                    }else{
                        $msg_title = "New Group Booking created for " . $data['title'];
                    }
                    $this->Recruitment_interview_model->add_interview_log($data, $msg_title, $data['interview_id'], $adminId);

                    $response = ['status' => true, 'msg' => $interview['msg'], 'data' => array('interview_id' => $interview['interview_id'] ?? '') ];
                } else {
                    $response = ['status' => false, 'error' => $interview['error']];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * fetches list of interviews of applicants
     */
    function get_interviews()
    {
        $reqData = request_handler('access_recruitment');
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['title', 'interview_start_datetime', 'interview_end_datetime', 'location', 'interview_type','owner_name','max_applicant','invite_type'], ['ri.title', 'ri.interview_start_datetime', 'ri.interview_end_datetime', 'ri.location_id', 'ri.interview_type_id','ri.owner','ri.max_applicant','ri.invite_type'], $filter_condition);
            }
        }
        if (!empty($reqData->data)) {
            $response = $this->Recruitment_interview_model->get_interviews($reqData->data, $reqData->adminId, $filter_condition);
            echo json_encode($response);
        }
    }

    /*
     * post parameters: interview-id
     * result: status true or false
     */
    public function archive_interview() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_interview_model->archive_interview((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }
     /*
     * post parameters: interview-id's
     * result: status true or false
     */
    public function rollback_archived_interviews() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_interview_model->rollback_archived_interviews((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    
       /*
     * post parameters: interview-id
     * result: status true or false
     */
    public function archived_interview_list() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_interview_model->archived_interview_list($reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }
    

/*
     * Get contact for send email in activity
     */
    public function get_applicant_name_search() {
        $reqData = request_handler('access_recruitment');
        if ($reqData->data) {
            $result["applicant_option"] = $this->Recruitment_interview_model->get_applicant_name_search($reqData->data);
            $response = ["status" => true, "data" => $result];
        } else {
            $response = ["status" => false, "error" => "search key is required"];
        }

        echo json_encode($response);
    }

    function get_application_data_by_applicant_id()
    {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;
        if (!empty($reqData->applicant_id)) {
            // get applicant details
            $result['applications'] = $this->Recruitment_interview_model->get_application_data_by_applicant_id($reqData->applicant_id);

            if (!empty($result['applications'])) {
                echo json_encode(['status' => true, 'data' => $result]);
            } else {
                echo json_encode(['status' => false, 'data' => 'Applicant not found']);
            }
        }else{
            echo json_encode(['status' => false, 'data' => 'Applicant id missing']);
        }
    }

    /**
     * create and update applicant interview
     */
    public function create_update_applicant_interview() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            // Validation rules set
            $validation_rules = [
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required'),
                array('field' => 'job_id', 'label' => 'Job Id', 'rules' => 'required'),
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required')
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');
                // create interview applicant model
                $check_applicant = $this->Recruitment_interview_model->check_applicant_interview_exists($data, 'create');
                if( $check_applicant['status'] && empty($data['interview_applicant_id'])){
                    $response = ['status' => false, 'error' => $check_applicant['msg'] ];
                }else{
                $interview_applicant = $this->Recruitment_interview_model->create_update_applicant_interview($data, $adminId);
                // According to that interview applicant will be created
                    if ($interview_applicant['status'] == true) {
                        /**
                         * Create logs. it will represent the user action they have made.
                         */
                        if($data['interview_applicant_id']){
                            $msg_title =  $data['interview_applicant_id'] . "applicant updated for Group Booking";
                        }else{
                            $msg_title = $interview_applicant['interview_applicant_id'] . "applicant created for Group Booking";
                        }
                        $this->Recruitment_interview_model->add_interview_log($data, $msg_title, $interview_applicant['interview_applicant_id'], $adminId);

                        $response = ['status' => true, 'msg' => $interview_applicant['msg'] ];
                    }else {
                        $response = ['status' => false, 'error' => $interview_applicant['error']];
                    }
                }

            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }    

    /*
     * For getting applicant list
     *
     * Return type json
     * - count
     * - data
     * - status
     */
    function get_applicant_list_by_interview_id() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitment_interview_model->get_applicant_list_by_interview_id($reqData->data);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }
     /*
     * post parameters: interview-applicant_id
     * result: status true or false
     */
    public function archive_applicant_interview() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_interview_model->archive_applicant_interview((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetches history of interview
     */

    public function get_field_history() {
        $reqData = request_handler('access_recruitment');
        if (empty($reqData)) {
            return;
        }
        $items = $this->Recruitment_interview_model->get_field_history($reqData->data);
        $this->sendResponse($items, 'Success');
    }

    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_interview_duration() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_interview_model->calculate_interview_duration(object_to_array($reqData->data));
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetches all the interview statuses
     */
    function get_interview_stage_status() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Recruitment_interview_model->get_interview_stage_status();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetches all the final interview statuses for interview details page
     */
    function get_interview_statuses_final() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_interview_model->get_interview_statuses_final();
            echo json_encode($response);
        }
        exit(0);
    }

     /**
     * Updating the interview status.
     */
    public function update_interview_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Recruitment_interview_model->update_interview_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Updating the interview status.
     */
    public function update_applicant_interview_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Recruitment_interview_model->update_applicant_interview_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * bulk updating the interview status.
     */
    public function bulk_update_applicant_interview_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Recruitment_interview_model->bulk_update_applicant_interview_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }


    /**
     * fetches list of interviews of applicants
     */
    function get_interviews_list_by_search()
    {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $response = $this->Recruitment_interview_model->get_interviews_list_by_search($reqData->data, $reqData->adminId);
            echo json_encode($response);
        }
    }


    /**
     * fetches list of interviews of applicants
     */
    function get_max_applicant_details()
    {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $response = $this->Recruitment_interview_model->get_max_applicant_details($reqData->data, $reqData->adminId);
            echo json_encode($response);
        }
    }
/*
* For getting resend the invite application email
*
* Return type json
* - count
* - data
* - status
*/

function resend_invite_to_applicants() {
    $reqData = request_handler('access_recruitment');  
    if (!empty($reqData->data)) {
        $data = (array) $reqData->data;
        // Validation rules set
        $validation_rules = [
            array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),
            
        ];

        if (empty($data->applicants) == true || isset($data->applicants) == false) {
            $validation_rules[] = array('field' => 'applicants', 'label' => 'Applicants', 'rules' => 'Please select the applicants');
        }
        // Set data in libray for validation
        $this->form_validation->set_data($data);

        // Set validation rule
        $this->form_validation->set_rules($validation_rules);
        $result = ['status' => false, 'msg' => 'Something went wrong']; 

        // Check data is valid or not
        if ($reqData && (!empty($reqData->data->applicants))) {
            if($this->form_validation->run()){
                $this->load->model(['Recruitment_interview_model']);
                  // call send mail
                  $url = base_url()."recruitment/RecruitmentInterview/send_group_interview_email_invitation";            
                  $param = array('reqData' => $reqData,'adminId'=>$reqData->adminId);
                 
                   $this->asynclibrary->do_in_background($url, $param);
                   echo json_encode(['status' => true, 'msg' => "Invitations have been sent successfully"]);
                   exit();
            }
        } else {
           // If requested data isn't valid
            $errors = $this->form_validation->error_array();
            $result = ['status' => false, 'error' => implode(', ', $errors)];
        }
    } else {
        // If requested data is empty or null
        $result = ['status' => false, 'error' => "Requested data is null"];
    }
        
}
// call the async function for send the group booking invitation
    function send_group_interview_email_invitation() {
        $this->load->model(['Recruitment_interview_model']);
        $reqData = $this->input->post('reqData');
        $adminId = $this->input->post('adminId', true);
        $index = 0;
        foreach($reqData['data']['applicants'] as $val) {            
            $resSendEmail = $this->Recruitment_interview_model->async_resend_invite_to_applicants($val, $reqData['data'],$adminId);           
            $index ++;
        }
        exit();
    }

    public function bulk_send_applicant_interview_status_email() {
        $data = $this->input->post('data');
        $applicant_ids = $this->input->post('applicant_ids');
        
        if (!empty($data)) {
            $result = $this->Recruitment_interview_model->bulk_send_applicant_interview_status_email($data, $applicant_ids);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }
    
    /**
     * Get interview imail template content as html
     */
    public function get_template_content_as_html() {
        $this->load->helper('i_pad');
        $reqData = request_handler('access_recruitment_admin');
        if (!empty($reqData)) {
            $data = (array) $reqData->data;
            $this->load->model('Basic_model');

            // Call create interview model
            $template = $this->Recruitment_interview_model->get_template_content_as_html($data['email_key']);
            
            // According to that interview will be created
            if ($template['status'] == true) {
                $response = ['status' => true, 'data' => $template['data'] ];
            } else {
                $response = ['status' => false, 'error' => 'Something went wrong.'];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($response);
        exit();
    }
    /**
     * Getting currently logged in admin user's details
     */
    public function get_current_admin_user_details() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data))
        return $response;

        require_once APPPATH . 'Classes/admin/admin.php';
        $objAdmin = new AdminClass\Admin();
        $objAdmin->setAdminid($adminId);       
        $result = $objAdmin->get_admin_details();        
        
        if (empty($result)) {
            $response = ['status' => false, 'error' => "User details not found!"];
            echo json_encode($response);
            exit();
        }else {
            $response = ['status' => true, 'data' => ["value" => $result['uuid'], "label" => $result['firstname']." ".$result['lastname'], "email" => $result['username']]];
        }

        echo json_encode($response);
        exit();
    }

/*
* create ms events logs
*
* Return type json
* - count
* - data
* - status
*/

    function create_ms_events_logs() {
        $reqData = request_handler('access_recruitment');  
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $adminId = $reqData->adminId;

            // Validation rules set
            $validation_rules = [
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),            
            ];        
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);
            $response = ['status' => false, 'msg' => 'Something went wrong']; 

            // Check data is valid or not
            if ($this->form_validation->run()) {
                $selected_applicants = $data['selected_applicants'];
                $check_count = 0;
                foreach($selected_applicants as $applicants) {
                $check_applicant = $this->Recruitment_interview_model->check_applicant_interview_exists((array)$applicants, 'create'); 
                    if( $check_applicant['status'] && empty($data['interview_applicant_id'])){
                        $response = ['status' => false, 'error' => $check_applicant['msg'] ];
                        $check_count = $check_count+1;
                        echo json_encode($response);
                        exit();
                    }
                }
                if($check_count==0){
                    $this->load->model('Basic_model');
                $interview_applicant = $this->Recruitment_interview_model->create_gb_ms_events_logs($data, $adminId);
                // According to that interview applicant will be created
            
                    if ($interview_applicant['status']) {
                        $response = ['status' => true, 'msg' => $interview_applicant['msg'] ];
                    }else {
                        $response = ['status' => false, 'error' => $interview_applicant['error']];
                    }
                }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }
    /**
     * check the added applicant already exists
     * */
    function check_applicant_interview_exists() {
        $reqData = request_handler('access_recruitment');  
        if (!empty($reqData->data)) {
            // Check data is valid or not
            $data = (array) $reqData->data;
                $selected_applicants = $data['selected_applicants'];
                $check_count = 0;
                foreach($selected_applicants as $applicants) {
                    if(empty($applicants->interview_applicant_id)){
                        $check_applicant = $this->Recruitment_interview_model->check_applicant_interview_exists((array)$applicants, 'create'); 
                        if( $check_applicant['status'] && empty($data['interview_applicant_id'])){
                            $response = ['status' => false, 'error' => $check_applicant['msg'] ];
                            $check_count = $check_count+1;
                            echo json_encode($response);
                            exit();
                        }else{
                            $response = ['status' => true, 'msg' => 'Something went wrong']; 
                        }
                    }
                
                }            
        
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }

    /**
     * Save the MS resend information 
     * */

    public function resend_ms_invite_to_applicants() {
        $reqData = request_handler('access_recruitment');  
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $adminId = $reqData->adminId;

            // Validation rules set
            $validation_rules = [
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),            
            ];        
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);
            $response = ['status' => false, 'msg' => 'Something went wrong'];
            // Check data is valid or not
            if ($this->form_validation->run()) {           
                $this->load->model('Basic_model');
                $interview_applicant = $this->Recruitment_interview_model->resend_ms_invite_to_applicants($data, $adminId);
                // According to that interview applicant will be created          
                    if ($interview_applicant['status']) {
                        $response = ['status' => true, 'msg' => $interview_applicant['msg'] ];
                    }else {
                        $response = ['status' => false, 'error' => $interview_applicant['error']];
                    }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }

    /**
     * check the group booking already exists
     * */
    public function check_any_changes_done_for_gb_update() {
        $reqData = request_handler('access_recruitment');  
        if (!empty($reqData->data)) {
            // Check data is valid or not
            $data = (array) $reqData->data;
            $check_applicant = $this->Recruitment_interview_model->check_any_changes_done_for_gb_update($data); 
            if($check_applicant['status']){
                $response = ['status' => true];
            }else{
                $response = ['status' => false];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }

    /**
     * get the email template
     * */
    public function get_ms_url_template() {
        $reqData = request_handler('access_recruitment');  

        if (!empty($reqData->data)) {
            // Check data is valid or not
            $data = (array) $reqData->data;
            $check_applicant = $this->Recruitment_interview_model->get_ms_url_template($data);
            $response = $check_applicant;        
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }

    /**
     * get the interview detail by id
     * */
    public function get_interview_detail_by_id() {
        $reqData = request_handler('access_recruitment');  

        if (!empty($reqData->data) && !empty($reqData->data->interview_id)) {
            // Check data is valid or not
            $data = $reqData->data;
            $response = [];
            $interview_data = [];
            $template_data = [];
            // Get interview detail by id
            $interview = $this->Recruitment_interview_model->get_interview_by_id($data->interview_id);
            if (!empty($interview['data'])) {
                $interview_data = (array) $interview['data'];
            }
            $template = $this->Recruitment_interview_model->get_template_content_as_html('group_booking_confirmation');
            if (!empty($template['data'])) {
                $template_data = (array) $template['data'];
            }

            $data = array_merge($interview_data, $template_data);
            $response = ['status' => true, 'data' => $data];
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }

    /**
     * Get interview cancellation imail template content as html
     */
    public function get_cancellation_template_as_html() {
        $this->load->helper('i_pad');
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData)) {
            $data = (array) $reqData->data;
            $this->load->model('Basic_model');

            // get cancellation template
            $template = $this->Recruitment_interview_model->get_cancellation_template_as_html();
            // According to that interview will be created
            if ($template['status'] == true) {
                $response = ['status' => true, 'data' => $template['data'] ];
            } else {
                $response = ['status' => false, 'error' => 'Something went wrong.'];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Save the MS cancellation event to all attendees     
     *  
     * */

    public function update_cancellation_ms_invite_to_gb() {
        $reqData = request_handler('access_recruitment');  
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $adminId = $reqData->adminId;
            // Validation rules set
            $validation_rules = [
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),            
            ];        
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);
            $response = ['status' => false, 'msg' => 'Something went wrong'];
            // Check data is valid or not
            if ($this->form_validation->run()) {           
                $this->load->model('Basic_model');
                $interview_applicant = $this->Recruitment_interview_model->update_cancellation_ms_invite_to_gb($data, $adminId);
                // According to that interview applicant will be created          
                    if ($interview_applicant['status']) {
                        $response = ['status' => true, 'msg' => $interview_applicant['msg'] ];
                    }else {
                        $response = ['status' => false, 'error' => $interview_applicant['error']];
                    }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }


    /**
     * store particular applicant cancel event     
     * */

    public function cancel_ms_event_for_particular_applicant() {
        $reqData = request_handler('access_recruitment');  
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $adminId = $reqData->adminId;

            // Validation rules set
            $validation_rules = [
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),            
            ];        
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);
            $response = ['status' => false, 'msg' => 'Something went wrong'];
            // Check data is valid or not
            if ($this->form_validation->run()) {           
                $this->load->model('Basic_model');
                $interview_applicant = $this->Recruitment_interview_model->cancel_ms_event_for_particular_applicant($data, $adminId);
                // According to that interview applicant will be created          
                    if ($interview_applicant['status']) {
                        $response = ['status' => true, 'msg' => $interview_applicant['msg'] ];
                    }else {
                        $response = ['status' => false, 'error' => $interview_applicant['error']];
                    }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }

    /**
     * update applicant meeting invite response     
     * */
    public function update_invite_response_for_all_applicant() {
        $reqData = request_handler('access_recruitment');  
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $adminId = $reqData->adminId;

            // Validation rules set
            $validation_rules = [
                array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required', "errors" => [ "required" => "Missing Interview Id" ]),            
            ];        
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);
            $response = ['status' => false, 'msg' => 'Something went wrong'];
            // Check data is valid or not
            if ($this->form_validation->run()) {           
                $this->load->model('Basic_model');
                $interview_applicant = $this->Recruitment_interview_model->update_invite_response_for_all_applicant($data, $adminId);
                // According to that interview applicant will be created          
                    if ($interview_applicant['status']) {
                        $response = ['status' => true];
                    }else {
                        $response = ['status' => false, 'error' => $interview_applicant['error']];
                    }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
        exit();   
    }

    /**
     * store ms login and invite failure log.
     */
    public function save_ms_error_log() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;           
            $result = $this->Recruitment_interview_model->save_ms_error_log($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }
   
}
