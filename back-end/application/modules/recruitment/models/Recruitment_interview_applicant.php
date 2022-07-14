<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Recruitment_interview_applicant extends Basic_model
{

    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'recruitment_interview_applicant';
        $this->object_fields['email'] = function($interviewId = '') {
            if (empty($interviewId)) {
                return 'Email';
            }
            $joins = [
                        ['left' => ['table' => 'recruitment_applicant AS ra', 'on' => 'ria.applicant_id = ra.id']],
                        ['left' => ['table' => 'person AS p', 'on' => 'ra.person_id = p.id']]
                    ];
            $result = $this->get_rows('recruitment_interview_applicant AS ria', ['p.username'], ['ria.interview_id' => $interviewId, 'ria.archive' => 0], $joins );
            $emails = [];
            if (!empty($result)) {
                foreach($result as $row) {
                    $emails[] = $row->username;
                }
            }
            return $emails;
        };
    }
}
