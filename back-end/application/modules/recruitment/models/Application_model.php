<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Application_model extends Basic_model
{

    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'recruitment_applicant_applied_application';
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('member/Member_model');
        $this->object_fields['created'] = 'Date Applied';
        $this->object_fields['channelId'] = 'Applied Through';
        $this->object_fields['Recruiter'] = [
            'field' => 'recruiter',
            'object_fields' => $this->Member_model->getObjectFields()
        ];
        $this->object_fields['Applicant'] = [
            'field' => 'applicant_id',
            'object_fields' => $this->Recruitment_applicant_model->getObjectFields()
        ];
        $this->object_fields['status'] = 'Status';
        $this->object_fields['current_stage'] = 'Recruitment Stage';
        $this->object_fields['Creator'] = [
                                            // 'field' => 'Creator',
                                            // 'object_fields' => $this->Recruitment_applicant_model->getObjectFields()
                                        ];
        $this->object_fields['Owner'] = [
                                            'field' => 'recruiter',
                                            'object_fields' => $this->Member_model->getObjectFields()
                                        ];
        $this->setApplicationRecipients();
    }

    public function getStatusOptions()
    {
        $status_options = [1 => 'In Progress', 2 => 'Rejected', 3 => 'Hired'];
        return $status_options;
    }

    public function getRecipientTypes()
    {
        $object_recipient_types = [
            'Application.Users' => 'Application Users'
        ];
        $this->setRecipientTypes($object_recipient_types);
        unset($this->recipient_types['Creator']);
        return $this->recipient_types;
    }

    public function setApplicationRecipients()
    {
        //field should match with $this->object_fields
        $recipients['Application.Users'] = [
            ['field' => 'Applicant', 'label' => 'Applicant'],
            ['field' => 'Recruiter', 'label' => 'Recruiter']
        ];
        $this->setObjectRecipients($recipients);
    }
}
