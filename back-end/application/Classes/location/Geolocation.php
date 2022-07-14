<?php

namespace location;

use persisted\Identifiable;
use persisted\Archiveable;
use persisted\Auditable;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APPPATH . 'Classes/traits/Identifiable.php';
require_once APPPATH . 'Classes/traits/Archiveable.php';
require_once APPPATH . 'Classes/traits/Auditable.php';

class Geolocation
{
	use Identifiable, Archiveable, Auditable;

	/**
     * @var float lat
     * @access private
     */
    private $lat;
    private $lat_dirty;

    /**
     * @var float long
     * @access private
     */
    private $long;
    private $long_dirty;

    /**
     * @method getLat
     * @access public
     * @return float $lat
     * Get Latitude
     */
    public function getLat() {
        return $this->lat;
    }

    /**
     * @method setLat
     * @access public
     * @param float $lat
     * Set Latitude
     */
    public function setLat($lat) {
        $this->lat_dirty = TRUE;
        $this->lat = $lat;
    }

    /**
     * @method getLong
     * @access public
     * @return float $long
     * Get Longitude
     */
    public function getLong() {
        return $this->long;
    }

    /**
     * @method setLong
     * @access public
     * @param float $long
     * Set Longitude
     */
    public function setLong($long) {
        $this->long_dirty = TRUE;
        $this->long = $long;
    }

    public function flat_array() {
        $flat_array = [];

        if ($this->lat_dirty) {
            $flat_array['lat'] = $this->getLat();
        }
        if ($this->long_dirty) {
            $flat_array['long'] = $this->getLong();   
        }
        
        return $flat_array;
    }
}