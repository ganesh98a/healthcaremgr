<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : Feed_model
 * Uses : for handle query operation of Feed
 *
 */
class Feed_model extends CI_Model {

    const ENTITY_TYPE_OPPORTUNITY = '';
    const ENTITY_TYPE_LEAD = '';

    function __construct() {
        parent::__construct();
    }

    /**
     * Define reated type constant
     */
    public function get_related_type($related) {
    	switch($related) {
    		case 'opportunity':
    			$related_type = 1;
    			break;
    		case '1':
    			$related_type = 'opportunity';
    			break;
    		case 'lead':
    			$related_type = 2;
    			break;
    		case '2':
    			$related_type = 'lead';
    			break;
    		case 'service_agreement':
    			$related_type = 3;
				break;
			case 'application':
				$related_type = 4;
				break;
    		case '3':
    			$related_type = 'service_agreement';
				break;
			case 'interview':
				$related_type = 5;
				break;
			case 'form_applicant':
				$related_type = 6;
				break;
			case 'fms_feedback':
				$related_type = 7;
				break;
    		default:
    			$related_type = '';
    			break;
    	}
    	return $related_type;
    }

    /*
     * Get comments by history id
     * @param {int} history_id
     */
    public function get_comment_by_history_id($history_id, $related_type) {
    	switch ($related_type) {
    		case 1:
    			$table_comment = 'opportunity_history_comment';
    			break;
    		case 2:
    			$table_comment = 'lead_history_comment';
    			break;
    		case 3:
    			$table_comment = 'service_agreement_history_comment';
				break;
			case 4:
				$table_comment = 'application_history_comment';
				break;
			case 5:
				$table_comment = 'recruitment_interview_history_comment';
				break;
			case 6:
				$table_comment = 'form_applicant_history_comment';
				break;
			case 7:
				$table_comment = 'fms_feedback_history_comment';
				break;
    		default:
    			$table_comment = '';
    			break;
    	}
    	if ($table_comment == '') {
    		return [];
    	}
    	$this->db->select(["hc.*", 'CONCAT(m.firstname, \' \', m.lastname) as created_by']);
        $this->db->from(TBL_PREFIX . $table_comment.' as hc');
        $this->db->join(TBL_PREFIX . 'member as m', 'm.id = hc.created_by', 'left');
		$this->db->where(['hc.archive' => 0, 'hc.history_id' => $history_id])->order_by('hc.id', 'DESC');
		$query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * To save feed
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type feedtId
     */
    function save_feed($data, $adminId, $created_at = false) {
    	// new opp - create specialised field history
    	$opportunityId = $data["source_id"];
    	$feed_title = $data["feed_title"];
    	$related_type = $data["related_type"];
        $feed_type = $data["feed_type"]?? null;

    	switch ($related_type) {
    		case 1:
    			$table_history = 'opportunity_history';
    			$table_feed = 'opportunity_history_feed';
    			$source_field = 'opportunity_id';
    			break;
    		case 2:
    			$table_history = 'lead_history';
    			$table_feed = 'lead_history_feed';
    			$source_field = 'lead_id';
    			break;
    		case 3:
    			$table_history = 'service_agreement_history';
    			$table_feed = 'service_agreement_history_feed';
    			$source_field = 'service_agreement_id';
				break;
			case 4:
				$table_history = 'application_history';
				$table_feed = 'application_history_feed';
				$source_field = 'application_id';
				break;
			case 5:
				$table_history = 'recruitment_interview_history';
				$table_feed = 'recruitment_interview_history_feed';
				$source_field = 'interview_id';
				break;
			case 6:
				$table_history = 'form_applicant_history';
				$table_feed = 'form_applicant_history_feed';
				$source_field = 'form_applicant_id';
				break;
			case 7:
				$table_history = 'fms_feedback_history';
				$table_feed = 'fms_feedback_history_feed';
				$source_field = 'feedback_id';
				break;
    		default:
    			$table_history = '';
    			$table_feed = '';
    			$source_field = '';
    			break;
    	}
    	if ($table_history == '' || $table_feed == '') {
    		return '';
    	}
        $bSuccess = $this->db->insert(
            TBL_PREFIX . $table_history,
            [
                $source_field => $opportunityId,
                'created_by' => $adminId,
                'created_at' => $created_at ? $created_at : DATE_TIME
            ]
        );
        $history_id = $this->db->insert_id();

        $feed_id = $this->create_feed_history_entry($history_id, $opportunityId, 'feed', $feed_title, $adminId, $table_feed, $feed_type);

        return $history_id;
    }

    /**
     * Save feed
     */
    public function create_feed_history_entry($history_id, $opportunity_id, $field, $val, $adminId, $table_feed, $feed_type = null)
    {
        $values = [
            'history_id' => $history_id,
            'desc' => $val,
            'created_by' => $adminId,
            'created_at' => DATE_TIME
        ];
        $sms_feed_tables = ['recruitment_interview_history_feed', 'application_history_feed'];
        if (in_array($table_feed, $sms_feed_tables) ) {
            $values['feed_type'] = $feed_type;
        }
        $bSuccess = $this->db->insert(TBL_PREFIX . $table_feed, $values);

        return $bSuccess;
    }

    /*
     * To save comment
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type feedtId
     */
    function save_comment($data, $adminId) {
    	$history_id = $data["history_id"];
    	$feed_comment = $data["feed_comment"];
    	$related_type = $data["related_type"];

    	switch ($related_type) {
    		case 1:
    			$table_comment = 'opportunity_history_comment';
    			break;
    		case 2:
    			$table_comment = 'lead_history_comment';
    			break;
    		case 3:
    			$table_comment = 'service_agreement_history_comment';
				break;
			case 4:
				$table_comment = 'application_history_comment';
				break;
			case 5:
				$table_comment = 'recruitment_interview_history_comment';
				break;
			case 6:
				$table_comment = 'form_applicant_history_comment';
				break;
			case 7:
				$table_comment = 'fms_feedback_history_comment';
				break;
    		default:
    			$table_comment = '';
    			break;
    	}
    	if ($table_comment == '') {
    		return '';
    	}

        $comment_id = $this->db->insert(TBL_PREFIX . $table_comment, [
            'history_id' => $history_id,
            'desc' => $feed_comment,
            'created_by' => $adminId,
            'created_at' => DATE_TIME
        ]);

        return $comment_id;
    }
}