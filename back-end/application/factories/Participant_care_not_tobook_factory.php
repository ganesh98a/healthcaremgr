<?php

// require the autoloader
require_once APPPATH . '../vendor/fzaninotto/faker/src/autoload.php';
require_once APPPATH . 'Classes/participant/ParticipantCareNotToBook.php';

class Participant_care_not_tobook_factory extends ParticipantCareNotToBook
{
    private $genders = 2;
    private $ethnicities = 8;
    private $religions = 9;

    public function __construct()
    {
        $faker = Faker\Factory::create();

        $this->setGender(rand(1, $this->genders));
        $this->setEthnicity(rand(1, $this->ethnicities));
        $this->setReligious(rand(1, $this->religions));
    }
}
