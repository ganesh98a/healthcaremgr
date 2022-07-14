<?php

// require the autoloader
require_once APPPATH . '../vendor/fzaninotto/faker/src/autoload.php';
require_once APPPATH . 'Classes/participant/ParticipantAddress.php';

/**
 * The address factory is not generating real address.
 */
class Participant_address_factory extends ParticipantAddress
{
    public function __construct($primary = 1)
    {
        $faker = Faker\Factory::create();
        $faker->addProvider(new Faker\Provider\en_AU\Address($faker));
        $siteCategoryCount = 7;

        $this->setStreet($faker->streetAddress);
        $this->setCity($faker->city);
        $this->setPostal($faker->postcode);
        $this->setState(rand(1, 8));
        $this->setLat('');
        $this->setLong('');
        $this->setSiteCategory(rand(1, $siteCategoryCount));
        $this->setPrimaryAddress($primary);
        $this->setArchive(0);
    }
}
