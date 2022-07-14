<?php


defined('BASEPATH') or exit('No direct script access allowed');

//class Master extends MX_Controller  
class Download extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('download', 'url');
        $this->load->helper('email_template_helper');
    }

    public function index()
    {
    }

    public function file()
    {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $file_name = $reqData->data->path;
            $file_url = FCPATH . CRM_UPLOAD_PATH . $reqData->data->crmParticipantId . '/' . $file_name;
            if (!file_exists($file_url)) {
                $file_url = FCPATH . ARCHIEVE_DIR . '/' . $file_name;
            }
            $filesize = strlen($file_url);
            $mime =  $this->mime_content_type($file_name);
            // var_dump($mime);exit;
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: private, no-transform, no-store, must-revalidate');
            header('Cache-Control: private', false);
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename=\"' . basename($file_name) . '\"');
            header('Content-Transfer-Encoding: binary');
            header('Connection: close');
            readfile($file_url);
        }
    }
    public function file_plan()
    {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $file_name = $reqData->data->path;
            $file_url = FCPATH . CRM_UPLOAD_PATH . $reqData->data->crmParticipantId . '/Stage3/' . $file_name;
            $filesize = strlen($file_url);
            $mime =  $this->mime_content_type($file_name);
            // var_dump($mime);exit;
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: private, no-transform, no-store, must-revalidate');
            header('Cache-Control: private', false);
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename=\"' . basename($file_name) . '\"');
            header('Content-Transfer-Encoding: binary');
            header('Connection: close');
            readfile($file_url);
        }
    }
    public function doument_file_crm()
    {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $file_name = $reqData->data->path;
            $file_url = FCPATH . 'archieve/' . $file_name;
            $data1 = file_get_contents($file_url);
            force_download($file_url, null);
        }
    }

    function mime_content_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        $tmp = explode('.', $filename);
        $ext = strtolower(array_pop($tmp));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}
