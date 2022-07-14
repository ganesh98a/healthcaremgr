<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '../vendor/ihor/nspl/autoload.php';
require_once APPPATH . '../vendor/autoload.php';

use function nspl\a\zip;
use MathPHP\Statistics\Distance;

class MemberDashboard extends MX_Controller {

    use formCustomValidation;
    var $member_status = [
        "0" => "Inactive",
        "1" => "Active",
    ];
    var $hourly_times = ["00:00","01:00","02:00","03:00","04:00","05:00","06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00","23:00"];

    function __construct() {
        parent::__construct();
        $this->load->model('Member_model');
        $this->load->model('Basic_model');
        $this->load->model('Member_action_model');
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('recruitment/Recruitment_applicant_move_to_hcm');
        $this->load->model('common/List_view_controls_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('member');
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

    public function index() {
        request_handler('access_member');
        $data['member_count'] = base_url('member_count');
        $data['form_action_member_srch'] = base_url('get_members_list');
    }


    /**
     * fetches all the member statuses
     */
    function get_member_statuses() {
        $reqData = request_handler('access_member');

        if (!empty($reqData->data)) {
            $data = null;
            foreach($this->member_status as $value => $label) {
                $newrow = null;
                $newrow['label'] = $label;
                $newrow['value'] = $value;
                $data[] = $newrow;
            }
            $response = array('status' => true, 'data' => $data);

            echo json_encode($response);
        }
    }

    /*
    * get all reference data to assign to members
    */
    function get_reference_data()
    {
        $reqData = request_handler('access_crm');
        $rows["likes"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>2],['id','display_name as label']);
        $rows["language"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>14],['id','display_name as label']);
        $rows["transport"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>19],['id','display_name as label']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
     * fetching members name matching keyword provided
     */
    function get_member_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $skip_ids = isset($reqData->data->skip_ids) ? $reqData->data->skip_ids : null;
        $rows = $this->Member_model->get_all_member_name_search($name, null, $skip_ids);
        echo json_encode($rows);
    }

    /*
     * fetching member's name based on id provided
     */
    function get_member_name_details() {
        $reqData = request_handler();
        $id = $reqData->data->id ?? '';
        $rows = $this->Member_model->get_all_member_name_search(null, $id);
        if($rows)
        $retarr['member'] = $rows[0];

        $return = array('data' => $retarr, 'status' => true);
        echo json_encode($return);
    }

    /*
    * get all reference data to assign to members
    */
    function get_skill_and_level_data() {
        $reqData = request_handler('access_member');
        $rows["skills"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>20],['id as value','display_name as label']);
        $rows["skill_levels"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>21],['id as value','display_name as label']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
    * get all reference data of unavailability to assign to members
    */
    function get_unavailability_type_data() {
        $reqData = request_handler('access_member');
        $rows["unavailability_types"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>22],['id as value','display_name as label']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /**
     * returns time slots in a gap of hour
     */
    function get_time_slots() {
        $reqData = request_handler('access_member');
        if (!empty($reqData->data)) {
            $data = null;
            foreach($this->hourly_times as $value => $label) {
                $newrow = null;
                $newrow['label'] = $label;
                $newrow['value'] = ($value+1);
                $data[] = $newrow;
            }
            $response = array('status' => true, 'data' => $data);

            echo json_encode($response);
        }
    }

    /**
     * returns time slots in a gap of half-hour
     */
    function get_time_slots_half_hour() {
        $reqData = request_handler('access_member');
        if (!empty($reqData->data)) {
            $numericIndex = $reqData->data->numericIndex ?? true;
            $data = $this->Member_model->get_time_slots_half_hour($numericIndex);
            $response = array('status' => true, 'data' => $data);

            echo json_encode($response);
        }
    }

    public function member_count() {
        $reqData = request_handler('access_member');
        if (!empty($reqData->data)) {
            $this->load->model('Member_action_model');

            $post_data = $reqData->data;

            $rows = $this->Member_action_model->member_count_based_created($post_data->view_type);
            $all_rows = $this->Member_action_model->get_all_member_count();

            if (!empty($all_rows))
                $all_member_count = count($all_rows);
            else
                $all_member_count = 0;

            if (!empty($rows))
                $count = count($rows);
            else
                $count = 0;

            echo json_encode(array('member_count' => $count, 'status' => TRUE, 'all_member_count' => $all_member_count));
            exit();
        }
    }

    /**
     * Controller function to fetch the members list for assigning to a shift
     */
    public function get_members_for_shift() {
        /*
        //$pairs = zip([1, 2, 3, 4], ['a', 'b', 'c','d']); // 1a, 2b, 3c, 4d
        // Test translated PHP function from python = 0.7
        $row1 = array (1,0.8,0.5,1);
        $row2 = array (1,1,0,1);

        $pairs = zip( $row1, $row2 );
        $distance = Distance::manhattan($row1, $row2);
        print_r($pairs);
        print_r($distance);
        die();
        */
        require_once APPPATH . 'Classes/member/Member.php';
        $reqData = request_handler('access_member');
        $rows = $this->Member_model->get_members_for_shift((array)$reqData->data, $reqData->adminId);
        echo json_encode($rows);
        exit();
    }    
    /**
     * Controller function to fetch the members list for the new lightning UI
     */
    function members_list_new()
    {
        $reqData = request_handler('access_member');
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            } 
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['contact','status'], ['contact_name','m.status'], $filter_condition);
            }          
        }
        if (!empty($reqData->data)) {
            $response = $this->Member_model->members_list_new($reqData->data, $reqData->adminId, $filter_condition);
            echo json_encode($response);
        }
    }

    /**
     * Fetching the keypay employees' leaves and reflect them as
     * unavailability within HCM
     */
    function get_keypay_employee_leaves() {
        $reqData = request_handler('access_member');
        if (!empty($reqData->adminId)) {
            $result = $this->Member_model->get_keypay_employee_leaves($reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    public function load_member_list() {
        require_once APPPATH . 'Classes/member/Member.php';
        $reqData = request_handler('access_member');
        $objMember = new MemberClass\Member();
        $rows = $objMember->load_member_list($reqData);
        echo json_encode($rows);
        exit();
    }

    public function get_member_profile() {
        require_once APPPATH . 'Classes/member/Member.php';
        $objMember = new MemberClass\Member();
        $request = request_handler('access_member');
        $memberId = $request->data->member_id;
        $objMember->setMemberid($memberId);
        $row = $objMember->get_member_profile();
        echo json_encode($row);
        exit();
    }

    public function get_member_about() {
        require_once APPPATH . 'Classes/member/Member.php';
        $objMember = new MemberClass\Member();
        $request = request_handler('access_member');
        $memberId = $request->data->member_id;
        $objMember->setMemberid($memberId);
        $row = $objMember->get_member_about();
        echo json_encode(array('status' => true, 'data' => $row));
        exit();
    }

    public function member_app_access() {
        if (!empty(request_handler('access_member'))) {
            $request = request_handler('access_member');

            $status = $request->data->status;
            $member_id = $request->data->member_id;

            $data = array('enable_app_access' => $status);
            $where = array('id' => $member_id);

            $this->Basic_model->update_records('member', $data, $where);
            /* logs */
            $this->loges->setCreatedBy($request->adminId);
            $this->loges->setUserId($member_id);
            $this->loges->setDescription(json_encode($request));
            $this->loges->setTitle('Update portal access for MemberId : ' . $member_id);
            $this->loges->createLog();

            if ($status == 0) {
                // check status if its zero mean app access is disable
                // logout participant if its login in portal
                $this->Basic_model->delete_records('member_login', array('memberId' => $member_id));
                //and send mail to member that is not access portal any more
            }
            $this->disable_portal_access_mail($request);

            echo json_encode(array('status' => true));
            exit();
        }
    }

    public function disable_portal_access_mail($request) {
        $this->load->model('Member_action_model');
        if ($request->data->status == 0) {

            //save disable portal access note
            $note_data = array(
                'userId' => $request->data->member_id,
                'user_type' => 1,
                'note' => $request->data->note,
                'action_by' => $request->adminId,
                'created' => DATE_TIME,
            );

            $this->basic_model->insert_records('disable_portal_access_note', $note_data, $multiple = false);
        }
        $member_data = $this->Member_action_model->get_member_name_and_email($request->data->member_id);

        if (!empty($member_data)) {
            $mail_data = ['note' => isset($request->data->note) ? $request->data->note : "", 'fullName' => $member_data->fullName, 'email' => $member_data->email];
            if ($member_data->email != '')
                send_disable_portal_access_mail($mail_data, $request->data->status);
        }
    }

    public function change_status() {
        require_once APPPATH . 'Classes/common/Disable_portal_access_history.php';
        require_once APPPATH . 'Classes/common/User_active_inactive_history.php';

        $request = request_handler('update_member');
        if (!empty($request->data)) {
            $status = $request->data->status;
            $member_id = $request->data->member_id;
            $data = array('status' => $status);

            if ($status == 0) {
                $data["enable_app_access"] = 0;
            }
            $where = array('id' => $member_id);
            $this->Basic_model->update_records('member', $data, $where);

            // insert history in inactive and active table according to action
            $objUs = new User_active_inactive_history();
            $objUs->setUserId($member_id);
            $objUs->setUser_type(1);
            $objUs->setAction_type($status == 0 ? 2 : 1);
            $objUs->setAction_by($request->adminId);
            $objUs->setArchive(0);
            $objUs->addHistory();

            /* logs */
            $this->loges->setCreatedBy($request->adminId);
            $this->loges->setUserId($member_id);
            $this->loges->setDescription(json_encode($request));
            $this->loges->setTitle('Change status for MemberId : ' . $member_id);
            $this->loges->createLog();

            if ($status == 0) {
                  // also disable portal access of member on inactive participant
                $objDis = new Disable_portal_access();
                $objDis->setUserId($member_id);
                $objDis->setUser_type(1);
                $objDis->setNote("");
                $objDis->setAction_by($request->adminId);
                $objDis->add_history();

                $this->Basic_model->delete_records('member_login', array('memberId' => $member_id));
            }
            echo json_encode(array('status' => true));
            exit();
        }
    }

    public function member_qualification() {
        require_once APPPATH . 'Classes/member/MemberQualification.php';
        $objMember = new MemberQualification();
        $request = request_handler('access_member');

        if (!empty($request->data)) {
            $req = $request->data;

            $memberId = $req->member_id;
            $view_by = $req->view_by;

            if ($view_by == 'Archive')
                $archive = 1;
            else
                $archive = 0;

            $objMember->setMemberid($memberId);
            $objMember->setArchive($archive);
            $objMember->setStart_date($req->start_date);
            $objMember->setEnd_date($req->end_date);

            $row = $objMember->get_member_qualification();

            if (!empty($row)) {
                echo json_encode(array('status' => true, 'data' => $row));
                exit();
            } else {
                echo json_encode(array('status' => false, 'data' => array()));
                exit();
            }
        }
    }

    public function upload_member_qualification() {
        $request = request_handlerFile('update_member');
        $member_data = (array) $request;
        // pr($request);
        if (!empty($member_data)) {
            $validation_rules = array(
                // array('field' => 'expiry', 'label' => 'Qualification Expiry Date', 'rules' => 'required'),
                array('field' => 'docsTitle', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'docsCategory', 'label' => 'Qualification Category', 'rules' => 'required'),
            );

            $this->form_validation->set_data($member_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                #$request_body = file_get_contents('php://input',true);
                #$request_body = json_decode($request_body);
                if (!empty($_FILES) && $_FILES['myFile']['error'] == 0) {
                    $config['upload_path'] = MEMBER_UPLOAD_PATH;
                    $config['input_name'] = 'myFile';
                    $config['directory_name'] = $request->memberId;
                    $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf';

                    $is_upload = do_upload($config);

                    if (isset($is_upload['error'])) {
                        echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                        exit();
                    } else {
                        $insert_ary = array('filename' => $is_upload['upload_data']['file_name'],
                            'memberId' => $request->memberId,
                            'title' => $request->docsTitle,
                            'type' => $request->docsCategory,
                            'expiry' => !empty($request->expiry) ? date('Y-m-d', strtotime($request->expiry)) : null,
                            'created' => DATE_TIME,
                            'archive' => 0,
                        );

                        $rows = $this->Basic_model->insert_records('member_qualification', $insert_ary, $multiple = FALSE);

                        /* logs */
                        $this->loges->setCreatedBy($request->adminId);
                        $this->loges->setUserId($this->input->post('memberId'));
                        $this->loges->setDescription(json_encode($request));
                        $this->loges->setTitle('Added New document : ' . $request->memberName);
                        $this->loges->createLog();

                        if (!empty($rows)) {
                            echo json_encode(array('status' => true));
                            exit();
                        } else {
                            echo json_encode(array('status' => false));
                            exit();
                        }
                    }
                } else {
                    echo json_encode(array('status' => false, 'error' => 'Please select a file to upload'));
                    exit();
                }
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode("\n", $errors));
            }
            echo json_encode($return);
        }
    }

    public function get_member_preference() {
        require_once APPPATH . 'Classes/member/Member.php';
        $objMember = new MemberClass\Member();
        $request = request_handler('access_member');
        $memberId = $request->data->member_id;
        $objMember->setMemberid($memberId);

        $row = $objMember->get_member_preference();
        if (!empty($row)) {
            echo json_encode(array('status' => true, 'data' => $row));
            exit();
        } else {
            echo json_encode(array('status' => false));
            exit();
        }
    }

    public function save_member_availability() {
        $request = request_handler('create_member');
        $responseAry = $request->data;
        $member_data = (array) $request->data->data;

        if (!empty($member_data)) {
            $validation_rules = array(
                array('field' => 'start_date', 'label' => 'Start Date', 'rules' => 'required'),
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                    // array('field' => 'end_date', 'label' => 'End Date', 'rules' => 'required|callback_check_enddate['.isset($member_data->data['end_date'])?$member_data->data['end_date']:''.','.$member_data->data['is_default'].']'),
                    //array('field' => 'first_week[]', 'label' => 'Please select atleast one day availability to Continue', 'rules' => 'callback_check_avail['.$member_data->data['first_week'].']'),
            );

            $this->form_validation->set_data($member_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $is_default = isset($responseAry->data->is_default) ? $responseAry->data->is_default : 1;
                $memberId = $responseAry->memberId;
                $insert_ary = array('title' => $responseAry->data->title,
                    'memberId' => $memberId,
                    'start_date' => DateFormate($responseAry->data->start_date, 'Y-m-d'),
                    //'end_date' => isset($responseAry->data->end_date) && !empty($responseAry->data->end_date) ? DateFormate($responseAry->data->end_date, 'Y-m-d') : '',
                    'first_week' => isset($responseAry->data->first_week) ? json_encode($responseAry->data->first_week) : '',
                    'second_week' => isset($responseAry->data->second_week) && $responseAry->data->open_second_week ? json_encode($responseAry->data->second_week) : '',
                    'flexible_availability' => isset($responseAry->data->flexible_availability) ? $responseAry->data->flexible_availability : 0,
                    'flexible_km' => isset($responseAry->data->flexible_km) ? $responseAry->data->flexible_km : '',
                    'travel_km' => isset($responseAry->data->travel_km) ? $responseAry->data->travel_km : '',
                    'status' => 1,
                    'is_default' => $is_default,
                    'travel_km' => isset($responseAry->data->travel_km) ? $responseAry->data->travel_km : 10,
                    'updated' => DATE_TIME
                );

                if ($is_default == 1) {
                    $delete_data = array('memberId' => $responseAry->memberId);
                    $this->delete_default_availability($delete_data);
                } else {
                    $insert_ary['end_date'] = isset($responseAry->data->end_date) && !empty($responseAry->data->end_date) ? DateFormate($responseAry->data->end_date, 'Y-m-d') : '';
                }

                if ($responseAry->is_update == 0) {
                    $availability_id = $row = $this->Basic_model->insert_records('member_availability', $insert_ary, $multiple = FALSE);
                    //$last_query = $this->db->last_query();
                    //@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/application/modules/member/controllers/response.txt', $last_query.PHP_EOL , FILE_APPEND | LOCK_EX);
                    $this->generate_member_shift($row);
                    $msg = "Availability created successfully.";
                    $log_msg = 'Added new availability : ' . $availability_id;
                } else {
                    $availability_id = $responseAry->data->id;
                    $where = array('id' => $responseAry->data->id);
                    $row = $this->Basic_model->update_records('member_availability', $insert_ary, $where);
                    $msg = "Availabitity updated successfully.";
                    $log_msg = 'Updated availability : ' . $responseAry->full_name;
                }
                #if($responseAry->avail_list)
                {
                    #$this->merge_avail_list($responseAry,$memberId,$availability_id);
                }

                //logs
                $this->loges->setCreatedBy($request->adminId);
                $this->loges->setUserId($responseAry->memberId);
                $this->loges->setDescription(json_encode($request));
                $this->loges->setTitle($log_msg);
                $this->loges->createLog();
                $return = array('status' => true, 'response_msg' => $msg);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
            exit();
        }
    }

    public function delete_default_availability($delete_data) {
        $where_avail = array('memberId' => $delete_data['memberId'], 'is_default' => 1);
        $data = array('archive' => 1);
        $this->Basic_model->update_records('member_availability', $data, $where_avail);
        $last_query = $this->db->last_query();
        @file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/application/modules/member/controllers/response.txt', $last_query . PHP_EOL, FILE_APPEND | LOCK_EX);
        $this->Basic_model->update_records('member_availability_list', $data, $where_avail);
        $last_query = $this->db->last_query();
        @file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/application/modules/member/controllers/response.txt', $last_query . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function merge_avail_list($responseAry, $memberId, $availability_id) {
        //pr($responseAry->avail_list);
        //1 = old save krna hai (nothing to do)
        //2 = old ko delete and new save
        $new_list = $responseAry->avail_list;
        if (!empty($new_list)) {
            $is_run = TRUE;
            foreach ($new_list as $key => $value) {
                if ($value->is_collapse == 'true') {
                    if ($value->status == 2) {
                        $where_avail = array('memberId' => $memberId, 'availability_type' => $value->old->availability_type, 'availability_date' => $value->old->availability_date);
                        $data = array('archive' => 1);
                        $this->Basic_model->update_records('member_availability_list', $data, $where_avail);
                        $is_run = FALSE;
                    }
                } else {
                    $is_run = FALSE;
                }

                if ($is_run == FALSE) {
                    $new_avail_list[] = array('member_availability_id' => $availability_id,
                        'memberId' => $memberId,
                        'is_default' => $value->new->is_default,
                        'availability_type' => $value->new->availability_type,
                        'availability_date' => DateFormate($value->new->availability_date, 'Y-m-d'),
                        'flexible_availability' => isset($responseAry->data->flexible_availability) ? $responseAry->data->flexible_availability : '',
                        'flexible_km' => isset($responseAry->data->flexible_km) ? $responseAry->data->flexible_km : '',
                        'travel_km' => isset($responseAry->data->travel_km) ? $responseAry->data->travel_km : 10,
                    );
                }
            }
            $this->Basic_model->insert_records('member_availability_list', $new_avail_list, $multiple = TRUE);
        }
    }

    /*
     * its used for create/update member
     * handle request for create member modal
     */
    function create_member() { 
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $member_id = $data['id'] ?? 0;
           
            # validation rule
            $validation_rules = [
                array('field' => 'fullname', 'label' => 'Full Name', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
            ];

            # checking the hours per week in numbers / decimals
            if (!empty($reqData->data->hours_per_week)) {
                $validation_rules[] = array('field' => 'hours_per_week', 'label' => 'Hours per week', 'rules' => 'numeric');
            }

             # checking the Max distance to travel (in Kms) in numbers / decimals
             if (!empty($reqData->data->max_dis_to_travel)) {
                $validation_rules[] = array('field' => 'max_dis_to_travel', 'label' => 'Max distance to travel', 'rules' => 'numeric');
            }
             # checking the Experience (In Years) in numbers / decimals
             if (!empty($reqData->data->mem_experience)) {
                $validation_rules[] = array('field' => 'mem_experience', 'label' => 'Experience', 'rules' => 'numeric');
            }

            # Checking key pay empid
            if (!empty($reqData->data->keypay_emp_id)) {
                
                $message = $this->Member_model->validate_keypay_employee_id($adminId, $reqData->data->keypay_emp_id, $member_id);
                
                if(!empty($message) && !$message['status']) {
                    $validation_rules[] = array('field' => 'keypay_emp_id', 'label' => 'External Employee ID
                    ', 'rules' => 'trim|valid_keypay_id',
                    'errors' => [
                        'valid_keypay_id' => $message['error'],
                    ]);                   
                }
            }

            # set data in libray for validate
            $this->form_validation->set_data($data);

            # set validation rule
            $this->form_validation->set_rules($validation_rules);

            # check data is valid or not
            if ($this->form_validation->run()) {
                $action = 'create';
                if(!empty($data['id'])){
                    $action = 'update'; 
                }

                 // create the contact already exist in member
                 $check_contact_exist = $this->Member_model->check_contact_exist_in_member($data);
                
                 if( $check_contact_exist['status']){
                     $response = ['status' => false, 'error' => $check_contact_exist['msg'] ];
                 }else{ 
                    if((isset($data['applicant_id']) && $data['applicant_id']=='0') || $action == 'create'){
                        $check_the_email = $this->Member_model->check_the_email_is_applicant_or_contact($data, $adminId);
                        
                        if($check_the_email['not_valid']=='true')
                         { 
                             $response = ['status' => false, 'error' => $check_the_email['msg']];
                             echo json_encode($response);
                             exit();
                         }
                    }
                   
                    if(!empty($data['applicant_id'])){
                        $check_the_email['is_applicant_created_as_member'] = true;
                        $check_the_email['valid'] = true;
                    }else{
                        $check_the_email['is_applicant_created_as_member'] = false;
                        $check_the_email['valid'] = true;
                    }
                   
                    if ($check_the_email['valid']) {
                    # call create member modal function
                    $member_id = $this->Member_model->create_member($data, $adminId, $check_the_email['is_applicant_created_as_member']);
                    
                    # check $member_id is not empty
                        if ($member_id) {
                            $this->load->library('UserName');
                            $adminName = $this->username->getName('admin', $adminId);
    
                            # create log setter getter
                            if (!empty($data['id'])) {
                                $msg = 'Support worker has been updated successfully.';
                                $this->loges->setTitle("Updated support worker " . $data['fullname'] . " by " . $adminName);
                            } else {
                                $msg = 'Support worker has been created successfully.';
                                $this->loges->setTitle("New support worker created " . $data['fullname'] . " by " . $adminName);
                            }
                            $this->loges->setDescription(json_encode($data));
                            $this->loges->setUserId($member_id);
                            $this->loges->setCreatedBy($adminId);
                            $this->loges->createLog();
    
                            $response = ['status' => true, 'msg' => $msg];
                        } else {
                            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                        }
                }else{
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            }
            } else {
                
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
        exit();
    }

    /*
     * For getting members' skills list
     */
    function get_member_skills_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Member_model->get_member_skills_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * its used for create/update member skill
     * handle request for create/update member skill modal
     */
    function create_update_member_skills() {
        $reqData = request_handler('access_member');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $member_skill_id = $data['id'] ?? 0;
            $member_id = $data['member_id'] ?? 0;

            # validation rule
            $validation_rules = [
                array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required'),
                array('field' => 'skill_id', 'label' => 'Skill', 'rules' => 'required'),
                array(
                    'field' => 'start_date', 'label' => 'Start date', 'rules' => 'required|valid_date_format[Y-m-d]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect start date',
                    ]
                ),
                array('field' => 'start_time_id', 'label' => 'Start time', 'rules' => 'required'),
            ];

            # checking end date & time
            if (!empty($reqData->data->end_date)) {
                $validation_rules[] = array(
                    'field' => 'end_date', 'label' => 'End date', 'rules' => 'required|valid_date_format[Y-m-d]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect end date',
                    ]
                );

                # end time also needs to be provided
                $validation_rules[] = array('field' => 'end_time_id', 'label' => 'End time', 'rules' => 'required');
            }

            # checking if the skill for member is not previously added
            if(!empty($data['member_id']) && !empty($data['skill_id']))
            {
                $rows = $this->Member_model->check_member_skill_already_exist($data['member_id'],$data['skill_id'],$member_skill_id);
                if(!empty($rows))
                {
                    $errors = 'Skill "'.$rows[0]['display_name'].'" already added for this member';
                    $return = array('status' => false, 'error' => $errors);
                    echo json_encode($return);exit();
                }
            }

            # set data in libray for validate
            $this->form_validation->set_data($data);

            # set validation rule
            $this->form_validation->set_rules($validation_rules);

            # check data is valid or not
            if ($this->form_validation->run()) {

                # appending timings into date fields
                if($data['start_date'] && $data['start_time_id'])
                $data['start_date'] = $data['start_date']." ".$this->hourly_times[($data['start_time_id'] - 1)].":00";

                if($data['end_date'] && $data['end_time_id'])
                $data['end_date'] = $data['end_date']." ".$this->hourly_times[($data['end_time_id'] - 1)].":00";

                if (!empty($data['start_date']) && !empty($data['end_date'])) {
                    $date1 = date_create(DateFormate($data['start_date'], 'Y-m-d H:i:s'));
                    $date2 = date_create(DateFormate($data['end_date'], 'Y-m-d H:i:s'));
                    $diff = date_diff($date1, $date2);
                    if (isset($diff->invert) && ($diff->invert > 0 || ($diff->invert == 0 && $diff->h == 0 && $diff->d == 0 && $diff->m == 0 && $diff->y == 0))) {
                        echo json_encode([
                            "status" => false,
                            "error" => "Start date-time: ".$data['start_date']." should be lower to end date-time: ".$data['end_date']
                            ]);
                        exit();
                    }
                }

                # call create/update member skill modal function
                $member_skill_id = $this->Member_model->create_update_member_skills($data, $adminId);

                # check $member_skill_id is not empty
                if ($member_skill_id) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    # create log setter getter
                    if (!empty($data['id'])) {
                        $msg = 'Support worker skill has been updated successfully.';
                        $this->loges->setTitle("Updated Support worker skill by " . $adminName);
                    } else {
                        $msg = 'Support worker skill has been created successfully.';
                        $this->loges->setTitle("New Support worker skill created by " . $adminName);
                    }
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($member_id);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog();

                    $response = ['status' => true, 'msg' => $msg];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Mark member skill as archived.
     */
    public function archive_member_skill() {
        $reqData = request_handler();
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $id = isset($data->id) ? $data->id : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        # does the member skill exist?
        $result = $this->Member_model->get_member_skill_details($data->id, $this->hourly_times);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Member skill does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("member_skill", $upd_data, ["id" => $id]);

        # logging action
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived member skill with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived member skill with ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived member skill"];
        echo json_encode($response);
        exit();
    }

    /**
     * Retrieve member skill details.
     */
    public function get_member_skill_details() {
        $reqData = request_handler('access_member');
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Member_model->get_member_skill_details($data->id, $this->hourly_times);
        echo json_encode($result);
        exit();
    }

    /*
     * For getting members' unavailability list
     */
    function get_member_unavailability_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Member_model->get_member_unavailability_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * its used for create/update member unavailability
     * handle request for create/update member unavailability modal
     */
    function create_update_member_unavailability() {
        $reqData = request_handler('access_member');
        $adminId = $reqData->adminId;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data))
        return $response;

        $data = (array) $reqData->data;
        $response = $this->Member_model->create_update_member_unavailability($data, $adminId);
        if(isset($response) && $response['status'] == true) {
            $this->loges->setCreatedBy($adminId);
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);

            # create log setter getter
            if (!empty($data['id'])) {
                $this->loges->setTitle("Updated member unavailability by " . $adminName);
            } else {
                $this->loges->setTitle("New member unavailability created by " . $adminName);
            }
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($data['member_id']);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Mark member unavailability as archived.
     */
    public function archive_member_unavailability() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;

        $data = (array) $reqData->data;
        $response = $this->Member_model->archive_member_unavailability($data, $adminId);
        if(isset($response) && $response['status'] == true) {
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setTitle(sprintf("Successfully archived member unavailability with ID of %s by %s", $data['id'], $adminName));
            $this->loges->setSpecific_title(sprintf("Successfully archived member unavailability with ID of %s by %s", $data['id'], $adminName));  // set title in log
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($data['id']);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Retrieve member unavailability details.
     */
    public function get_member_unavailability_details() {
        $reqData = request_handler('access_member');
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Member_model->get_member_unavailability_details($data->id);
        echo json_encode($result);
        exit();
    }

    /**
     * Retrieve member details.
     */
    public function get_member_details() {
        $reqData = request_handler('access_member');
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Member_model->get_member_details($data->id);
        echo json_encode($result);
        exit();
    }


    /**
     * Mark member as archived.
     */
    public function archive_member() {
        $reqData = request_handler('access_member');
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $id = isset($data->id) ? $data->id : 0;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        # does the member exist?
        $result = $this->Member_model->get_member_details($data->id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Member does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        $member_data["updated_date"] = DATE_TIME;
        $member_data["updated_by"] = $adminId;
        $member_data["archive"] = 1;
        $result = $this->basic_model->update_records("member", $member_data, ["id" => $data->id]);

        # logging action
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived member with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived member with ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived member"];
        echo json_encode($response);
        exit();
    }

    public function get_member_availability() {
        require_once APPPATH . 'Classes/member/Member.php';
        $objMember = new MemberClass\Member();

        $request = request_handler('access_member');
        $memberId = $request->data->member_id;
        $status = $request->data->status;

        if ($status == 'Active')
            $Db_status = 1;
        else
            $Db_status = 0;

        $objMember->setMemberid($memberId);
        $objMember->setStatus($Db_status);
        $row = $objMember->get_member_availability();

        if (!empty($row)) {
            echo json_encode(array('status' => true, 'data' => $row));
            exit();
        } else {
            echo json_encode(array('status' => false, 'data' => array()));
            exit();
        }
    }

    public function save_member_work_area() {
        $request = request_handler('create_member');
        $responseAry = $request->data;
        $responseData = (array) $request->data->data;
        #pr($responseData);
        if (!empty($responseData)) {
            $validation_rules = array(
                array('field' => 'memberWorkArea', 'label' => 'Work Area', 'rules' => 'required'),
                array('field' => 'memberWorkAreaStatus', 'label' => 'Status for Work Area', 'rules' => 'required'),
            );

            $this->form_validation->set_data($responseData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $row = $this->Basic_model->get_row('member_work_area', array('work_area'), array('memberId' => $responseAry->memberId, 'archive' => 0, 'work_area' => $responseAry->data->memberWorkArea));
                if ($row) {
                    echo json_encode(array('status' => false, 'error' => 'This work area already exists.'));
                    exit();
                } else {
                    $insert_ary = array('memberId' => $responseAry->memberId,
                        'work_area' => isset($responseAry->data->memberWorkArea) ? $responseAry->data->memberWorkArea : '',
                        'work_status' => isset($responseAry->data->memberWorkAreaStatus) ? $responseAry->data->memberWorkAreaStatus : '',
                        'created' => DATE_TIME,
                    );

                    $rows = $this->Basic_model->insert_records('member_work_area', $insert_ary, $multiple = FALSE);

                    //logs
                    $this->loges->setCreatedBy($request->adminId);
                    $this->loges->setUserId($responseAry->memberId);
                    $this->loges->setDescription(json_encode($request));
                    $this->loges->setTitle('Save workarea : ' . $responseAry->memberId);
                    $this->loges->createLog();

                    if (!empty($rows)) {
                        $return = array('status' => TRUE);
                        echo json_encode($return);
                        exit();
                    } else {
                        $return = array('status' => false);
                        echo json_encode($return);
                        exit();
                    }
                }
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    public function get_member_work_area() {
        $request = request_handler('access_member');
        $responseAry = $request->data;
        $status = $responseAry->status;
        $row = $this->Member_action_model->get_member_work_area($status, $responseAry->member_id);
        echo json_encode(array('status' => true, 'data' => $row));
        exit();
    }

    public function upload_member_special_agreement() {
        $request = request_handlerFile('update_member');
        $member_data = (array) $request;
        #pr($request);
        if (!empty($member_data)) {
            $validation_rules = array(
                array('field' => 'expiry', 'label' => 'Expiry date', 'rules' => 'required'),
                array('field' => 'docsTitle', 'label' => 'Title', 'rules' => 'required'),
            );

            $this->form_validation->set_data($member_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                #$request_body = file_get_contents('php://input',true);
                #$request_body = json_decode($request_body);
                if (!empty($_FILES) && $_FILES['myFile']['error'] == 0) {
                    $config['upload_path'] = MEMBER_UPLOAD_PATH;
                    $config['input_name'] = 'myFile';
                    $config['directory_name'] = $this->input->post('memberId');
                    $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf';

                    $is_upload = do_upload($config);

                    if (isset($is_upload['error'])) {
                        echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                        exit();
                    } else {
                        $insert_ary = array('filename' => $is_upload['upload_data']['file_name'],
                            'memberId' => $this->input->post('memberId'),
                            'title' => $this->input->post('docsTitle'),
                            'expiry' => date('Y-m-d', strtotime($this->input->post('expiry'))),
                            'created' => DATE_TIME,
                            'archive' => 0,
                        );
                        $rows = $this->Basic_model->insert_records('member_special_agreement', $insert_ary, $multiple = FALSE);
                        /* logs */
                        $this->loges->setCreatedBy($request->adminId);
                        $this->loges->setUserId($this->input->post('memberId'));
                        $this->loges->setDescription(json_encode($request));
                        $this->loges->setTitle('Added special agreement : ' . $this->input->post('memberName'));
                        $this->loges->createLog();
                        if (!empty($rows)) {
                            echo json_encode(array('status' => true));
                            exit();
                        } else {
                            echo json_encode(array('status' => false));
                            exit();
                        }
                    }
                } else {
                    echo json_encode(array('status' => false, 'error' => 'Please select a file to upload'));
                    exit();
                }

                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode("\n", $errors));
            }
            echo json_encode($return);
        }
    }

    public function get_member_special_agreement() {
        $request = request_handler('access_member');
        $status = $request->data->status;
        $where_array = array('memberId' => $request->data->member_id);

        if ($status == 'Expire')
            $where_array['DATE(expiry) < '] = date('Y-m-d');
        else
            $where_array['DATE(expiry) >='] = date('Y-m-d');

        $columns = array('expiry,filename,id,title');
        $row = $this->Basic_model->get_result('member_special_agreement', $where_array, $columns);

        if (!empty($row)) {
            echo json_encode(array('status' => true, 'data' => $row));
            exit();
        } else {
            echo json_encode(array('status' => false, 'data' => array()));
            exit();
        }
    }

    public function download_selected_file() {
        $request = request_handler('access_member');
        $responseAry = $request->data;
        $this->load->library('zip');
        $member_id = $responseAry->member_id;
        $download_data = $responseAry->downloadData;
        $this->zip->clear_data();
        $x = '';
        $file_count = 0;
        if (!empty($download_data)) {
            $zip_name = time() . '_' . $member_id . '.zip';
            foreach ($download_data as $file) {
                if (isset($file->is_active) && $file->is_active) {
                    $file_path = MEMBER_UPLOAD_PATH . $member_id . '/' . $file->filename;
                    $this->zip->read_file($file_path, FALSE);
                    $file_count = 1;
                }
            }
            $x = $this->zip->archive('archieve/' . $zip_name);
        }
        if ($x && $file_count == 1) {
            echo json_encode(array('status' => true, 'zip_name' => $zip_name));
            exit();
        } else {
            echo json_encode(array('status' => false, 'error' => 'Please select atleast one file to continue.'));
            exit();
        }
    }

    public function update_member_preference() {
        $reqData = request_handler('update_member');
        if (!empty($reqData->data)) {
            $data = $reqData->data;
            $prefers = array();
            $key = ($data->type == 'places') ? 'placeId' : 'activityId';
            $table = ($data->type == 'places') ? 'member_place' : 'member_activity';

            $this->basic_model->delete_records($table, $where = array('memberId' => $data->memberId));
            if (!empty($data->preference)) {
                foreach ($data->preference as $val) {
                    //if ($val->type == 1 || $val->type == 2) {
                    $prefers[] = array('memberId' => $data->memberId, 'type' => $val->type, $key => $val->id);
                    //}
                }

                if (!empty($prefers)) {
                    $this->basic_model->insert_records($table, $prefers, $multiple = true);
                    #last_query();
                    //logs
                    $this->loges->setCreatedBy($reqData->adminId);
                    $this->loges->setUserId($data->memberId);
                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->setTitle('Updated Preference ' . $data->type . ': ' . $data->memberId);
                    $this->loges->createLog();
                }
            }
            echo json_encode(array('status' => true));
        }
    }

    public function member_shift_count() {
        $reqData = request_handler();
        $this->Member_model->member_shift_count($reqData);
    }

    public function get_member_shifts() {
        $reqData = request_handler('access_member');
        #pr($reqData);
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Member_model->get_member_shifts($reqData);
            echo json_encode($result);
        }
    }

    public function get_member_upcoming_shifts() {
        $reqData = request_handler('access_member');
        if (!empty($reqData->data)) {
            $result = $this->Member_model->get_member_upcoming_shifts($reqData->data);
            echo json_encode($result);
        }
    }

    public function generate_member_shift($member_availability_id) {
        /*         * this function is call inside another fun.
        So server side validation is not implemented* */
        $where = array('id' => $member_availability_id, 'archive' => 0);
        $rows = $this->Basic_model->get_row('member_availability', array('*'), $where);

        $x = $this->generate_shift_data($rows, $member_availability_id);
        if (!empty($x))
            $this->Basic_model->insert_records('member_availability_list', $x, $multiple = TRUE);

        $last_query = $this->db->last_query();
        //@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/application/modules/member/controllers/response.txt', $last_query.PHP_EOL , FILE_APPEND | LOCK_EX);
    }

    public function generate_shift_data($rows, $member_availability_id) {
        /*         * this function is call inside another fun.
        So server side validation is not implemented* */

        $main_ary = array();
        $is_create_shift = TRUE;
        $break_loop = FALSE;

        if (is_json($rows->first_week))
            $week = $rows->first_week;
        else if (is_json($rows->second_week))
            $week = $rows->second_week;
        else
            $is_create_shift = FALSE;

        $day_before = '';
        if (is_json($week)) {
            $start_date = date('Y-m-d', strtotime($rows->start_date));
            // if end date is 00 and default is 1 means create current date shift
            if ($rows->is_default == 1) {
                //echo '<br/>';
                //shift is cr
                $day_before = dayDifferenceBetweenDate(date('Y-m-d'), $start_date);
                //echo '<br/>';
                //shift is created if difference between current date and start date is less than 28
                if ($day_before <= 28) {
                    $day_difference = 28 - $day_before;
                    $end_date = date('Y-m-d', strtotime($start_date . " +$day_difference days"));
                } else {
                    $is_create_shift = FALSE;
                }
            } else {
                $end_date = DateFormate($rows->end_date, 'Y-m-d');
            }

            //pr([$rows->start_date, $end_date]);
            if (strtotime($rows->start_date) >= strtotime(date('Y-m-d')) && $is_create_shift) {
                $dates = dateRangeBetweenDate($start_date, $end_date);
                $avail_ary = json_decode($week, true);
                #pr($dates);
                foreach ($dates as $dateKey => $date) {
                    $myWweek = '';
                    if ($break_loop) {
                        $myWweek = 'second_week';
                        $break_loop = FALSE;
                        $avail_ary = json_decode($rows->second_week, true);
                    }

                    if (!empty($avail_ary)) {
                        foreach ($avail_ary as $key => $value) {
                            if ($break_loop)
                                break;
                            foreach ($value as $val) {
                                if ($break_loop)
                                    break;
                                foreach ($val as $kk => $day) {
                                    if ($day && ($date == $kk)) {

                                        $shift_row = $this->Member_model->get_shift_for_availibility(array('member_id' => $rows->memberId, 'shift_date' => $dateKey));
                                        /* if shift is created for any availiibility date then that date will not overide */
                                        if (empty($shift_row)) {
                                            $temp_ary['memberId'] = $rows->memberId;
                                            $temp_ary['flexible_availability'] = $rows->flexible_availability;
                                            $temp_ary['flexible_km'] = $rows->flexible_km;
                                            $temp_ary['is_default'] = $rows->is_default;
                                            $temp_ary['travel_km'] = $rows->travel_km;
                                            $temp_ary['run_mode'] = 'OCS';
                                            $temp_ary['availability_type'] = $key;
                                            $temp_ary['availability_date'] = $dateKey;
                                            $temp_ary['created'] = DATE_TIME;
                                            $temp_ary['member_availability_id'] = $member_availability_id;
                                            $main_ary[] = $temp_ary;
                                        }
                                        // if condition is bocoz, In first/second week we have to create shift till sunday i.e., for a week only
                                        /*  if ($kk == 'Sun' && $myWweek != 'second_week') {
                                          $break_loop = true;
                                          break;
                                      } */
                                  }
                              }
                          }
                      }
                  }
              }
              return $main_ary;
          }
      }
    }

  public function dwes_checked() {
    if (!empty(request_handler('update_member'))) {
        $request = request_handler('update_member');
        $status = $request->data->status;
        $member_id = $request->data->member_id;
        $data = array('dwes_confirm' => $status);
        $where = array('id' => $member_id);
        $this->Basic_model->update_records('member', $data, $where);
        $return_status = isset($status) && $status == 1 ? 0 : 1;
        echo json_encode(array('status' => true, 'return_status' => $return_status));
        exit();
    }
}

public function get_previous_availiability() {
    $reqData = request_handler('access_member');
    $data = $reqData->data;
    $result = $this->Member_model->get_previous_availiability($data);

    $day_difference = 28;
    if (isset($reqData->end_date)) {
        $end_date = date('Y-m-d', strtotime($reqData->end_date));
    } else {
        $start_date = date('Y-m-d', strtotime($data->start_date));
        $end_date = date('Y-m-d', strtotime($start_date . " +$day_difference days"));
    }

        //
    $first_week = isset($data->first_week) ? json_encode($data->first_week) : '';
    $second_week = isset($data->second_week) ? json_encode($data->second_week) : '';

    $row = (object) array('first_week' => $first_week,
        'second_week' => $second_week,
        'is_default' => $data->is_default,
        'end_date' => $end_date,
        'start_date' => date('Y-m-d', strtotime($data->start_date)),
        'memberId' => $data->member_id,
        'flexible_availability' => $data->flexible_availability,
                //'travel_km'=>$data->travel_km,
                //'flexible_km'=>$data->flexible_km,
    );
    $x = $this->generate_shift_data($row, 0);

    $old_record = $result;
    $new_record = $x;

    $colapse_count = 0;
    $main_ary = array();

    if (!empty($new_record)) {
        foreach ($new_record as $key => $value) {
            $main_ary[$key]['is_collapse'] = FALSE;
            $new_s_key = array_search($value['availability_date'], array_column($old_record, 'availability_date'));
            if ($new_s_key !== FALSE) {
                if ($old_record[$new_s_key]['availability_type'] == $value['availability_type']) {
                    $main_ary[$key]['old'] = $old_record[$new_s_key];
                    $main_ary[$key]['is_collapse'] = true;
                    $main_ary[$key]['status'] = 1;
                    $colapse_count = $colapse_count + 1;
                }
            }
            $main_ary[$key]['new'] = $value;
        }
    }
    echo json_encode(array('data_ary' => $main_ary, 'colapse_count' => $colapse_count, 'status' => true));
    exit();
}

public function check_availibility_already_exist() {
    $reqData = request_handler('access_member');
    $data = $reqData->data;
    $result = $this->Member_model->check_availibility_already_exist($data);
    echo json_encode(array('rows_count' => count((array) $result), 'status' => true));
    exit();
}

public function get_member_fms() {
    $reqestData = request_handler('access_fms');
    if (!empty($reqestData->data)) {
        $reqData = json_decode($reqestData->data);
        $result = $this->Member_model->get_member_fms($reqData, $reqestData->adminId);
        echo json_encode($result);
    }
}

public function get_member_position_award() {
    $request = request_handler('access_member');
    $responseAry = $request->data;

    $row = $this->Member_model->get_member_position_award($responseAry);

    echo json_encode(array('status' => true, 'data' => $row));
    exit();
}

public function get_posistion_and_level() {
    request_handler('access_member');

    $row = [];
    $row['levels_list'] = $this->Basic_model->get_record_where('classification_level', ['level_name as label', 'id as value'], '');
    $row['paypoint_level'] = $this->Basic_model->get_record_where('classification_point', ['point_name as label', 'id as value'], '');
    echo json_encode(array('status' => true, 'data' => $row));
    exit();
}

public function save_member_position_award() {
    $request = request_handler('create_member');
    $responseAry = $request->data;

    $mode = $responseAry->mode;


    if ($mode === 'add') {
        $where = ['memberId' => $responseAry->memberId, 'archive' => 0];
        if (isset($responseAry->data->work_area) && $responseAry->data->work_area != '')
            $where['work_area'] = $responseAry->data->work_area;

        $res = $this->Basic_model->get_row('member_position_award', ['id'], $where);

        if (!empty($res)) {
            echo json_encode(array('status' => false, 'msg' => 'Work area already exist.'));
            exit();
        } else {
            $success_msg = 'Position created successfully';
            $this->loges->setTitle('Save Position/Award : ' . $responseAry->memberId);
            $insert_ary = array('memberId' => $responseAry->memberId,
                'work_area' => isset($responseAry->data->work_area) ? $responseAry->data->work_area : '',
                'award' => isset($responseAry->data->award) ? $responseAry->data->award : '',
                'level' => isset($responseAry->data->level) ? $responseAry->data->level : '',
                'pay_point' => isset($responseAry->data->pay_point) ? $responseAry->data->pay_point : '',
                'created' => DATE_TIME,
                'updated' => DATE_TIME,
            );
            $rows = $this->Basic_model->insert_records('member_position_award', $insert_ary);
        }
    } else {

        $where = ['memberId' => $responseAry->memberId, 'archive' => 0, 'id!=' => $responseAry->id];

        if (isset($responseAry->data->work_area) && $responseAry->data->work_area != '')
            $where['work_area'] = $responseAry->data->work_area;

        $res = $this->Basic_model->get_row('member_position_award', ['id'], $where);

        if (!empty($res)) {
            echo json_encode(array('status' => false, 'msg' => 'Work area already exist.'));
            exit();
        }

        $success_msg = 'Position updated successfully';
        $this->loges->setTitle('Update Position/Award : ' . $responseAry->memberId);
        $update_ary = array('memberId' => $responseAry->memberId,
            'work_area' => isset($responseAry->data->work_area) ? $responseAry->data->work_area : '',
            'award' => isset($responseAry->data->award) ? $responseAry->data->award : '',
            'level' => isset($responseAry->data->level) ? $responseAry->data->level : '',
            'pay_point' => isset($responseAry->data->pay_point) ? $responseAry->data->pay_point : '',
        );
        $rows = $this->Basic_model->update_records('member_position_award', $update_ary, array('id' => $responseAry->id));
        $rows = TRUE;
    }

        //logs
    $this->loges->setCreatedBy($request->adminId);
    $this->loges->setUserId($responseAry->memberId);
    $this->loges->setDescription(json_encode($request));

    $this->loges->createLog();

    if (!empty($rows)) {
        echo json_encode(array('status' => true, 'msg' => $success_msg));
        exit();
    } else {
        echo json_encode(array('status' => false, 'msg' => 'Error,Please try again'));
        exit();
    }
        //}
}

public function get_imail_call_list() {
    $request = request_handler('access_member');
    $reqData = json_decode($request->data);
    $result = $this->Member_action_model->get_member_contact_history($reqData);
    echo json_encode($result);
    exit();
}

public function update_Member_profile() {
    $request = request_handler('update_member');
    $member_data = (array) $request->data->member_data;

        #pr($member_data);
    if (!empty($member_data)) {
        $validation_rules = array(
            array('field' => 'prefer_contact', 'label' => 'Prefered Contact', 'rules' => 'required'),
            array('field' => 'gender', 'label' => 'Gender', 'rules' => 'required'),
            array('field' => 'dob', 'label' => 'Date of Birth', 'rules' => 'required|callback_date_check[dob,current,%s cannot enter a date in the future.]'),
            array('field' => 'phone_ary[]', 'label' => 'Member Phone', 'rules' => 'callback_check_phone_number'),
            array('field' => 'email_ary[]', 'label' => 'Member Email', 'rules' => 'callback_check_email_address'),
            array('field' => 'completeAddress[]', 'label' => 'Address', 'rules' => 'callback_check_address|callback_postal_code_check[postal]'),
            array('field' => 'kin_ary[]', 'label' => 'Kin Address', 'rules' => 'callback_kin_details_check[Next of kin ]|callback_phone_number_check[phone,required,Kin contact should be enter valid phone number.]'),
            array('field' => 'phone_ary[]', 'label' => 'Member Phone', 'rules' => 'callback_phone_number_check[phone,required,Member Contact should be enter valid phone number.]'),
        );

        if(!empty($member_data['email_ary']))
        {
            $emails = array_column($member_data['email_ary'], 'email');
            $member_id = isset($member_data['ocs_id']) && $member_data['ocs_id'] >0?$member_data['ocs_id']:0;
            $rows = $this->Member_action_model->check_email_already_exist($emails,$member_id);
            if(!empty($rows))
            {
                $errors = 'Email already exist.';
                $return = array('status' => false, 'error' => $errors);
                echo json_encode($return);exit();
            }
        }

        $this->form_validation->set_data($member_data);
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            $ocs_row = $this->Member_model->update_Member_profile($member_data);
            /* logs */
            $this->loges->setTitle("Update Member: " . $ocs_row['id']);
            $this->loges->setUserId($request->adminId);
            $this->loges->setDescription(json_encode($member_data));
            $this->loges->setCreatedBy($request->adminId);
            $this->loges->createLog();
            $return = array('status' => true, 'msg' => 'Member updated successfully.', 'memberId' => $ocs_row['id']);
        } else {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
        }
        echo json_encode($return);
    }
}

public function check_phone_number($phone_numbers) {
    if (!empty($phone_numbers)) {
        foreach ($phone_numbers as $key => $val) {
            if ($key == 'primary_phone')
                continue;

            if (empty($val)) {
                $this->form_validation->set_message('check_phone_number', 'Phone number can not be empty');
                return false;
            }
        }
    } else {
        $this->form_validation->set_message('check_participant_number', 'phone number can not be empty');
        return false;
    }
    return true;
}

public function check_email_address($email_address) {
    if (!empty($email_address)) {
            #foreach ($email_address as $key => $val) {
            #pr($val);
            /* if ($email_address->primary_email)
            continue; */

            if (empty($email_address->email)) {
                $this->form_validation->set_message('check_email_address', 'Email can not empty');
                return false;
            } elseif (!filter_var($email_address->email, FILTER_VALIDATE_EMAIL)) {
                $this->form_validation->set_message('check_email_address', $email_address->email . ' Email is not valid');
                return false;
            }
            #}
        } else {
            $this->form_validation->set_message('check_email_address', 'Email can not empty');
            return false;
        }
        return true;
    }

    public function check_address($address) {
        if (!empty($address)) {
            if (empty($address->street)) {
                $this->form_validation->set_message('check_address', 'Address can not be empty');
                return false;
            } if (empty($address->state)) {
                $this->form_validation->set_message('check_address', 'State can not be empty');
                return false;
            } if (empty($address->postal)) {
                $this->form_validation->set_message('check_address', 'Pincode can not be empty');
                return false;
            } if (!isset($address->city)   ) {
                $this->form_validation->set_message('check_address', 'Suburb can not be empty');
                return false;
            }
        } else {
            $this->form_validation->set_message('check_address', 'Address can not empty');
            return false;
        }
        return true;
    }

    public function archive_member_qualification() {
        require_once APPPATH . 'Classes/member/MemberQualification.php';
        $objMember = new MemberQualification();
        $request = request_handler('access_member');

        if (!empty($request->data)) {
            $req = $request->data;
            $memberId = $req->member_id;
            $id = $req->id;

            $objMember->setMemberid($memberId);
            $objMember->setMemberqualificationid($id);
            $row = $objMember->archive_member_qualification();

            if (!empty($row)) {
                echo json_encode(array('status' => true));
                exit();
            } else {
                echo json_encode(array('status' => false));
                exit();
            }
        }
    }

    public function archive_member_workarea_awards() {
        $request = request_handler('access_member');

        if (!empty($request->data)) {
            $req = $request->data;
            $memberId = $req->member_id;
            $id = $req->id;
            $type = $req->type;

            $arr = array("id" => $id, "memberId" => $memberId);

            if ($type == 2)
                $row = $this->Basic_model->update_records('member_position_award', array('archive' => 1), $arr);
            else
                $row = $this->Basic_model->update_records('member_work_area', array('archive' => 1), $arr);

            if (!empty($row)) {
                echo json_encode(array('status' => true));
                exit();
            } else {
                echo json_encode(array('status' => false));
                exit();
            }
        }
    }

    public function get_work_area_of_member() {
        $request = request_handler('access_member');
        $row = $this->Member_action_model->get_work_area_of_member($request);
        echo json_encode(array('status' => !empty($row) ? true : false, 'data' => $row));
        exit();
    }

    public function create_Member_profile() {
        $request = request_handler('update_member');
        $member_data = (array) $request->data->member_data;
        #pr($member_data);
        if (!empty($member_data)) {
            $validation_rules = array(
                array('field' => 'firstname', 'label' => 'First Name', 'rules' => 'required'),
                array('field' => 'lastname', 'label' => 'Last Name', 'rules' => 'required'),
                array('field' => 'gender', 'label' => 'Gender', 'rules' => 'required'),
                array('field' => 'prefer_contact', 'label' => 'Prefered Contact', 'rules' => 'required'),
                array('field' => 'dob', 'label' => 'Date of Birth', 'rules' => 'required|callback_date_check[dob,current,%s cannot enter a date in the future.]'),
                array('field' => 'phone_ary[]', 'label' => 'Member Phone', 'rules' => 'callback_check_phone_number'),
                array('field' => 'email_ary[]', 'label' => 'Member Email', 'rules' => 'callback_check_email_address'),
                array('field' => 'completeAddress[]', 'label' => 'Address', 'rules' => 'callback_check_address|callback_postal_code_check[postal]'),
                array('field' => 'phone_ary[]', 'label' => 'Member Phone', 'rules' => 'callback_phone_number_check[phone,required,Member Contact should be enter valid phone number.]'),
                /*array('field' => 'kindetails[]', 'label' => 'Kin Address', 'rules' => 'callback_kin_details_check[Next of kin ]|callback_phone_number_check[phone,required,Kin contact should be enter valid phone number.]'),*/
            );

            if(!empty($member_data['email_ary']))
            {
                $emails = array_column($member_data['email_ary'], 'email');
                $rows = $this->Member_action_model->check_email_already_exist($emails);
                if(!empty($rows))
                {
                    $errors = 'Email already exist.';
                    $return = array('status' => false, 'error' => $errors);
                    echo json_encode($return);exit();
                }
            }

            $this->form_validation->set_data($member_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $ocs_row = $this->Member_model->update_Member_profile($member_data);
                /* logs */
                $this->loges->setTitle("Create Member profile: " . $ocs_row['id']);
                $this->loges->setUserId($request->adminId);
                $this->loges->setDescription(json_encode($member_data));
                $this->loges->setCreatedBy($request->adminId);
                $this->loges->createLog();
                $return = array('status' => true, 'msg' => 'Member Created successfully.', 'memberId' => $ocs_row['id']);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);exit();
        }
    }
}
