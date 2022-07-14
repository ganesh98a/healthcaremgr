<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Attachment_model $Attachment_model
 *
 * Controller for attachment-related actions.
 */
class Attachment extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->loges->setLogType('crm');

        $this->load->model('Attachment_model');
        $this->load->model('common/Common_model');
        $this->load->helpers(['message', 'file']);
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
     * Find all attachment by object ID and object type
     */
    public function get_all_related_attachments()
    {
        $reqData = request_handler();
		if (empty($reqData->data->org_portal)) {
            $reqData = request_handler('access_crm');
        }
        $this->output->set_content_type('json');
        $data = obj_to_arr($reqData->data);

        $object_id = $data['object_id'] ?? 0;
        $object_type = $data['object_type'] ?? 0;

        $results = $this->Attachment_model->find_all_attachments_by_object_id($object_id, $object_type);

        // don't expose paths to client side
        foreach ($results as $i => $item) {
            $exclude_keys = ['file_path', 'full_path'];

            foreach ($exclude_keys as $exclude_key) {
                if (isset($results[$i][$exclude_key])) {
                    unset($results[$i][$exclude_key]);
                }
            }
        }


        return $this->output->set_output(json_encode([
            'status' => true,
            'data' => $results,
        ]));
    }


    /**
     * Uploads attachments and inserts the information of uploaded attachment to DB.
     *
     * Items in the link below will be added to the database (in 2 tables)
     * https://codeigniter.com/userguide3/libraries/file_uploading.html#CI_Upload::data
     *
     * @todo. ATM this only uploads new attachment.
     * There wasnt a requirement to update existing one
     */
    public function save_multiple_attachments()
    {
        $data = request_handlerFile('access_crm',true,false,true);

        $adminId = $data->adminId;
        $this->output->set_content_type('json');

        $attachment_object_types = $this->db->get_where('tbl_sales_attachment_relationship_object_type')->result_array();
        $attachment_object_type_ids = array_values(array_column($attachment_object_types, 'id'));

        $validation_rules = [
            [
                'field' => "object_id",
                'label' => 'Object id',
                'rules' => [ 'required', 'is_natural_no_zero' ]
            ],
            [
                'field' => "object_type",
                'label' => 'Object type',
                'rules' => [ 'required', 'trim', sprintf("in_list[%s]", implode(',', $attachment_object_type_ids)) ]
            ],
        ];

        $this->form_validation->set_data((array) $data)->set_rules($validation_rules);

        if ( ! $this->form_validation->run()) {
            $error_array = $this->form_validation->error_array();
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => implode(', ', $error_array)
            ]));
        }

        $result = $this->Attachment_model->save_multiple_attachments((array) $data, $_FILES, $adminId);

        if ($result['status']) {
            $attachment_ids = $result['inserted_sales_attachment_ids'];

            // needed by client side to preview all recently uploaded files
            $uploadedFiles = $this->Attachment_model->find_all_attachments_by_ids($attachment_ids);

            // don't expose paths to client side
            foreach ($uploadedFiles as $i => $item) {
                $exclude_keys = ['file_path', 'full_path'];

                foreach ($exclude_keys as $exclude_key) {
                    if (isset($uploadedFiles[$i][$exclude_key])) {
                        unset($uploadedFiles[$i][$exclude_key]);
                    }
                }
            }

            $result['uploadedFiles'] = $uploadedFiles;
        }


        return $this->output->set_output(json_encode($result));
    }


    /**
     * Preview attachment
     *
     * @todo Don't know if this action method does support large files
     *
     * @return CI_Output
     */
    public function preview_attachment()
    {
        // the usual `request_handler` does not support GET params
        // so let's created our own `request_handler`        

        $attachment_id = $this->input->get('attachment_id');
        $attachment = $this->Attachment_model->find_attachment_by_id($attachment_id);
        if (!$attachment) {
            return $this->output->set_output('This attachment does not exist anymore or was marked as deleted');
        }

        if($attachment['aws_uploaded_flag'] == 1) {
            $file_path = str_replace('=', '%3D%3D', base64_encode($attachment['file_path']));
            $token = random_genrate_password(10);
            #Set the token for download verification
           $this->Common_model->insert_file_download_validation_token($token);
           //Redirect to frontend common download page
            redirect(getenv('OCS_REACT_ADMIN_URL'). "admin/common/download/crm/?url=mediaShow/rg/$attachment_id/". $file_path . "?download_as=". $attachment['file_name']. "&s3=true&link=" . $token);

            exit();
        }
        // some dev relocated the files without updating the db
        // or someone tinkered with the files
        $location = $this->Attachment_model->get_attachment_path_by_id($attachment_id);
        if (!$location) {
            return $this->output->set_output('File does not exist.');
        }

        // use the name that was used originally by client, or if empty, fallback to name of the file
        $filename = $attachment['client_name'] ?? null;
        if (empty($filename)) {
            $filename = basename($location);
        }

        // use the mime in DB or fallback to determining mime manually if not found
        $mimeType = $attachment['file_type'] ?? null;
        if (empty($mimeType)) {
            $mimeType = get_mime_by_extension($location);
        }

        // @todo: Maybe support large files
        return $this->output
            ->set_header("Content-type: $mimeType")
            ->set_header("Content-Disposition: inline; filename=$filename")
            ->set_output(file_get_contents($location));
    }


    /**
     * Custom request handler function. If allowed, will return the request data and some additional info,
     * else will shortcircuit the request
     *
     * This is necessary because we want a way to validate token via GET request
     *
     * @param string $token
     * @param mixed|null $pin
     * @param string $permission_key
     * @return mixed
     */
    private function request_handler($token, $pin = null, $permission_key = '')
    {
        // verify_server_request(); // <!--- doesn't work if request came from itself!

        $request_body = [
            "token" => $token,
            "pin" => $pin,
            "request_data" => $this->input->get(),
        ];

        // here check token , pin, permission
        $response = verifyAdminToken((object) $request_body, $permission_key, $pin);

        if (!empty($response['status'])) {
            $request_body['request_data']['adminId'] = $response['adminId'];
            return $request_body['request_data'];
        } else {
            echo "Invalid request. Missing token";
            exit();
        }
    }

}