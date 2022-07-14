<?php

abstract class Enum {

    private $members = [];    

    public function __construct(array $members)
    {
        //validate that each emember is an object
        foreach($members as $member) {
            if (gettype($member) !== 'object') {
                throw new Exception("members of type 'Enum' could only be of type 'object'", 500);
            }
        }
        $this->setMembers($members);
    }

    /**
     * Get the value of members
     */ 
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set the value of members
     *
     * @return  self
     */ 
    public function setMembers(array $members)
    {
        $this->members = $members;

        return $this;
    }
}