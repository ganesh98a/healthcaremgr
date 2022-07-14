<?php
/*
 * Filename: ParticipantAddress.php
 * Desc: Address details of Participant
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Class: ParticipantAddress
 * Desc:  Getter and Setter Methods for getting participant address.
 * Variables for participant address class, which are used in getter ands setter methods
 * Created: 02-08-2018
 */

class ParticipantAddress
{
    /**
     * @var int id
     * @access private
     */
    private $id;

    /**
     * @var int participantId
     * @access private
     */
    private $participantId;

    /**
     * @var string street
     * @access private
     */
    private $street;

    /**
     * @var string city
     * @access private
     */
    private $city;

    /**
     * @var int postal
     * @access private
     */
    private $postal;

    /**
     * @var int state
     * @access private
     */
    private $state;

    /**
     * @var float lat
     * @access private
     */
    private $lat;

    /**
     * @var float long
     * @access private
     */
    private $long;

    /**
     * @var int site_category
     * @access private
     */
    private $site_category;

    /**
     * @var int participantid
     * @access private
     */
    private $primary_address;

    /**
     * @var int archive
     * @access private
     */
    private $archive;

    /**
     * @method setId
     * @access public
     * @param int $id
     * Set Id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @method getId
     * @access public
     * @return int $id
     * Get Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @method setParticipantId
     * @access public
     * @param int $participantId
     * Set Participant Id
     */
    public function setParticipantId($participantId)
    {
        $this->participantId = $participantId;
    }

    /**
     * @method getParticipantId
     * @access public
     * @return int $participantId
     * Get Participant Id
     */
    public function getParticipantId()
    {
        return $this->participantId;
    }

    /**
     * @method getStreet
     * @access public
     * @return int $street
     * Get Street
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @method setStreet
     * @access public
     * @param string $street
     * Set Street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @method getStreet
     * @access public
     * @return string $city
     * Get City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @method setCity
     * @access public
     * @param string $city
     * Set Street
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @method getPostal
     * @access public
     * @return int $postal
     * Get Postal
     */
    public function getPostal()
    {
        return $this->postal;
    }

    /**
     * @method setPostal
     * @access public
     * @param int $postal
     * Set Postal
     */
    public function setPostal($postal)
    {
        $this->postal = $postal;
    }

    /**
     * @method getState
     * @access public
     * @return int $state
     * Get State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @method setState
     * @access public
     * @param int $postal
     * Set State
     */
    public function setState($state)
    {
        $this->state = $state;
    }

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
        $this->long = $long;
    }

    /**
     * @method getSiteCategory
     * @access public
     * @return int $site_category
     * Get Site Category
     */
    public function getSiteCategory()
    {
        return $this->site_category;
    }

    /**
     * @method setSiteCategory
     * @access public
     * @param int $siteCategory
     * Set Site Category
     */
    public function setSiteCategory($siteCategory)
    {
        $this->site_category = $siteCategory;
    }

    /**
     * @method getPrimaryAddress
     * @access public
     * @return int $primary_address
     * Get Primary Address
     */
    public function getPrimaryAddress()
    {
        return $this->primary_address;
    }

    /**
     * @method setPrimaryAddress
     * @access public
     * @param int $primary_address
     * Set Primary Address
     */
    public function setPrimaryAddress($primaryAddress)
    {
        $this->primary_address = $primaryAddress;
    }

    /**
     * @method getArchive
     * @access public
     * @return int $archive
     * Get Archive
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @method setArchive
     * @access public
     * @param int $archive
     * Set Archive
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;
    }

    public function addParticipantAddress()
    {
        $CI = & get_instance();
        $CI->load->model('Participant/Participant_address_model');
        return $CI->Participant_address_model->create_participant_address($this);
    }
}
