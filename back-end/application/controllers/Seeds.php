<?php

require_once APPPATH . '../vendor/fzaninotto/faker/src/autoload.php';
require_once APPPATH . 'factories/Participant_factory.php';
require_once APPPATH . 'factories/Participant_address_factory.php';
require_once APPPATH . 'factories/Participant_care_requirement_factory.php';
require_once APPPATH . 'factories/Participant_care_not_tobook_factory.php';

class Seeds extends CI_Controller
{
    /**
     * An index of primary keys saved to memory for random access.
     */
    private $primary_keys = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function all(int $participantCount)
    {
        $this->participant($pariticipantCount);
    }

    /**
     * Seed Participants.
     */
    public function participant(int $pariticipantCount = 10)
    {
        $participants = [];
        $tables = [
            'participant',
            'participant_address',
            'participant_care_requirement',
            'participant_care_not_tobook',
        ];

        $this->clearTables($tables);

        for ($i = 0; $i < $pariticipantCount; ++$i) {
            $participant = new Participant_factory();
            $participantId = $participant->AddParticipant();
            $this->primary_keys['participant'][] = $participantId;

            $participantAddress = new Participant_address_factory();
            $participantAddress->setParticipantid($participantId);
            $participantAddressId = $participantAddress->addParticipantAddress();

            $participantCareRequirement = new Participant_care_requirement_factory();
            $participantCareRequirement->setParticipantid($participantId);
            $participantCareRequirement->create_care_requirement();

            // A participant will have anywhere between 1 and 7 carers not to book
            $careNotToBookCount = rand(1, 7);
            for ($j = 1; $j < $careNotToBookCount; ++$j) {
                $participantCareNotToBook = new Participant_care_not_tobook_factory();
                $participantCareNotToBook->setParticipantid($participantId);
                $participantCareNotToBook->save();
            }


        }
    }

    /**
     * Empty tables in database and add the table name as the first index of the primary_keys variable.
     */
    private function clearTables(array $tables)
    {
        foreach ($tables as $table) {
            $this->db->empty_table(TBL_PREFIX . $table);
            $this->primary_keys[$table] = [];
        }
    }

    /**
     * Return a random id from a table in the primary_keys variable.
     */
    private function getRandomId(string $table)
    {
        $random_key = array_rand($this->primary_keys[$table]);

        return $this->primary_keys[$table][$random_key];
    }
}
