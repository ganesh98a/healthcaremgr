<?php

namespace location;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use location\Geolocation;

require_once APPPATH . 'Classes/location/Geolocation.php';

class Address extends Geolocation
{
    /**
	 * @var street
     * @access private
     * @vartype: int
     */
    private $street;
    private $street_dirty;

    /**
	 * @var city
     * @access private
     * @vartype: varchar
     */
    private $city;
    private $city_dirty;

    /**
	 * @var postal
     * @access private
     * @vartype: varchar
     */
    private $postal;
    private $postal_dirty;

    /**
	 * @var state
     * @access private
     * @vartype: varchar
     */
    private $state;
    private $state_dirty;

    /**
	 * @var primary_address
     * @access private
     * @vartype: varchar
     */
    private $primary_address;
    private $primary_address_dirty;

    /**
	 * @function getStreet
	 * @access public
	 * @return $street varchar
	 * Get Street
	 */
    public function getStreet() {
        return $this->street;
    }

    /**
	 * @function setStreet
     * @param $street varchar 
     * @access public
	 * Set Street
     */
    public function setStreet($street) {
        $this->street_dirty = TRUE;
        $this->street = $street;
    }

    /**
	 * @function getCity
	 * @access public
	 * @return $city varchar
	 * Get City
	 */
    public function getCity() {
        return $this->city;
    }

    /**
	 * @function setCity
     * @param $city varchar 
     * @access public
	 * Set City
     */
    public function setCity($city) {
        $this->city_dirty = TRUE;
        $this->city = $city;
    }

    /**
	 * @function getPostal
	 * @access public
	 * @return $postal varchar
	 * Get Postal
	 */
    public function getPostal() {
        return $this->postal;
    }

    /**
	 * @function setPostal
     * @param $postal varchar 
     * @access public
	 * Set Postal
     */
    public function setPostal($postal) {
        $this->postal_dirty = TRUE;
        $this->postal = $postal;
    }

    /**
	 * @function getState
	 * @access public
	 * @return $state varchar
	 * Get State
	 */
    public function getState() {
        return $this->state;
    }

    /**
	 * @function setState
     * @param $state varchar 
     * @access public
	 * Set State
     */
    public function setState($state) {
        $this->state_dirty = TRUE;
        $this->state = $state;
    }

    /**
	 * @function getPrimaryAddress
	 * @access public
	 * @return $primary_address varchar
	 * Get Primary Aaddress
	 */
    public function getPrimaryAddress() {
        return $this->primary_address;
    }

    /**
	 * @function setPrimaryAddress
     * @param $primaryAddress varchar 
     * @access public
	 * Set Primary Address
     */
    public function setPrimaryAddress($primaryAddress) {
        $this->primary_address_dirty = TRUE;
        $this->primary_address = $primaryAddress;
    }
	
    public function flat_array() {
        $flat_array = parent::flat_array();

        if ($this->street_dirty) {
            $flat_array['street'] = $this->getStreet();
        }

        if ($this->primary_address_dirty) {
            $flat_array['primary_address'] = $this->getPrimaryAddress();
        }

        if ($this->city_dirty) {
            $flat_array['city'] = $this->getCity();
        }
        
        if ($this->postal_dirty) {
            $flat_array['postal'] = $this->getPostal();
        }
        
        if ($this->state_dirty) {
            $flat_array['state'] = $this->getState();
        }

        return $flat_array;
    }
}