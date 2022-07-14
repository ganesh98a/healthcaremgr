<?php defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends MX_Controller
{
    public function __construct()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI =& $this; // Line is needed because we use MX_Controller
        $this->load->model('Settings_model');
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

    /**
     * Send docusign details for modification
     * 
     * `POST: /settings/docusign_settings`
     */
    public function docusign_settings()
    {
        request_handler('access_admin');

        $data = [
            'DocuSignUsername' => get_setting(Setting::DS_STAGING_USERNAME, DS_STAGING_USERNAME),
            'DocuSignPassword' => get_setting(Setting::DS_STAGING_PASSWORD, DS_STAGING_PASSWORD), 
            'DocuSignIntegratorKey' => get_setting(Setting::DS_STAGING_INTEGRATATION_KEY, DS_STAGING_INTEGRATATION_KEY),
        ];

        return $this->output->set_content_type('json')->set_output(json_encode([
            'status' => true,
            'data' => $data,
        ]));
    }


    /**
     * Apply docusign setting changes
     * 
     * `POST: /settings/save_docusign_settings`
     * 
     * @return CI_Output 
     * @throws Exception If key passed to `save_setting` is blank
     */
    public function save_docusign_settings()
    {
        $reqData = request_handler('access_admin');

        $data = obj_to_arr($reqData->data);

        $this->form_validation->set_data($data);

        $validation_rules = [
            [
                'field' => 'DocuSignUsername',
                'label' => 'username',
                'rules' => [ 
                    'required' 
                ]
            ],
            [
                'field' => 'DocuSignPassword',
                'label' => 'password',
                'rules' => [ 
                    'required' 
                ]
            ],
            [
                'field' => 'DocuSignIntegratorKey',
                'label' => 'integrator key',
                'rules' => [ 
                    'required' 
                ]
            ],
        ];

        $this->form_validation->set_rules($validation_rules);

        // validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return $this->output->set_content_type('json')->set_output(json_encode([
                'status' => false,
                'error' => implode(', ', $errors),
            ]));
        }

        try {
            $this->db->trans_begin();

            // save using this helper function
            save_setting(Setting::DS_STAGING_USERNAME, $data['DocuSignUsername']);
            save_setting(Setting::DS_STAGING_PASSWORD, $data['DocuSignPassword']);
            save_setting(Setting::DS_STAGING_INTEGRATATION_KEY, $data['DocuSignIntegratorKey']);

            $this->db->trans_commit();
    
            // apply logs
            $this->log($reqData, [
                'title' => "DocuSign settings saved",
            ]);
    
            return $this->output->set_content_type('json')->set_output(json_encode([
                'status' => true,
                'msg' => 'DocuSign settings successfully saved'
            ]));

        } catch (\Exception $e) {
            $this->db->trans_rollback();

            return $this->output->set_content_type('json')->set_output(json_encode([
                'status' => false,
                'error' => 'Failed to apply docusign settings. ' . $e->getMessage(),
            ]));
        }

    }

    /**
     * Create application log for this controller
     * 
     * @param mixed $reqData
     * @param array<string, mixed> $mergeData
     */
    protected function log($reqData, array $mergeData = [])
    {
        $MODULE_ADMIN = 1;

        $defaults = [
            'user_id' => $reqData->adminId,
            'created_by' => $reqData->adminId,
            'title' => 'Settings updated',
            'description' => json_encode($reqData->data),
            'module' => $MODULE_ADMIN,
        ];

        $data = array_merge($defaults, $mergeData);

        // These items are override-able because it is in the $defaults var
        $this->loges->setUserId($data['user_id']);
        $this->loges->setCreatedBy($data['created_by']);
        $this->loges->setTitle($data['title']);
        $this->loges->setDescription($data['description']);
        $this->loges->setModule($data['module']);

        $this->loges->createLog();
    }

    /**
     * getting the general settings
     */
    public function get_general_settings()
    {
        $reqData = request_handler('access_admin');

        if (!empty($reqData->data)) {
            $result = $this->Settings_model->get_general_settings();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }
        echo json_encode($result);
        exit();
    }

    /**
     * saving the general settings
     */
    public function save_general_settings()
    {
        $reqData = request_handler('access_admin');

        if (!empty($reqData->data)) {
            $data = obj_to_arr($reqData->data);
            $adminId = $reqData->adminId;
            $result = $this->Settings_model->save_general_settings($data, $adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Send seek details for modification
     * 
     * `POST: /settings/seek_settings`
     */
    public function seek_settings()
    {
        request_handler('access_admin');

        $data = [
            'seekUsername' => get_setting(Setting::SEEK_USERNAME),
            'seekPassword' => get_setting(Setting::SEEK_PASSWORD), 
            'seekAdvertiserName' => get_setting(Setting::SEEK_ADVERTISER_NAME),
            'seekAdvertiserId' => get_setting(Setting::SEEK_ADVERTISER_ID),
        ];

        return $this->output->set_content_type('json')->set_output(json_encode([
            'status' => true,
            'data' => $data,
        ]));
    }

    /**
     * Apply seek setting changes
     * 
     * `POST: /settings/save_seek_settings`
     * 
     * @return CI_Output 
     * @throws Exception If key passed to `save_setting` is blank
     */
    public function save_seek_settings()
    {
        $reqData = request_handler('access_admin');

        $data = obj_to_arr($reqData->data);

        $this->form_validation->set_data($data);

        $validation_rules = [
            [
                'field' => 'seekUsername',
                'label' => 'Seek Username',
                'rules' => [ 
                    'required' 
                ]
            ],
            [
                'field' => 'seekPassword',
                'label' => 'Seek Password',
                'rules' => [ 
                    'required' 
                ]
            ],
            [
                'field' => 'seekAdvertiserName',
                'label' => 'Seek Advertiser Account Name',
                'rules' => [ 
                    'required' 
                ]
            ],
            [
                'field' => 'seekAdvertiserId',
                'label' => 'Seek Advertiser Account Id',
                'rules' => [ 
                    'required' 
                ]
            ],
        ];

        $this->form_validation->set_rules($validation_rules);

        // validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return $this->output->set_content_type('json')->set_output(json_encode([
                'status' => false,
                'error' => implode(', ', $errors),
            ]));
        }

        try {
            $this->db->trans_begin();

            // save using this helper function
            save_setting(Setting::SEEK_USERNAME, $data['seekUsername']);
            save_setting(Setting::SEEK_PASSWORD, $data['seekPassword']);
            save_setting(Setting::SEEK_ADVERTISER_NAME, $data['seekAdvertiserName']);
            save_setting(Setting::SEEK_ADVERTISER_ID, $data['seekAdvertiserId']);

            $this->db->trans_commit();
    
            // apply logs
            $this->log($reqData, [
                'title' => "Seek settings saved",
            ]);
    
            return $this->output->set_content_type('json')->set_output(json_encode([
                'status' => true,
                'msg' => 'Seek settings successfully saved'
            ]));

        } catch (\Exception $e) {
            $this->db->trans_rollback();

            return $this->output->set_content_type('json')->set_output(json_encode([
                'status' => false,
                'error' => 'Failed to apply docusign settings. ' . $e->getMessage(),
            ]));
        }

    }
    
    /**
     * Generate random token for data migration access
     */
    function generate_random_token() {
        request_handler('access_admin');

        # generate random token
        $token = bin2hex(random_bytes(32));

        $response = ['status' => true, 'data' => $token];
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }
}
