<?php

trait formCustomValidation {

    public function phone_number_check($sourceData, $fieldData = 'phone') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $empty_check_required = isset($fieldExport[1]) && !empty($fieldExport[1]) ? true : false;
        $msg_show = isset($fieldExport[2]) && !empty($fieldExport[2]) && gettype($fieldExport[2] == 'string') ? $fieldExport[2] : 'Please enter valid phone number';
        if (!empty($data)) {
            $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];
            if ($empty_check_required && isset($data[$field]) && empty($data[$field])) {
                $this->form_validation->set_message('phone_number_check', 'Phone number can not be empty');
                return false;
            }
            if (isset($data[$field]) && !empty($data[$field]) && !preg_match(PHONE_REGEX_KEY, $data[$field], $match)) {
                $this->form_validation->set_message('phone_number_check', $msg_show);
                return false;
            }
        }
        return true;
    }

    public function suburb_check($sourceData, $fieldData = 'value') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $empty_check_required = isset($fieldExport[1]) && !empty($fieldExport[1]) ? true : false;
        $msg_show = isset($fieldExport[2]) && !empty($fieldExport[2]) && gettype($fieldExport[2] == 'string') ? $fieldExport[2] : 'Suburb can not be empty';
        $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];

        if ($empty_check_required && (!isset($data[$field]) || empty($data[$field]))) {
            $this->form_validation->set_message('suburb_check', $msg_show);
            return false;
        }
        return true;
    }

    public function check_site_not_alloted($sourceData, $fieldData = 'value') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $option_label = isset($fieldExport[1]) && !empty($fieldExport[1]) && gettype($fieldExport[1] == 'string') ? $fieldExport[1] : 'label';
        $empty_check_required = isset($fieldExport[2]) && !empty($fieldExport[2]) ? true : false;
        $msg_show = isset($fieldExport[3]) && !empty($fieldExport[3]) && gettype($fieldExport[3] == 'string') ? $fieldExport[3] : 'Please Select site again it alerady assign to other organization.';
        if (!empty($data)) {

            $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];
            $msg = isset($data[$option_label]) ? $data[$option_label] . ', ' : '';
            if ((!isset($data[$field])) || (isset($data[$field]) && empty($data[$field]))) {
                $this->form_validation->set_message('check_site_not_alloted', $msg . $msg_show);
                return false;
            }
            if (!is_numeric($data[$field])) {
                $this->form_validation->set_message('check_site_not_alloted', 'Something went wrong.');
                return false;
            }
            $res = $this->Basic_model->get_row('organisation_site', array('organisationId', 'id'), array('organisationId' => '0', 'archive' => '0', 'id' => $data[$field]));
            if (!$res) {
                $this->form_validation->set_message('check_site_not_alloted', $msg . $msg_show);
                return false;
            }
        } else if ($empty_check_required && (empty($data) || count($data) <= 0)) {
            $this->form_validation->set_message('check_site_not_alloted', 'Please Select atleast one site to attach.');
            return false;
        }
        return true;
    }

    public function multiple_phone_number_check($sourceData, $fieldData = 'sitePh,phone') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $fieldInsideIndex = $fieldExport[1];
        $empty_check_required = isset($fieldExport[2]) && !empty($fieldExport[2]) ? true : false;
        $msg_show = isset($fieldExport[3]) && !empty($fieldExport[3]) && gettype($fieldExport[3] == 'string') ? $fieldExport[3] : 'Please enter valid phone number';
        if (!empty($data)) {
            $data = gettype($data) == 'object' || gettype($data) == 'array' ? json_decode(json_encode($data), TRUE) : [];
            if ($empty_check_required && empty($data)) {
                $this->form_validation->set_message('multiple_phone_number_check', 'Phone number can not be empty.');
                return false;
            }
            if ($empty_check_required && isset($data[$field]) && empty($data[$field])) {
                $this->form_validation->set_message('multiple_phone_number_check', 'Phone number can not be empty.');
                return false;
            }
            $sts = false;
            foreach ($data[$field] as $row) {

                if (!isset($row[$fieldInsideIndex])) {
                    $this->form_validation->set_message('multiple_phone_number_check', 'Phone number can not be empty.');
                    $sts = true;
                    break;
                }
                if ($empty_check_required && empty($row[$fieldInsideIndex])) {
                    $this->form_validation->set_message('multiple_phone_number_check', 'Phone number can not be empty');
                    $sts = true;
                    break;
                }


                if (isset($row[$fieldInsideIndex]) && !empty($row[$fieldInsideIndex]) && !preg_match(PHONE_REGEX_KEY, $row[$fieldInsideIndex], $match)) {
                    $this->form_validation->set_message('multiple_phone_number_check', $msg_show);
                    $sts = true;
                    break;
                }
            }
            if ($sts) {
                return false;
            }
        }
        return true;
    }

    public function multiple_email_check($sourceData, $fieldData = 'siteMail,email') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $fieldInsideIndex = $fieldExport[1];
        $empty_check_required = isset($fieldExport[2]) && !empty($fieldExport[2]) ? true : false;
        $msg_show = isset($fieldExport[3]) && !empty($fieldExport[3]) && gettype($fieldExport[3] == 'string') ? $fieldExport[3] : 'Please enter valid phone number';
        if (!empty($data)) {
            $data = gettype($data) == 'object' || gettype($data) == 'array' ? json_decode(json_encode($data), TRUE) : [];
            if ($empty_check_required && empty($data)) {
                $this->form_validation->set_message('multiple_email_check', 'Email can not empty');
                return false;
            }
            if ($empty_check_required && isset($data[$field]) && empty($data[$field])) {
                $this->form_validation->set_message('multiple_email_check', 'Email can not empty');
                return false;
            }
            foreach ($data[$field] as $row) {
                if (!isset($row[$fieldInsideIndex])) {
                    $this->form_validation->set_message('multiple_email_check', 'Email can not empty');
                    return false;
                }
                if ($empty_check_required && empty($row[$fieldInsideIndex])) {
                    $this->form_validation->set_message('multiple_email_check', 'Email can not empty');
                    return false;
                }

                if (isset($row[$fieldInsideIndex]) && !empty($row[$fieldInsideIndex]) && !filter_var($row[$fieldInsideIndex], FILTER_VALIDATE_EMAIL)) {
                    $this->form_validation->set_message('multiple_email_check', $msg_show);
                    return false;
                }
            }
        }
        return true;
    }

    public function site_details_check($sourceData) {
        $data = $sourceData;
        if (!empty($data)) {
            $msg = ' can not empty';
            $data = gettype($data) == 'object' || gettype($data) == 'array' ? json_decode(json_encode($data), TRUE) : [];
            if ((isset($data['firstname']) && empty($data['firstname'])) || !isset($data['firstname'])) {
                $msg_show = 'First name';
                $this->form_validation->set_message('site_details_check', $msg_show . $msg);
                return false;
            }

            if ((isset($data['lastname']) && empty($data['lastname'])) || !isset($data['lastname'])) {
                $msg_show = 'Last name';
                $this->form_validation->set_message('site_details_check', $msg_show . $msg);
                return false;
            }

            if ((isset($data['position']) && empty($data['position'])) || !isset($data['position'])) {
                $msg_show = 'Position';
                $this->form_validation->set_message('site_details_check', $msg_show . $msg);
                return false;
            }

            if ((isset($data['department']) && empty($data['department'])) || !isset($data['department'])) {
                $msg_show = 'Department';
                $this->form_validation->set_message('site_details_check', $msg_show . $msg);
                return false;
            }
        } else {
            $msg_show = 'Required field ';
            $this->form_validation->set_message('site_details_check', $msg_show . $msg);
            return false;
        }
        return true;
    }

    /**
     * check site_name is already exists or not in the database table "tbl_organisation_site" 
     * @param	string	$sourceData	the site name.
     * @param	int	$siteIdValue the site id. Can be intager
     * @return	bool
     */
    public function check_unique_site_name($sourceData, $siteIdValue = 0) {
        $sourceData = trim($sourceData);
        if (empty($sourceData)) {
            $msg = 'Title can not empty.';
            $this->form_validation->set_message('check_unique_site_name', $msg);
            return false;
        }
        if (!is_string($sourceData)) {
            $msg = 'Title Only allow alphanumeric characters.';
            $this->form_validation->set_message('check_unique_site_name', $msg);
            return false;
        }

        $selectColumn = ['id', 'organisationId'];
        $siteIdValue = (int) $siteIdValue;
        $whereCondition = ['site_name' => $sourceData, 'id !=' => $siteIdValue];
        $table = 'organisation_site';
        $res = $this->Basic_model->get_row($table, $selectColumn, $whereCondition);
        if ($res) {
            $msg = 'Site title already exists.';
            $this->form_validation->set_message('check_unique_site_name', $msg);
            return false;
        }
        return true;
    }

    public function kin_details_check($sourceData, $msgStart = 'Next of kin ') {
        $data = $sourceData;

        if (!empty($data)) {
            $msg = ' can not empty.';
            $data = gettype($data) == 'object' || gettype($data) == 'array' ? json_decode(json_encode($data), TRUE) : [];
            if ((isset($data['firstname']) && empty($data['firstname'])) || !isset($data['firstname'])) {
                $msg_show = !empty($msgStart) ? '' : 'name';
                $this->form_validation->set_message('kin_details_check', ucfirst($msgStart . $msg_show . $msg));
                return false;
            }

            /* if((isset($data['lastname']) && empty($data['lastname']))) {
              $msg_show = 'Last name';
              $this->form_validation->set_message('kin_details_check', ucfirst($msgStart.$msg_show.$msg));
              return false;
              } */

            if ((isset($data['relation']) && empty($data['relation'])) || !isset($data['relation'])) {
                $msg_show = 'relation';
                $this->form_validation->set_message('kin_details_check', ucfirst($msgStart . $msg_show . $msg));
                return false;
            }

            if ((isset($data['kin_email']) && empty($data['kin_email'])) || !isset($data['kin_email'])) {
                $msg_show = 'kin_email';
                $this->form_validation->set_message('kin_details_check', ucfirst($msgStart . $msg_show . $msg));
                return false;
            } else if (isset($data['kin_email']) && !empty($data['kin_email']) && !filter_var($data['kin_email'], FILTER_VALIDATE_EMAIL)) {
                $msg_show = 'Email is not valid.';
                $this->form_validation->set_message('kin_details_check', ucfirst($msgStart . $msg_show));
                return false;
            }

            if ((isset($data['phone']) && empty($data['phone'])) || !isset($data['phone'])) {
                $msg_show = 'phone';
                $this->form_validation->set_message('kin_details_check', ucfirst($msgStart . $msg_show . $msg));
                return false;
            }
        } else {
            $msg_show = 'Required field ';
            $this->form_validation->set_message('kin_details_check', ucfirst($msg_show . $msg));
            return false;
        }
        return true;
    }

    public function postal_code_check($sourceData, $fieldData = 'postal') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $msgShow = isset($fieldExport[1]) && !empty($fieldExport[1]) && gettype($fieldExport[1] == 'string') ? $fieldExport[1] : 'Please enter valid 4 digit postcode number.';
        $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];

        if (isset($data[$field]) && !empty($data[$field])) {
            if (!preg_match(POSTCODE_AU_REGEX_KEY, $data[$field], $match)) {
                $this->form_validation->set_message('postal_code_check', ucfirst($msgShow));
                return false;
            }
        } elseif (!isset($data[$field]) || empty($data[$field])) {
            $msg_show = 'Postcode can not be empty.';
            $this->form_validation->set_message('postal_code_check', ucfirst($msg_show));
            return false;
        } else {
            $msg_show = 'Postcode can not be empty.';
            $this->form_validation->set_message('postal_code_check', ucfirst($msg_show));
            return false;
        }
        return true;
    }

    public function date_check($sourceData, $fieldData = 'dob') {
        $data = $sourceData;
        $allowDateCheck = ['current', 'before', 'after', 'date_check_greaterthenotequal_other_field', 'date_check_lessthenotequal_other_field'];
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $msgShow = isset($fieldExport[2]) && !empty($fieldExport[2]) && gettype($fieldExport[2] == 'string') ? $fieldExport[2] : 'Date is not grater than current date.';
        $type = isset($fieldExport[1]) && !empty($fieldExport[1]) && gettype($fieldExport[1] == 'string') && in_array(strtolower($fieldExport[1]), $allowDateCheck) ? trim(strtolower($fieldExport[1])) : 'current';
        $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];
        $otherDate = isset($fieldExport[3]) && !empty($fieldExport[3]) && gettype($fieldExport[3] == 'string') && validateDateWithFormat($fieldExport[3], DB_DATE_FORMAT) ? $fieldExport[3] : date(DB_DATE_FORMAT);
        if (isset($data[$field]) && !empty($data[$field])) {

            if ($type == 'current' && strtotime(date(DB_DATE_FORMAT)) < strtotime(DateFormate($data[$field], DB_DATE_FORMAT))) {
                $this->form_validation->set_message('date_check', ucfirst($msgShow));
                return false;
            } elseif ($type == 'before' && strtotime(date(DB_DATE_FORMAT)) <= strtotime(DateFormate($data[$field], DB_DATE_FORMAT))) {
                $this->form_validation->set_message('date_check', ucfirst($msgShow));
                return false;
            } elseif ($type == 'after' && strtotime(date(DB_DATE_FORMAT)) >= strtotime(DateFormate($data[$field], DB_DATE_FORMAT))) {
                $this->form_validation->set_message('date_check', ucfirst($msgShow));
                return false;
            } else if ($type == 'date_check_greaterthenotequal_other_field' && strtotime(DateFormate($data[$field], DB_DATE_FORMAT)) <= strtotime(DateFormate($otherDate, DB_DATE_FORMAT))) {
                $this->form_validation->set_message('date_check', ucfirst($msgShow));
                return false;
            } else if ($type == 'date_check_lessthenotequal_other_field' && strtotime(DateFormate($data[$field], DB_DATE_FORMAT)) >= strtotime(DateFormate($otherDate, DB_DATE_FORMAT))) {
                $this->form_validation->set_message('date_check', ucfirst($msgShow));
                return false;
            }
        } elseif (!isset($data[$field]) || empty($data[$field])) {
            $msg_show = 'date can not be empty.';
            $this->form_validation->set_message('date_check', ucfirst($msg_show));
            return false;
        } else {
            $msg_show = 'date can not be empty.';
            $this->form_validation->set_message('date_check', ucfirst($msg_show));
            return false;
        }
        return true;
    }

    public function check_valid_email_address($sourceData, $fieldData) {
        if (!empty($sourceData->{$fieldData}) || (is_array($sourceData) && isset($sourceData[$fieldData])) || (!is_array($sourceData) && isset($sourceData))) {
            if (!empty($sourceData->{$fieldData}))
                $email = $sourceData->{$fieldData};
            else if (is_array($sourceData) && isset($sourceData[$fieldData]))
                $email = $sourceData[$fieldData];
            else if (!is_array($sourceData) && isset($sourceData))
                $email = $sourceData;
            
            if(empty($email)) {
                $this->form_validation->set_message('check_valid_email_address', "Please enter email address");
                return false;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->form_validation->set_message('check_valid_email_address', "Please enter valid email address.");
                return false;
            } else {
                return true;
            }
        } else {
            $this->form_validation->set_message('check_valid_email_address', "Please enter email address");
            return false;
        }
    }

    public function check_recruitment_applicant_references($sourceData) {
        if (!empty($sourceData)) {
            if (empty($sourceData->name)) {
                $this->form_validation->set_message('check_recruitment_applicant_references', "Please fill reference name.");
                return false;
            }
            if (empty($sourceData->phone)) {
                $this->form_validation->set_message('check_recruitment_applicant_references', "Please fill reference phone.");
                return false;
            }
            if (empty($sourceData->email)) {
                $this->form_validation->set_message('check_recruitment_applicant_references', "Please fill reference email.");
                return false;
            }

            return true;
        } else {
            $this->form_validation->set_message('check_recruitment_applicant_references', "Please fill reference details.");
            return false;
        }
    }

    public function check_valid_google_address($sourceData) {
        if (!empty($sourceData) && !empty($sourceData->street) && !empty($sourceData->city) && !empty($sourceData->state) && !empty($sourceData->postal)) {
            $complete_address = $sourceData->street . " " . $sourceData->city . " " . $sourceData->state . " " . $sourceData->postal;
            $google_res = getLatLong($complete_address);
            if (!$google_res) {
                $this->form_validation->set_message('check_valid_google_address', "Please valid complete address");
                return false;
            }
        }
    }

    /**
     * validates the questions and their given answers array through validator
     */
    public function validate_submitted_answer($subdata) {

        if (!isset($subdata->question_answers) || empty($subdata->question_answers)) {
            $this->form_validation->set_message("validate_submitted_answer", "Please provide atleast one answer");
            return false;
        }
        $question_answers_validate = $subdata->question_answers;

        $form_applicant_id = null;
        if (isset($subdata->form_applicant_id) && !empty($subdata->form_applicant_id))
            $form_applicant_id = $subdata->form_applicant_id;
        $val_ok = true;

        // pr($question_answers_validate);

        foreach ($question_answers_validate as $index => $value) {
            $que_details = $this->Recruitmentform_model->get_question_details($value['id'], $form_applicant_id);
            $answer_details = $this->Recruitmentform_model->get_answer_details($value['id'], true, $form_applicant_id);

            if (!$que_details || !$answer_details)
                continue;

            $no_answer = false;

            # if question is mandatory
            if ($que_details->is_required == 1) {
                if (isset($value['answer_id']))
                    $value['answer_id'] = array_values(array_filter($value['answer_id']));

                # questions other than short answer
                if ($que_details->question_type != 4 && (!isset($value['answer_id']) || !$value['answer_id']))
                    $no_answer = true;
                # answer id has to be from question's answers
                else if ($que_details->question_type != 4 && $value['answer_id'] && !serach_double_array_in_double_array($answer_details, 'answer_id', $value['answer_id']))
                    $no_answer = true;
                # short answer has to be provided
                else if ($que_details->question_type == 4 && (!isset($value['answer_text']) || !$value['answer_text']))
                    $no_answer = true;

                if ($no_answer) {
                    $this->form_validation->set_message("validate_submitted_answer", "Please provide answer for question: " . $que_details->question);
                    $val_ok = false;
                }
            }
        }

        return $val_ok;
    }

    /**
     * checks in the person db table that provided email addresses exist or not
     */
    function check_lead_person_emailaddress_already_exist($emailData = [], $id = 0) {
        if (!empty($emailData)) {
            $emails = (array) $emailData;
            foreach ($emails as $val) {
                $result = $this->Lead_model->check_person_duplicate_email($val, $id);
                if (!empty($result)) {
                    $this->form_validation->set_message('check_lead_person_emailaddress_already_exist', 'this ' . $result['email'] . ' Email address Already Exist');
                    return false;
                }
                return true;
            }
        }
    }

    /**
     * while bulk importing applicants, making sure flag reason and notes both are provided
     */
    public function check_flag_and_flagreason($sourceData) {
        if (!empty($sourceData)) {
            if (empty($sourceData->flagged_reason_title) && empty($sourceData->flagged_reason_notes))
                return true;
        }

        if (!empty($sourceData)) {
            if (empty($sourceData->flagged_reason_title)) {
                $this->form_validation->set_message('check_flag_and_flagreason', "Please enter reason title for flagging");
                return false;
            }
            if (empty($sourceData->flagged_reason_notes)) {
                $this->form_validation->set_message('check_flag_and_flagreason', "Please enter notes for flagging");
                return false;
            }
        }
    }

    /**
     * checks for any given field of a complete address (street, state, suburb and postcode)
     * if any of them is provided then all others should also be provided
     */
    public function check_recruitment_applicant_address_ifadded($sourceData) {

        if (!empty($sourceData)) {
            if (empty($sourceData->street) && empty($sourceData->city) && empty($sourceData->state) && empty($sourceData->postal))
                return true;
        }

        if (!empty($sourceData)) {
            if (empty($sourceData->street)) {
                $this->form_validation->set_message('check_recruitment_applicant_address_ifadded', "Please fill street");
                return false;
            }
            if (empty($sourceData->city)) {
                $this->form_validation->set_message('check_recruitment_applicant_address_ifadded', "Please fill suburb.");
                return false;
            }
            if (empty($sourceData->state)) {
                $this->form_validation->set_message('check_recruitment_applicant_address_ifadded', "Please select state.");
                return false;
            }
            if (empty($sourceData->postal)) {
                $this->form_validation->set_message('check_recruitment_applicant_address_ifadded', "Please fill postal.");
                return false;
            }
            if (!empty($sourceData->postal) && $sourceData->postal == "0") {
                $this->form_validation->set_message('check_recruitment_applicant_address_ifadded', "Please fill valid postal code.");
                return false;
            }

            return true;
        } else {
            $this->form_validation->set_message('check_recruitment_applicant_address_ifadded', "Please fill address details");
            return false;
        }
    }

    public function check_suburb_state_exists($sourceData) {
        if (empty($sourceData) || empty($sourceData->city) || empty($sourceData->postal) || empty($sourceData->state)) {
            return true;
        }
        // pr($sourceData,0);
        $this->db->select('id');
        $this->db->from('tbl_suburb_state as ss');
        $this->db->where('ss.suburb', $sourceData->city);
        $this->db->where('ss.state', $sourceData->state);
        $this->db->where('ss.postcode', $sourceData->postal);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if ($query->result())
            return true;
        else {
            $this->form_validation->set_message('check_suburb_state_exists', "Address combination (suburb, state and postcode) not found");
            return false;
        }
    }

    public function check_recruitment_applicant_address($sourceData) {
        if (!empty($sourceData)) {
            if (empty($sourceData->street)) {
                $this->form_validation->set_message('check_recruitment_applicant_address', "Please fill street");
                return false;
            }
            if (empty($sourceData->city)) {
                $this->form_validation->set_message('check_recruitment_applicant_address', "Please fill suburb.");
                return false;
            }
            if (empty($sourceData->state)) {
                $this->form_validation->set_message('check_recruitment_applicant_address', "Please select state.");
                return false;
            }
            if (empty($sourceData->postal)) {
                $this->form_validation->set_message('check_recruitment_applicant_address', "Please fill postal.");
                return false;
            }
            if (!empty($sourceData->postal) && $sourceData->postal == "0") {
                $this->form_validation->set_message('check_recruitment_applicant_address', "Please fill valid postal code.");
                return false;
            }

            if (!preg_match(POSTCODE_AU_REGEX_KEY, $sourceData->postal, $match)) {
                $this->form_validation->set_message('check_recruitment_applicant_address', "Please fill valid postal code.");
                return false;
            }

            return true;
        } else {
            $this->form_validation->set_message('check_recruitment_applicant_address', "Please fill address details");
            return false;
        }
    }

    public function check_applicant_required_checks($sourceData) {
        $sourceData = json_decode($sourceData);

        $this->load->library('ApplicantRequiredValidation');

        if (!empty($sourceData->type_of_checks)) {
            $rule = [];
            foreach ($sourceData->type_of_checks as $val) {
                $rule[$val] = true;
            }

            $extra_params = isset($sourceData->extra_params) ? $sourceData->extra_params : [];

            $this->applicantrequiredvalidation->setApplicantId($sourceData->applicant_id);
            if (isset($sourceData->status)) {
                $this->applicantrequiredvalidation->setStatus($sourceData->status);
            }            
            $this->applicantrequiredvalidation->setValidationRule($rule);
            $res = $this->applicantrequiredvalidation->check_applicant_required_checks($extra_params);

            if (!$res['status']) {
                $this->form_validation->set_message('check_applicant_required_checks', implode(', ', $res['error']));
                return false;
            } else {
                return true;
            }
        }
    }

    public function check_applicant_duplicate_profile($sourceData, $applicantId) {
        // get only pending status applicant whose case is pending
        $res = $this->Recruitment_applicant_model->check_any_duplicate_applicant($applicantId, 1);

        if (!empty($res)) {
            $this->form_validation->set_message('check_applicant_duplicate_profile', "Already duplicate applicant exist");
            return false;
        } else {
            return true;
        }
    }

    public function check_email_already_exist_to_another_applicant($email, $applicantId) {
        $this->load->model('recruitment/Recruitment_applicant_model');
        if (!empty($email->email)) {
            $email = $email->email;
        } else {
            $email = $email;
        }
        $res = $this->Recruitment_applicant_model->check_email_already_exist_to_another_applicant($applicantId, $email);

        if (!empty($res)) {
            $this->form_validation->set_message('check_email_already_exist_to_another_applicant', "This email already exist in another applicant");
            return false;
        } else {
            return true;
        }
    }

    public function check_phone_already_exist_to_another_applicant($phone, $applicantId) {
        if (!empty($phone->phone)) {
            $phone = $phone->phone;
        } else {
            $phone = $phone;
        }

        if (empty($phone)) {
            $this->form_validation->set_message('check_phone_already_exist_to_another_applicant', "Phone number can not be blank");
            return false;
        }
        if (!preg_match(PHONE_REGEX_KEY, $phone, $match)) {
            $this->form_validation->set_message('check_phone_already_exist_to_another_applicant', 'Please enter a valid phone number');
            return false;
        }

        $res = $this->Recruitment_applicant_model->check_phone_already_exist_to_another_applicant($applicantId, $phone);

        if (!empty($res)) {
            $this->form_validation->set_message('check_phone_already_exist_to_another_applicant', "This phone number already exist in another applicant");
            return false;
        } else {
            return true;
        }
    }

    public function check_applicant_assign_document_category($dummy, $sourceData) {
        $sourceData = (json_decode($sourceData, true));

        if (!empty($sourceData)) {
            $checked = array_filter(array_column($sourceData, 'assined'));

            if (empty($checked)) {
                $this->form_validation->set_message('check_applicant_assign_document_category', "Please select at least one document");
                return false;
            } else {
                return true;
            }
        } else {
            $this->form_validation->set_message('check_applicant_assign_document_category', "Please select at least one document");
            return false;
        }
    }

    public function check_payroll_exemption_exists_between_date_range($sourceData, $fieldData = 'orgId') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $fromDate = isset($fieldExport[1]) && !empty($fieldExport[1]) && gettype($fieldExport[1]) == 'string' && validateDateWithFormat($fieldExport[1], DB_DATE_FORMAT) ? $fieldExport[1] : '';
        $toDate = isset($fieldExport[2]) && !empty($fieldExport[2]) && gettype($fieldExport[2]) == 'string' && validateDateWithFormat($fieldExport[2], DB_DATE_FORMAT) ? $fieldExport[2] : '';
        $msgShow = isset($fieldExport[3]) && !empty($fieldExport[3]) && gettype($fieldExport[3]) == 'string' ? $fieldExport[3] : ' already exists for give valid from and valid until date range.';
        $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];
        if (isset($data[$field]) && !empty($data[$field]) && !empty($fromDate) && !empty($toDate)) {
            $this->load->model(['finance/Finanace_shift_payroll_model' => 'Finanace_shift_payroll']);
            $res = $this->Finanace_shift_payroll->check_payroll_exemption_exists_between_date_range($data[$field], $fromDate, $toDate);

            if ($res['status'] && isset($res['data_count']) && !empty($res['data_count'])) {
                $this->form_validation->set_message('check_payroll_exemption_exists_between_date_range', '%s ' . $msgShow);
                return false;
            } else if (!$res['status'] && isset($res['msg'])) {
                $this->form_validation->set_message('check_payroll_exemption_exists_between_date_range', '%s ' . $res['msg']);
                return false;
            } else {
                return true;
            }
        } else {
            $this->form_validation->set_message('check_payroll_exemption_exists_between_date_range', '%s ' . 'Invalid request.');
            return false;
        }
        return true;
    }

    public function valid_email_check($sourceData, $fieldData = 'email') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);

        $field = $fieldExport[0];
        $empty_check_required = isset($fieldExport[1]) && !empty($fieldExport[1]) ? true : false;
        $msg_show = isset($fieldExport[2]) && !empty($fieldExport[2]) && gettype($fieldExport[2] == 'string') ? $fieldExport[2] : 'Please enter valid email address';
        if (!empty($data)) {
            $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];
            if ($empty_check_required && isset($data[$field]) && empty($data[$field])) {
                $this->form_validation->set_message('valid_email_check', 'email can not be empty');
                return false;
            }
            if (isset($data[$field]) && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->form_validation->set_message('valid_email_check', $msg_show);
                return false;
            }
        }
        return true;
    }

    public function check_credit_notes_invoice_and_amount_assigned_to_user_from($sourceData, $fieldData = 'orgId') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $invoice_for = isset($fieldExport[1]) && !empty($fieldExport[1]) ? $fieldExport[1] : '';
        $booked_by = isset($fieldExport[2]) && !empty($fieldExport[2]) ? $fieldExport[2] : '';
        $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];
        $invoiceIds = array_column($data, 'invoice_id');
        $amounts = array_column($data, $field);
        if (!empty($invoiceIds) && !empty($amounts) && count($invoiceIds) == count($amounts)) {
            $this->load->model(['finance/Finance_invoice_model' => 'Finance_invoice']);
            $invoiceData = $this->Finance_invoice->get_invoice_data_with_amount(['invoice_for' => $invoice_for, 'booked_by' => $booked_by, 'ids' => $invoiceIds]);
            $invoiceData = pos_index_change_array_data($invoiceData, 'id');
            if (count($invoiceIds) != count($invoiceData)) {
                $this->form_validation->set_message('check_credit_notes_invoice_and_amount_assigned_to_user_from', '%s ' . 'Invalid information from invoice used credit avalable amount.');
                return false;
            } else if (count($invoiceIds) == count($invoiceData)) {
                $errors = [];
                foreach ($data as $key => $row) {
                    $fromAmount = $row->amount;
                    $invoiceAvailabelAmount = isset($invoiceData[$row->invoice_id]) ? $invoiceData[$row->invoice_id]['total'] - $invoiceData[$row->invoice_id]['amount_used_from'] : 0;
                    if ($fromAmount < 0 || $invoiceAvailabelAmount < $fromAmount) {
                        $errors[] = $invoiceData[$row->invoice_id]['invoice_number'] . ' apply Amount not greater then availabel amount or amount should be more then 0';
                        continue;
                    }
                }
                if (!empty($errors)) {
                    $this->form_validation->set_message('check_credit_notes_invoice_and_amount_assigned_to_user_from', implode('.,', $errors));
                    return false;
                }
            }
        } else {
            $this->form_validation->set_message('check_credit_notes_invoice_and_amount_assigned_to_user_from', '%s ' . 'Invalid request.');
            return false;
        }
        return true;
    }

    public function check_credit_notes_invoice_and_amount_assigned_to_user_applied($sourceData, $fieldData = 'orgId') {
        $data = $sourceData;
        $fieldExport = explode(',', $fieldData);
        $field = $fieldExport[0];
        $invoice_for = isset($fieldExport[1]) && !empty($fieldExport[1]) ? $fieldExport[1] : '';
        $booked_by = isset($fieldExport[2]) && !empty($fieldExport[2]) ? $fieldExport[2] : '';
        $data = gettype($data) == 'object' || gettype($data) == 'array' ? (array) $data : [$field => $data];
        $invoiceIds = array_column($data, 'invoice_id_selected');
        $amounts = array_column($data, $field);

        if (!empty($invoiceIds) && !empty($amounts) && count($invoiceIds) == count($amounts)) {
            $this->load->model(['finance/Finance_invoice_model' => 'Finance_invoice']);
            $invoiceData = $this->Finance_invoice->get_invoice_data_with_amount(['invoice_for' => $invoice_for, 'booked_by' => $booked_by, 'ids' => $invoiceIds]);
            $invoiceData = pos_index_change_array_data($invoiceData, 'id');
            if (count($invoiceIds) != count($invoiceData)) {
                $this->form_validation->set_message('check_credit_notes_invoice_and_amount_assigned_to_user_applied', '%s ' . 'Invalid information from invoice used credit apply amount.');
                return false;
            } else if (count($invoiceIds) == count($invoiceData)) {
                $errors = [];
                foreach ($data as $key => $row) {
                    $fromAmount = $row->invoice_apply_amount;
                    $invoiceAvailabelAmount = isset($invoiceData[$row->invoice_id_selected]) ? $invoiceData[$row->invoice_id_selected]['total'] - $invoiceData[$row->invoice_id_selected]['amount_used_to'] : 0;
                    if ($fromAmount < 0 || $invoiceAvailabelAmount < $fromAmount) {
                        $errors[] = $invoiceData[$row->invoice_id_selected]['invoice_number'] . ' applied amount not greater then invoice amount or amount should be more then 0.';
                        continue;
                    }
                }
                if (!empty($errors)) {
                    $this->form_validation->set_message('check_credit_notes_invoice_and_amount_assigned_to_user_applied', implode('.,', $errors));
                    return false;
                }
            }
        } else {
            $this->form_validation->set_message('check_credit_notes_invoice_and_amount_assigned_to_user_applied', '%s ' . 'Invalid request.');
            return false;
        }
        return true;
    }

    public function check_house_contact_details($field, $contact_details) {

        $contact_details = json_decode($contact_details);
        $error = [];
        if (!empty($contact_details)) {
            foreach ($contact_details as $val) {
                if (empty($val->firstname)) {
                    $error[] = $field . ' firstname field is required';
                }

                if (!empty($val->contact_phone)) {
                    foreach ($val->contact_phone as $ph) {
                        if (empty($ph->phone)) {
                            $error[] = $field . ' phone number field is required';
                        }
                    }
                } else {
                    $error[] = $field . ' phone number field is required';
                }

                if (!empty($val->contact_email)) {
                    foreach ($val->contact_email as $ph) {
                        if (empty($ph->email)) {
                            $error[] = $field . ' email address field is required';
                        } elseif (!filter_var($ph->email, FILTER_VALIDATE_EMAIL)) {
                            $error[] = $field . ' ' . $ph->email . ' email address is not valid email';
                        }
                    }
                } else {
                    $error[] = $field . ' email address field is required';
                }
            }
        }

        if (!empty($error)) {
            $this->form_validation->set_message('check_house_contact_details', implode('., ', $error));
            return false;
        }

        return true;
    }

    public function check_shift_address($address, $param_type) {
        if (!empty($address)) {
            if (empty($address->street)) {
                $this->form_validation->set_message('check_shift_address', 'Street can not be empty');
                return false;
            } elseif (empty($address->suburb)) {
                $this->form_validation->set_message('check_shift_address', 'Suburb can not be empty');
                return false;
            } elseif (empty($address->state)) {
                $this->form_validation->set_message('check_shift_address', 'State can not be empty');
                return false;
            } elseif (empty($address->postal)) {
                $this->form_validation->set_message('check_shift_address', 'Postal can not be empty');
                return false;
            }
        } else {
            $this->form_validation->set_message('check_shift_address', 'Address can not empty');
            return false;
        }

        return true;
    }

    /*
     * Check min and max value of input
     * */

    public function check_min_max_number($input_value) {
        $input_value = (int) $input_value;
        if (empty($input_value)) {
            $msg = 'Count of Individual Interview is required.';
            $this->form_validation->set_message('check_min_max_number', $msg);
            return false;
        }

        if (!in_array($input_value, range(1, 3))) {
            $msg = 'Value Individual Interview count is between 1 to 3.';
            $this->form_validation->set_message('check_min_max_number', $msg);
            return false;
        }
        return true;
    }

    /*
     * its use for check valid address of organisation
     * 
     * @params $address
     * 
     * return type boolean
     */

    function check_string_google_address_is_valid($address) {
        if (!empty($address)) {
            $addr = devide_google_or_manual_address($address);
            $wrong_address_standard = false;

            if (!$addr["street"]) {
                $wrong_address_standard = true;
            } elseif (!$addr["suburb"]) {
                $wrong_address_standard = true;
            } elseif (!$addr["state"]) {
                $wrong_address_standard = true;
            } else if (!$addr["postcode"]) {
                $wrong_address_standard = true;
            }

            if ($wrong_address_standard) {
                $this->form_validation->set_message('check_string_google_address_is_valid', 'Please provide valid address like street, suburb state postcode');

                return false;
            }

            return true;
        }
    }

}
