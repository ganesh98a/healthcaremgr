<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper class for save the viewed log history
 */
class ViewedLog {
	// Constant
	const ENTITY_TYPE_APPLICATION = 1;
    const ENTITY_TYPE_APPLICANT = 2;
    const ENTITY_TYPE_LEAD = 3;
    const ENTITY_TYPE_OPPORTUNITY = 4;
    const ENTITY_TYPE_SERVICE_AGREEMENT = 5;

    /**
     * @var entityType
     * @access private
     * @vartype: int
     */
    private $entityType;

    /**
     * @var entityType
     * @access private
     * @vartype: int
     */
    private $entityId;

    /**
     * @var viewedDate
     * @access private
     * @vartype: date_time
     */
    private $viewedDate;

    /**
     * @var viewdBy
     * @access private
     * @vartype: int
     */
    private $viewdBy;

    /**
     * @var archive
     * @access private
     * @vartype: int
     */
    private $archive = 0;

    static function getConstant($var)
    {
        return constant('self::'. $var);
    }

    /**
     * Getters and Setters
     */

    public function getEntityType() {
        return $this->entityType;
    }

    public function setEntityType($entityType) {
        $this->entityType = $entityType;
    }

    public function getEntityId() {
        return $this->entityId;
    }

    public function setEntityId($entityId) {
        $this->entityId = $entityId;
    }

    public function getViewedDate() {
        return $this->viewedDate;
    }

    public function setViewedDate($viewedDate) {
        $this->viewedDate = $viewedDate;
    }

    public function getViewedBy() {
        return $this->viewedBy;
    }

    public function setViewedBy($viewedBy) {
        $this->viewedBy = $viewedBy;
    }

    public function getArchive() {
        return $this->archive;
    }

    public function setArchive($archive) {
        $this->archive = $archive;
    }

    /**
     * Get Viewed Log by entity type
     */
    public function getEntityTypeValue($value) {
        $entity_type = 0;

        switch($value) {
    		case 'application':
    			$entity_type = 1;
    			break;
    		case '1':
    			$entity_type = 'application';
    			break;
    		case 'applicant':
    			$entity_type = 2;
    			break;
    		case '2':
    			$entity_type = 'applicant';
    			break;
    		case 'lead':
    			$entity_type = 3;
    			break;
    		case '3':
    			$entity_type = 'lead';
    			break;
    		case 'opportunity':
    			$entity_type = 4;
    			break;
    		case '4':
    			$entity_type = 'opportunity';
    			break;
    		case 'service_agreement':
    			$entity_type = 5;
    			break;
    		case '5':
    			$entity_type = 'service_agreement';
    			break;
            case 'online_assessment':
                $entity_type = 6;
                break;
    		default:
    			$entity_type = 0;
    			break;
    	}
        
        return $entity_type;
    }

    /**
     * Create Viewed Log
     */
    public function createViewedLog() {
        $ci = & get_instance();
        $logViewed = self::getViewedLogByDateAndEntity();
        if (isset($logViewed) == true && empty($logViewed) == false) {
        	// update
        	$logViewedId = $logViewed->id;
        	$history = json_decode($logViewed->history);
        	$history[] = $this->viewedDate;

        	$update_ary = array(
	            'viewed_date' => $this->viewedDate,
	            'history' => json_encode($history),
	            'archive' => $this->archive
	        );

	        $where_ary = array(
	        	'id' => $logViewedId
	        );

	        $viewedId = $ci->basic_model->update_records('viewed_log', $update_ary, $where_ary);
        } else {
        	// create
        	$history = array();
        	$history[] = $this->viewedDate;
        	$insert_ary = array(
	            'entity_type' => $this->entityType,
	            'entity_id' => $this->entityId,
	            'viewed_date' => $this->viewedDate,
	            'viewed_by' => $this->viewedBy,
	            'history' => json_encode($history),
	            'archive' => $this->archive
	        );

	        $viewedId = $ci->basic_model->insert_records('viewed_log', $insert_ary, $multiple = FALSE);
        }
        

        return $viewedId;
    }

    /**
     * Get Viewed Log by entity date and user
     */
    public function getViewedLogByDateAndEntity() {
        $ci = & get_instance();
        
        $select_ary = array(
            'id', 'entity_type', 'entity_id', 'viewed_date', 'viewed_by', 'history'
        );
        $where_ary = array(
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'viewed_by' => $this->viewedBy,
            'DATE(viewed_date)' => date('Y-m-d', strtotime($this->viewedDate)),
            'archive' => $this->archive
        );
        
        return $result = $ci->basic_model->get_row('viewed_log', $select_ary, $where_ary);
    }

}
