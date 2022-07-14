<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model file to retrieve and save file info to db.
 *
 * This will also do upload
 */
class Attachment_model extends CI_Model
{
    const META_KEY_IMAGE_WIDTH = 'image_width';
    const META_KEY_IMAGE_HEIGHT = 'image_height';
    const META_KEY_IMAGE_TYPE = 'image_type';
    const META_KEY_IMAGE_SIZE_STR = 'image_size_str';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Find all attachments based on given multiple attachment IDs.
     *
     * For each result, this method will also eager-load related
     * metadata and relationship info with other objects, such as
     * opportunity, lead, contact, org, etc.
     *
     * @param int[] $multiple_ids
     * @return array
     */
    public function find_all_attachments_by_ids($multiple_ids)
    {
        $query = $this->db->from('tbl_sales_attachment')->where('archive', 0)->where_in('id', $multiple_ids)->select()->get();

        $results = $query->result_array();
        if (empty($results)) {
            return [];
        }

        // add related objects
        foreach ($results as $i => $result) {
            $id = $result['id'];
            $results[$i]['meta'] = $this->db->get_where('tbl_sales_attachment_meta', [ 'sales_attachment_id' => $id ])->result_array();
            $results[$i]['relationship'] =$this->db->get_where('tbl_sales_attachment_relationship', [ 'sales_attachment_id' => $id ])->result_array();
        }

        return $results;
    }


    /**
     * Find attachment info by ID.
     *
     * Will also eager-load related metadata and relationship info with other objects such
     * as opportunity, lead, contact, org, etc.
     *
     * @param int $id
     * @return null|array
     */
    public function find_attachment_by_id($id)
    {
        $query = $this->db->get_where('tbl_sales_attachment', [
            'archive' => 0,
            'id' => $id,
        ]);

        $attachment = $query->row_array();
        if (!$attachment) {
            return null;
        }

        $attachment['meta'] = $this->db->get_where('tbl_sales_attachment_meta', [ 'sales_attachment_id' => $id ])->result_array();
        $attachment['relationships'] = $this->db->get_where('tbl_sales_attachment_relationship', [ 'sales_attachment_id' => $id ])->result_array();

        return $attachment;
    }


    /**
     * Find attachment metadata by key and attachment ID
     *
     * @param int $attachment_id
     * @param string $key
     * @return mixed
     */
    public function find_attachment_meta_value($attachment_id, $key)
    {
        $q = $this->db->get_where('tbl_sales_attachment_meta', [
            'sales_attachment_id' => $attachment_id,
            'key' => $key
        ]);

        $value = $q->row('value');
        return $value;
    }

    /**
     * Saves multiple attachment based on given data and file array
     *
     * @param array $data
     * @param array $_files
     * @param int|null $saved_by Usually the `$reqData->adminId`
     * @return array
     */
    public function save_multiple_attachments(array $data, $_files = [], $saved_by = null)
    {
        $object_id = $data['object_id'];
        $object_type = $data['object_type'];
        $object_name = NULL;       
        if(isset($data['object_name'])){
            $object_name = $data['object_name'];
        }

        if (!$object_id || !$object_type) {
            return [
                'status' => false,
                'error' => 'Cannot determine ID and type of the submitted request'
            ];
        }

        $upload_response = $this->upload_multiple_attachments($object_type, $object_id, $_files);
        if ( $upload_response['status'] == FALSE) {
            $firstError = $upload_response['errors'][0];

            return [
                'status' => false,
                'error' => $firstError['file_error'] ?? 'Something went wrong while uploading one of the files'
            ];
        }

        $attachments = $upload_response['attachments'];

        $inserted_relationship_ids = [];
        $inserted_sales_attachment_ids = [];
        $saved_meta_ids = [];

        foreach ($attachments as $attachment) {
            $insertData = [
                'file_name' => $attachment['file_name'],
                'file_type' => $attachment['file_type'],
                'file_path' => $attachment['file_path'],
                'full_path' => $attachment['file_path'],
                'raw_name' => $attachment['raw_name'],
                'orig_name' => $attachment['orig_name'],
                'client_name' => $attachment['client_name'],
                'file_ext' => "." . $attachment['file_ext'],
                'file_size' => round($attachment['file_size'] / 1024),
                'is_image' => $attachment['is_image'],
                'created_by' => $saved_by,
                'aws_object_uri'=> array_key_exists('aws_object_uri', $attachment) ? $attachment['aws_object_uri'] : NULL,
                'aws_uploaded_flag'=> array_key_exists('aws_uploaded_flag', $attachment) ? $attachment['aws_uploaded_flag'] : 0,
                'aws_file_version_id'=> array_key_exists('aws_file_version_id', $attachment) ? $attachment['aws_file_version_id'] : NULL,
                'aws_object_uri'=> array_key_exists('aws_object_uri', $attachment) ? $attachment['aws_object_uri'] : NULL,
                'aws_response'=> array_key_exists('aws_response', $attachment) ? $attachment['aws_response'] : NULL,

            ];

            $isSuccess = $this->db->insert('tbl_sales_attachment', $insertData);
            if (!$isSuccess) {
                continue;
            }

            $sales_attachment_id = $this->db->insert_id();
            $inserted_sales_attachment_ids[] = $sales_attachment_id;

            $isSuccess = $this->db->insert('tbl_sales_attachment_relationship', [
                'sales_attachment_id' => $sales_attachment_id,
                'object_id' => $object_id,
                'object_type' => $object_type,
                'object_name' => $object_name
            ]);

            if (!$isSuccess) {
                continue;
            }

            $relationship_id = $this->db->insert_id();
            $inserted_relationship_ids[] = $relationship_id;

            // if attachment is an image, lets insert additional meta data
            if ($attachment['is_image'] ?? null) {
                $meta_keys_for_img = [
                    self::META_KEY_IMAGE_HEIGHT,
                    self::META_KEY_IMAGE_WIDTH,
                    self::META_KEY_IMAGE_TYPE,
                    self::META_KEY_IMAGE_SIZE_STR,
                ];
                foreach ($meta_keys_for_img as $k) {
                    if (isset($attachment[$k])) {
                        $saved_meta_ids[] = $this->save_attachment_meta($sales_attachment_id, $k, $attachment[$k], $saved_by);
                    }
                }
            }

        }

        if (empty($inserted_relationship_ids)) {
            return [
                'status' => false,
                'error' => 'No files were uploaded'
            ];
        }

        return [
            'status' => true,
            'inserted_relationship_ids' => $inserted_relationship_ids,
            'inserted_sales_attachment_ids' => $inserted_relationship_ids, // needed by Attachment::save_multiple_attachments() controller action
            'saved_meta_ids' => $saved_meta_ids,
            'msg' => sprintf("%s file%s uploaded successfully",
                count($inserted_sales_attachment_ids),
                count($inserted_sales_attachment_ids) > 1 ? 's' : ''
            )
        ];
    }

    /**
     * Prepare meta value before inserting into `tbl_sales_attachment_meta`.
     *
     * * `null` will be saved as `null`
     * * `bool` will be saved as `0` or `1`
     * * `object` or `array` will be saved in json form via `json_encode`
     * * For rest of the values, we trust that the caller is passing `string` or any numeric value
     *
     * @todo Unit test???
     * @param mixed $value
     * @return string|int|null
     */
    protected function serialize_meta_value($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value === true ? 1 : 0;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return $value;
    }


    /**
     * Saves attachment metadata.
     *
     * `tbl_sales_attachment_meta` is an table storage of any unstructured data (like NoSQL),
     * meaning we can store whatever we want without making changes in DB schema
     *
     * One example use case of this is if you want to store image dimensions.
     * You can use this to store attachment description if you want
     *
     * TIP: For possible values of meta keys, use application/class constants.
     *
     * @param int $sales_attachment_id
     * @param string $key
     * @param mixed $value
     * @param int|null $saved_by
     * @return int|null
     */
    public function save_attachment_meta($sales_attachment_id, $key, $value, $saved_by = null)
    {
        $savedId = null;

        $existingMeta = $this->db->get_where('tbl_sales_attachment_meta', [
            'key' => $key,
            'sales_attachment_id' => $sales_attachment_id
        ], 1)->row_array();

        if (!empty($existingMeta)) {
            // update meta
            $dataToBeUpdated = [
                'value' => $this->serialize_meta_value($value),
                'updated_by' => $saved_by,
            ];
            $cond = ['id' => $existingMeta['id']];
            $isSuccess = $this->db->update('tbl_sales_attachment_meta',$dataToBeUpdated, $cond);
            if ($isSuccess) {
                $savedId = $existingMeta['id'];
            }
        } else {
            // insert meta
            $dataToBeInserted = [
                'sales_attachment_id' => $sales_attachment_id,
                'key' => $key,
                'value' => $this->serialize_meta_value($value),
                'created_by' => $saved_by,
            ];

            $isSuccess = $this->db->insert('tbl_sales_attachment_meta', $dataToBeInserted);
            if ($isSuccess) {
                $savedId = $this->db->insert_id();
            }
        }

        return $savedId;

    }

    /**
     * Uploads attachments based on provided `$_files` (usually the `$_FILES` superglobal)
     *
     * @param array $_files Pass $_FILES here
     *
     * @return array
     */
    public function upload_multiple_attachments($object_type, $object_id, $_files, $object_name='')
    {
        $errors = [];

       $name = $this->get_module_folder_name($object_type);

        if (!empty($_files)) {
            $config['input_name'] = $object_name ? $object_name : 'files';
            $config['directory_name'] = '';
            $config['module_id'] = 3;

            // make sure the array items are similar to those mentioned in client
            $config['allowed_types'] = implode('|', [
                'jpg',
                'jpeg',
                'png',
                //'xlx',
                //'xlsx',
                //'xls',
                'doc',
                'docx',
                'pdf',
                //'csv',
                //'odt',
                //'rtf',
            ]);

            if(getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                $files = $_FILES;                
                $local_upload_path = $this->ensure_directory_for_attachment_created($object_type, $object_id);
                $config['upload_path'] = $local_upload_path; // with trailing slash

                do_muliple_upload($config);
                //Assign variable again into $_FILES because do_upload method will cleared the values
                $_FILES = $files;
            }

            require_once APPPATH . 'Classes/common/Aws_file_upload.php';
            $awsFileupload = new Aws_file_upload();
            if(!empty($object_name)){
                $config['upload_path'] = S3_SALES_ATTACHMENT_UPLOAD_PATH . $name . "/$object_id/" . "$object_name/";
            }else{
                $config['upload_path'] = S3_SALES_ATTACHMENT_UPLOAD_PATH . $name . "/$object_id/";
            }
            $response = $awsFileupload->do_muliple_upload($config, FALSE);

            $attachments = [];
            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    if (isset($val['error'])) {
                        $errors[]['file_error'] = strip_tags($val['error']);
                    } else {
                        $attachments[$key] = $val['upload_data'];
                    }
                }

                if (!empty($attachments)) {
                    return [
                        'status' => true,
                        'attachments' => $attachments,
                    ];
                }
            }
        }

        return [
            'status' => false,
            'errors' => $errors,
        ];
    }

     /** Get module name for S3 upload */
     public function get_module_folder_name($object_type) {

        $module_name = [1 => 'leads', 2 => 'opportunity', 3 => 'contact', 4 => 'organisation',
            5 => 'need_assessment', 6 => 'risk_assessment', 7 => 'service_agreement/attachment'];

        return array_key_exists($object_type, $module_name) ? $module_name[$object_type] : $object_type;
    }

    /**
     * Attempt to create folder for object type and object id. If attempt fails, return `false`.
     *
     * This is needed because the helper `create_directory()` cannot create folders recursively
     *
     * @param int $object_type
     * @param int $object_id
     * @return false|string
     */
    protected function ensure_directory_for_attachment_created($object_type, $object_id)
    {
        $destination = 'SALES_ATTACHMENT_UPLOAD_PATH' . $object_type;
        create_directory($destination);
        if ( ! is_writable($destination)) {
            return false;
        }

        $destination = 'SALES_ATTACHMENT_UPLOAD_PATH' . $object_type . "/" . $object_id;
        create_directory($destination);
        if ( ! is_writable($destination)) {
            return false;
        }

        return $destination;
    }

    /**
     * Gets all records of `tbl_sales_attachment` if the given `$object_id` and `$object_type`
     * exist in `tbl_sales_attachment_relationships`
     *
     * @param int $object_id
     * @param int $object_type
     * @return array[]
     */
    public function find_all_attachments_by_object_id($object_id, $object_type)
    {
        $q = $this->db
            ->from('tbl_sales_attachment att')
            ->join('tbl_sales_attachment_relationship rel', 'rel.sales_attachment_id = att.id', 'INNER')
            ->where([
                'rel.object_id' => $object_id,
                'rel.object_type' => $object_type,
            ])
            ->select(['att.*','rel.object_name'])
            ->order_by('att.created','DESC')
            ->get();

        $results = $q->result_array();
        foreach ($results as $i => $result) {
            $results[$i]['metadata'] = $this->find_all_attachment_meta_by_attachment_id($result['id']);
        }

        return $results;
    }


    /**
     * Find all attachment metadata by `$attachment_id`
     *
     * @param int $attachment_id
     * @return array[]
     */
    public function find_all_attachment_meta_by_attachment_id($attachment_id)
    {
        return $this->db->get_where('tbl_sales_attachment_meta', ['sales_attachment_id' => $attachment_id])->result_array();
    }

    /**
     * Computes the attachment path by attachment ID
     *
     * @todo: Write integration tests?
     * @param mixed $attachment_id
     * @return false|string
     */
    public function get_attachment_path_by_id($attachment_id)
    {
        $attachment = $this->find_attachment_by_id($attachment_id);
        if (!$attachment) {
            return false;
        }

        $file_name = $attachment['file_name'];
        $fullpath = $attachment['full_path'];
        if (!$fullpath) {

            // compute the fullpath
            $relationship = ($attachment['relationships'] ?? [])[0] ?? null;
            if (!empty($relationship)) {
                $object_type = $relationship['object_type'];
                $object_id = $relationship['object_id'];
                $destination = SALES_ATTACHMENT_UPLOAD_PATH . $object_type . '/' . $object_id . '/' . $file_name;
                $fullpath = $destination;
            }
        }

        // try again
        if (!$fullpath) {
            return false;
        }


        if (file_exists($fullpath)) {
            return $fullpath;
        }

        return false;
    }

    /** Download content from S3
     * @param $s3_file_path {string} S3 File Path
     * @param $file_name {string} filename which is stored in user machine
     * @return error status / File download
     * **/
    public function s3MediaDownload($s3_file_path, $file_name = NULL) {
        $this->load->library('AmazonS3');

        $this->amazons3->setFolderKey($s3_file_path);
        $this->amazons3->setSourceFile($file_name);
        return $this->amazons3->downloadDocument();
    }

    /**
     * Gets all records of `tbl_sales_attachment` if the given `$object_id` and `$object_type`
     * exist in `tbl_sales_attachment_relationships`
     *
     * @param int $object_id
     * @param int $object_type
     * @return array[]
     */
    public function find_all_attachments_by_object_id_and_name($data)
    {
        $q = $this->db
            ->from('tbl_sales_attachment att')
            ->join('tbl_sales_attachment_relationship rel', 'rel.sales_attachment_id = att.id', 'INNER')
            ->where([
                'rel.object_id' => $data['need_assessment_id'],
                'rel.object_type' => $data['object_type'],
                'rel.object_name' => $data['object_name'],
                'att.archive' => 0
            ])
            ->select(['att.*','rel.object_name'])
            ->order_by('att.created','DESC')
            ->get();

        $results = $q->result_array();

        foreach ($results as $i => $result) {
            $results[$i]['metadata'] = $this->find_all_attachment_meta_by_attachment_id($result['id']);
        }

        return $results;
    }

    public function find_all_attachments_by_object_id_and_like_obj_name($data)
    {
        $q = $this->db
            ->from('tbl_sales_attachment att')
            ->join('tbl_sales_attachment_relationship rel', 'rel.sales_attachment_id = att.id', 'INNER')
            ->like('rel.object_name', 'ns_')
            ->where([
                'rel.object_id' => $data['need_assessment_id'],
                'rel.object_type' => $data['object_type'],
                'att.archive' => 0,
            ])
            ->select(['att.*','rel.object_name'])
            ->order_by('att.created','DESC')
            ->get();

        $results = $q->result_array();
        foreach ($results as $i => $result) {
            $results[$i]['metadata'] = $this->find_all_attachment_meta_by_attachment_id($result['id']);
        }

        $data = [];
        foreach ($results as $key => $result) {
            $data[$result['object_name']][] = $result;
        }


        return $data;
    }

    /**
     * Saves multiple attachment based on given data and file array
     *
     * @param array $data
     * @param array $_files
     * @param int|null $saved_by Usually the `$reqData->adminId`
     * @return array
     */
    public function save_multiple_attachments_for_nutritional_support(array $data, $_files = [], $saved_by = null)
    {
        foreach ($_files as $key=>$fileData) {
            $object_id = $data['object_id'];
            $object_type = $data['object_type'];
            $object_name = NULL;
            if(isset($data['object_name'])){
                $object_name = $key;
            }
    
            if (!$object_id || !$object_type) {
                return [
                    'status' => false,
                    'error' => 'Cannot determine ID and type of the submitted request'
                ];
            }
    
            $upload_response = $this->upload_multiple_attachments($object_type, $object_id, $fileData,$key);
            if ( $upload_response['status'] == FALSE) {
                $firstError = $upload_response['errors'][0];
    
                return [
                    'status' => false,
                    'error' => $firstError['file_error'] ?? 'Something went wrong while uploading one of the files'
                ];
            }
    
            $attachments = $upload_response['attachments'];
    
            $inserted_relationship_ids = [];
            $inserted_sales_attachment_ids = [];
            $saved_meta_ids = [];
    
            foreach ($attachments as $attachment) {
                $insertData = [
                    'file_name' => $attachment['file_name'],
                    'file_type' => $attachment['file_type'],
                    'file_path' => $attachment['file_path'],
                    'full_path' => $attachment['file_path'],
                    'raw_name' => $attachment['raw_name'],
                    'orig_name' => $attachment['orig_name'],
                    'client_name' => $attachment['client_name'],
                    'file_ext' => "." . $attachment['file_ext'],
                    'file_size' => round($attachment['file_size'] / 1024),
                    'is_image' => $attachment['is_image'],
                    'created_by' => $saved_by,
                    'aws_object_uri'=> array_key_exists('aws_object_uri', $attachment) ? $attachment['aws_object_uri'] : NULL,
                    'aws_uploaded_flag'=> array_key_exists('aws_uploaded_flag', $attachment) ? $attachment['aws_uploaded_flag'] : 0,
                    'aws_file_version_id'=> array_key_exists('aws_file_version_id', $attachment) ? $attachment['aws_file_version_id'] : NULL,
                    'aws_object_uri'=> array_key_exists('aws_object_uri', $attachment) ? $attachment['aws_object_uri'] : NULL,
                    'aws_response'=> array_key_exists('aws_response', $attachment) ? $attachment['aws_response'] : NULL,
    
                ];
    
                $isSuccess = $this->db->insert('tbl_sales_attachment', $insertData);
                if (!$isSuccess) {
                    continue;
                }
    
                $sales_attachment_id = $this->db->insert_id();
                $inserted_sales_attachment_ids[] = $sales_attachment_id;
    
                $isSuccess = $this->db->insert('tbl_sales_attachment_relationship', [
                    'sales_attachment_id' => $sales_attachment_id,
                    'object_id' => $object_id,
                    'object_type' => $object_type,
                    'object_name' => $object_name
                ]);
                if (!$isSuccess) {
                    continue;
                }
    
                $relationship_id = $this->db->insert_id();
                $inserted_relationship_ids[] = $relationship_id;
    
                // if attachment is an image, lets insert additional meta data
                if ($attachment['is_image'] ?? null) {
                    $meta_keys_for_img = [
                        self::META_KEY_IMAGE_HEIGHT,
                        self::META_KEY_IMAGE_WIDTH,
                        self::META_KEY_IMAGE_TYPE,
                        self::META_KEY_IMAGE_SIZE_STR,
                    ];
                    foreach ($meta_keys_for_img as $k) {
                        if (isset($attachment[$k])) {
                            $saved_meta_ids[] = $this->save_attachment_meta($sales_attachment_id, $k, $attachment[$k], $saved_by);
                        }
                    }
                }
    
            }
        }
       

        if (empty($inserted_relationship_ids)) {
            return [
                'status' => false,
                'error' => 'No files were uploaded'
            ];
        }

        return [
            'status' => true,
            'inserted_relationship_ids' => $inserted_relationship_ids,
            'inserted_sales_attachment_ids' => $inserted_relationship_ids, // needed by Attachment::save_multiple_attachments() controller action
            'saved_meta_ids' => $saved_meta_ids,
            'msg' => sprintf("%s file%s uploaded successfully",
                count($inserted_sales_attachment_ids),
                count($inserted_sales_attachment_ids) > 1 ? 's' : ''
            )
        ];
    }
}
