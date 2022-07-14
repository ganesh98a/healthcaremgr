<?php

// require the autoloader
require_once APPPATH . '../vendor/fzaninotto/faker/src/autoload.php';
require_once APPPATH . 'Classes/participant/ParticipantCareRequirement.php';

/**
 * The address factory is not generating real address.
 */
class Participant_care_requirement_factory extends ParticipantCareRequirement
{
    private $numberOfLanguages = 11;
    private $otherLanguagePosition = 11;

    public function __construct()
    {
        $faker = Faker\Factory::create();
        $faker->addProvider(new Faker\Provider\Lorem($faker));
        $key = $faker->numerify('CREQ######');
        $english = rand(1, 2);
        $preferredLanguage = $english === 1 ? 1 : rand(1, $this->numberOfLanguages);
        $otherLanguage = $preferredLanguage === $this->otherLanguagePosition ? $faker->word : '';

        // 1 in 5 chance of other fields being populated
        $requiresAssistanceOther = rand(1, 1) === 1 ? "$key: " . $faker->sentence : '';
        $supportRequireOther = rand(1, 1) === 1 ? "$key: " . $faker->sentence : '';

        $this->setDiagnosisPrimary("Primary Diagnosis $key: " . $faker->paragraph);
        $this->setDiagnosisSecondary("Secondary Diagnosis $key: " . $faker->paragraph);
        $this->setParticipantCare("Participant Care $key: " . $faker->paragraph);
        $this->setCognition(rand(1, 2));
        $this->setCommunication(rand(1, 2));
        $this->setEnglish($english);
        $this->setPreferredLanguage($preferredLanguage);
        $this->setPreferredLanguageOther($otherLanguage);
        $this->setLinguisticInterpreter(rand(1, 2));
        $this->setHearingInterpreter(rand(1, 2));
        $this->setRequireAssistanceOther($requiresAssistanceOther);
        $this->setSupportRequireOther($supportRequireOther);
    }
}
