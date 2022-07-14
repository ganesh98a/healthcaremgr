<?php

class Random_gender {
    // Selected gender
    private $gender;
    // Possible genders
    private $genders = ['', 'male', 'female'];

    public function __construct() {
        // Choose a random gender based on possible positions
        $this->gender = rand(1,2);
    }

    public function toInt(): int {
        return $this->gender;
    }

    public function toString(): string {
        return $this->genders[$this->gender];
    }
}