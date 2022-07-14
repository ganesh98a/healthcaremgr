<?php

/**
 * Carers not to book for a participant.
 *
 * @author YDT <yourdevelopmentteam.com.au>
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ParticipantCareNotToBook
{

    private $participantid;
    private $gender;
    private $ethnicity;
    private $religious;

    public function getParticipantid(): int
    {
        return $this->participantid;
    }

    public function setParticipantid(int $participantid)
    {
        $this->participantid = $participantid;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function setGender(int $gender) 
    {
        $this->gender = $gender;
    }

    public function getEthnicity(): int 
    {
        return $this->ethnicity;
    }

    public function setEthnicity(int $ethnicity)
    {
        $this->ethnicity = $ethnicity;
    }

    public function getReligious(): int
    {
        return $this->religious;
    }

    public function setReligious(int $religious)
    {
        $this->religious = $religious;
    }

    public function save()
    {
        $CI = &get_instance();
        $CI->load->model('Participant/Participant_care_not_tobook_model');

        return $CI->Participant_care_not_tobook_model->create($this);
    }
}
