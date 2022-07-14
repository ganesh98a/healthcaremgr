<?php

// require the autoloader
require_once APPPATH . '../vendor/fzaninotto/faker/src/autoload.php';
require_once APPPATH . 'Classes/participant/Participant.php';
require_once 'Random_gender.php';

use ParticipantClass\Participant;

class Participant_factory extends Participant
{
    public function __construct()
    {
        $faker = Faker\Factory::create();
        $faker->addProvider(new Faker\Provider\en_AU\PhoneNumber($faker));
        //$faker->addProvider(new Faker\Provider\en_AU\Address($faker));

        $relations = ['Brother', 'Sister', 'Father', 'Mother', 'Guardian'];
        $livingSituationCount = 7;
        $aboriginalTsiCount = 4;
        $ocDepartmentCount = 3;
        $gender = new Random_gender();
        $middleName = $faker->firstname($gender->toString());
        $referral = rand(0, 1);

        $this->setUserName($faker->username);
        $this->setFirstname($faker->firstname($gender->toString()));
        $this->setMiddlename($middleName);
        $this->setLastname($faker->lastname);
        // 1 in 10 participants will have their middlename as their preferred name
        $this->setPreferredname(rand(0, 1) === 0 ? $middleName : '');
        $this->setGender($gender->toInt());
        $this->setParticipantEmail($faker->email);
        $this->setParticipantPhone($faker->mobileNumber);

        $this->setPassword('summer11');
        $this->encryptPassword();
        $this->setDob($faker->dateTimeThisCentury->format('Y-m-d'));
        $this->setNdisNum(rand(100000000000000, 999999999999999));
        $this->setMedicareNum($faker->numerify('0000 ##### #'));
        $this->setCrnNum($faker->numerify('000 ### ###'));
        $this->setReferral($referral);

        if ($referral === 1) {
            $this->setParticipantRelation($relations[rand(0, count($relations) - 1)]);
            $this->setReferralFirstName($faker->firstname);
            $this->setReferralLastName($faker->lastname);
            $this->setReferralEmail($faker->email);
            $this->setReferralPhone($faker->mobileNumber);
        }

        $this->setLivingSituation(rand(1, $livingSituationCount));
        $this->setAboriginalTsi(rand(1, $aboriginalTsiCount));
        $this->setOcDepartments(rand(1, $ocDepartmentCount));
        $this->setCreated($faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d H:i:s'));
        // 1 in 49 participants will be inactive
        $this->setStatus(rand(0, 49) === 0 ? 0 : 1);
        // 1 in 10 participants will have portal access
        $this->setPortalAccess(rand(0, 9) === 0 ? 1 : 0);
        // 1 in 100 participants will be archived
        $this->setArchive(rand(0, 99) === 0 ? 1 : 0);
        $this->setHouseid(rand(1, 2));
    }
}
