<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    function get_all_notification($reqData, $adminId = NULL) {
        $limit = sprintf("%d", $reqData->pageSize) ?? 0;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';
        $tbl_notification = TBL_PREFIX . 'notification';
        $available_columns = array("id", "created", "title", "shortdescription");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'id';
            $direction = 'desc';
        }

        $notification_columns = array($tbl_notification . ".id", $tbl_notification . ".created", $tbl_notification . ".title", $tbl_notification . ".shortdescription");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $notification_columns)), false);

        $this->db->select("(case when(user_type = 1)
        THEN
        (select concat(firstname,' ',lastname)  from tbl_member where id= userId)
        ELSE
          (select concat(firstname,' ',lastname) from tbl_participant where id= userId)
        END) as username");

        $this->db->select("(case when(user_type = 1)
        THEN
        'Member'
        ELSE
          'Participant'
        END) as user_type");
        if($adminId) {
          $this->db->where('specific_admin_user', $adminId);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->from($tbl_notification);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $return = array('count' => $dt_filtered_total, 'data' => $query->result());
        return $return;
    }

    function get_notification_alert($adminId=0) {
//        $limit = 10;
//        $page = 0;
        $tbl_notification = TBL_PREFIX . 'notification';

        $notification_columns = array($tbl_notification . ".id", $tbl_notification . ".created", $tbl_notification . ".title", $tbl_notification . ".shortdescription");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $notification_columns)), false);

        $this->db->select("(case when(user_type = 1)
        THEN
        (select concat(firstname)  from tbl_member where id= userId)
        ELSE
          (select concat(firstname) from tbl_participant where id= userId)
        END) as username");

        $this->db->select("(case when(user_type = 1)
        THEN 'Member'
        ELSE
          'Participant'
        END) as user_type");

//        $this->db->limit($limit);
        $this->db->from($tbl_notification);
        $this->db->where('sender_type', '1');
        $this->db->where('status', '0');
        $this->db->order_by('created', 'DESC');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $res1 =$query->result();

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $notification_columns)), false);

        $this->db->select("(case when(user_type = 1)
        THEN
        (select concat(firstname)  from tbl_member where id= userId)
        ELSE
          (select concat(firstname) from tbl_participant where id= userId)
        END) as username");

        $this->db->select("(case when(user_type = 1)
        THEN 'Member'
        ELSE
          'Participant'
        END) as user_type");

//        $this->db->limit($limit);
        $this->db->from($tbl_notification);
        $this->db->where('sender_type', '2');
        $this->db->where('specific_admin_user!=', 0);
        $this->db->where('specific_admin_user', $adminId);
        $this->db->where('status', '0');
        $this->db->order_by('created', 'DESC');
        $query2 = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total_spacific = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $res2 =$query2->result();

        $result = array_merge( $res1, $res2 );
        $return = array('count' => $dt_filtered_total+$dt_filtered_total_spacific, 'data' => $result);
        return $return;
    }

    /*
     * Get all notifcation without imail internel message
     * @param {int} adminId
     *
     * return type - array
     */
    function get_notification_without_imail_alert($adminId, $reqData, $filter_condition = '') {
        $limit = sprintf("%d",$reqData->data->pageSize) ?? 9999;
        $page = sprintf("%d",$reqData->data->page) ?? 0;
        $member_id = $reqData->data->member_id ?? '';
        $applicant_id = $reqData->data->applicant_id ?? '';
        $filter = $reqData->data->filtered ?? null;
        $popup = $reqData->data->popup ?? '';
        $is_admin_login =  !empty($reqData->data->admin_login) ? sprintf("%d",$reqData->data->admin_login) : '';
        $filter_logic = $reqData->data->filter_logic ?? '';
        $tbl_notification = TBL_PREFIX . 'notification';
        $src_columns = ["tbl_notification.title", "tbl_notification.shortdescription", "tbl_notification.entity_type", "tbl_notification.status", "tbl_notification.created"];

        $notification_columns = array($tbl_notification . ".id", $tbl_notification . ".created", $tbl_notification . ".title", $tbl_notification . ".shortdescription", $tbl_notification . ".entity_type", $tbl_notification . ".entity_id", $tbl_notification . ".status as notification_status");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $notification_columns)), false);

        $this->db->select("(CASE WHEN(user_type = 1)
        THEN
        (select concat(firstname)  from tbl_member where id= userId)
        ELSE
          (select concat(firstname) from tbl_participant where id= userId)
        END) as username");

        $this->db->select("(CASE WHEN(user_type = 1)
        THEN 'Member'
        ELSE
          'Participant'
        END) as user_type");

        $this->db->select("(CASE WHEN(status = 0)
        THEN '1'
        ELSE
          '0'
        END) as unread");

        $this->db->select("(CASE WHEN (entity_type = 6)
        THEN (SELECT service_agreement_id FROM tbl_service_agreement_attachment AS tsaa WHERE tsaa.id = entity_id)
        ELSE
          ''
        END) as redirect_url_id");

        $this->db->select("(CASE WHEN (entity_type = 6)
        THEN (SELECT signed_file FROM tbl_service_agreement_attachment AS tsaa WHERE tsaa.id = entity_id AND tsaa.signed_status = 1)
        ELSE
          ''
        END) as signed_file");

        $this->db->select("(CASE WHEN (entity_type = 9 OR entity_type = 10)
        THEN 'Email'
        ELSE
          'In-app'
        END) as notification_type");

        $this->db->from($tbl_notification);
        $this->db->where_in('sender_type', ['1','2']);
        $this->db->where('status!=', '2');

        //Skip the email notification for usual dropdown and admin user
        if($is_admin_login && $popup) {
          $this->db->where('entity_type !=', '9');
        }
        if(!empty($is_admin_login)) {
          $this->db->where_not_in('entity_type', ['8', '9']);
        }
        if($adminId) {
          $this->db->where('specific_admin_user', $adminId);
        }

        if($applicant_id)  {
          $this->db->where("(userID = $applicant_id and user_type = 5)");
        }
        //Notification List view page       

        if(!empty($member_id) && !empty($applicant_id)){
          $this->db->or_where("(userID = $member_id and user_type = 1 and entity_type = 8)");
        } elseif(!empty($member_id)) {
          $this->db->where("(userID = $member_id and user_type = 1)");
        }

        if(!empty($filter->search)) {
            $search_key = $this->db->escape_str($filter->search, TRUE);
            if (!empty($search_key)) {
              $this->db->group_start();
              for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if($column_search == 'tbl_notification.created') {
                  $this->db->or_like($column_search, DateFormate(str_replace('/','-', $search_key), 'Y-m-d'));
                }else if($column_search == 'tbl_notification.entity_type') {

                  if(stristr($search_key, 'Email')) {
                    $this->db->or_where($column_search, 9);
                  } else if(stristr($search_key, 'In-app')) {
                    $this->db->or_where($column_search .'!=', 9);
                  }

                }
                else if($column_search == 'tbl_notification.status') {

                  if(strtolower($search_key) == 'read') {
                    $this->db->or_where($column_search, 1);
                  } else if(strtolower($search_key) == 'unread') {
                    $this->db->or_where($column_search , 0);
                  }

                }else {
                  $this->db->or_like($column_search, $search_key);
                }
              }
              $this->db->group_end();
            }
        }
        
        $this->db->order_by('created', 'DESC');
        $this->db->limit($limit, ($page * $limit));

         //list view filter condition
         if (!empty($filter_condition)) {
          $this->db->having($filter_condition);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $res1 =$query->result();

        //Below query for HCM admin user
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $notification_columns)), false);

        $this->db->select("(CASE WHEN(user_type = 1)
        THEN
        (select concat(firstname)  from tbl_member where id= userId)
        ELSE
          (select concat(firstname) from tbl_participant where id= userId)
        END) as username");

        $this->db->select("(CASE WHEN(user_type = 1)
        THEN 'Member'
        ELSE
          'Participant'
        END) as user_type");

        $this->db->select("(CASE WHEN(status = 0)
        THEN '1'
        ELSE
          '0'
        END) as unread");

        $this->db->select("(CASE WHEN (entity_type = 6)
        THEN (SELECT service_agreement_id FROM tbl_service_agreement_attachment AS tsaa WHERE tsaa.id = entity_id)
        ELSE
          ''
        END) as redirect_url_id");

        $this->db->select("(CASE WHEN (entity_type = 6)
        THEN (SELECT signed_file FROM tbl_service_agreement_attachment AS tsaa WHERE tsaa.id = entity_id AND tsaa.signed_status = 1)
        ELSE
          ''
        END) as signed_file");

        $this->db->from($tbl_notification);
        $this->db->where('sender_type', '2');
        $this->db->where('specific_admin_user!=', 0);
        $this->db->where('specific_admin_user', $adminId);
        $this->db->where('status!=', '2');
        if($member_id) {
          $this->db->where('userID', $member_id);
        }

        if($is_admin_login) {
          $this->db->where_not_in('entity_type', ['8','9']);
        }
        $this->db->order_by('created', 'DESC');
        $this->db->limit($limit, ($page * $limit));

        $query2 = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total_spacific = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $res2 =$query2->result();

        $result = array_merge( $res1, $res2 );

        $notification = array();
        foreach ($result as $key => $value) {
            # code...
            $id = intval($value->id);
            if ($value->entity_type == 6 && $value->signed_file != '') {
                $signed_url = base_url('mediaShow/SA/' . $value->entity_id . '?filename=' . urlencode(base64_encode($value->signed_file)));
                $value->signed_url = $signed_url;
            } else {
                $value->signed_url = '';
            }
            $notification[$id] = $value;
        }
        $price = array_column($notification, 'id');
        array_multisort($price, SORT_DESC, $notification);

        $this->db->from($tbl_notification);
        if($member_id) {
          $this->db->where('userID', $member_id);
        }else
        {
          $this->db->where('specific_admin_user!=', 0);
          $this->db->where('specific_admin_user', $adminId);
        }
        $this->db->where('status', '0');

        $totCount = $dt_filtered_total + $dt_filtered_total_spacific;

        if ($totCount % $limit == 0) {
          $count = ($totCount / $limit);
      } else {
         $count = ((int) ($totCount / $limit)) + 1;
      }

        return array('status' => TRUE, 'count' => $count, 'data' => $notification, 'popCount' => $totCount, 'total_item' => $totCount, 'filter_logic' => $filter_logic);

    }


    /*
     * Get unread notification count
     * @param {int} adminId
     *
     * return type - array
     */
    function get_unread_notification_count_for_member($reqData) {
      $member_id = sprintf("%d",$reqData->data->member_id) ?? '';
      $applicant_id = sprintf("%d",$reqData->data->applicant_id) ?? '';
      $tbl_notification = TBL_PREFIX . 'notification';

      $notification_columns = array($tbl_notification . ".id", $tbl_notification . ".created", $tbl_notification . ".title", $tbl_notification . ".shortdescription", $tbl_notification . ".entity_type", $tbl_notification . ".entity_id", $tbl_notification . ".status as notification_status");
      $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $notification_columns)), false);      

      $this->db->from($tbl_notification);
      $this->db->where_in('sender_type', ['1','2']);
      $this->db->where('status', '0');
      $this->db->group_start();
      if($applicant_id)  {
        $this->db->where("(userID = $applicant_id and user_type = 5)");
      }
      //Notification List view page      
      
      if(!empty($member_id) && !empty($applicant_id)){
        $this->db->or_where("(userID = $member_id and user_type = 1 and entity_type = 8)");
      } elseif(!empty($member_id)) {
        $this->db->where("(userID = $member_id and user_type = 1)");
      }
      $this->db->group_end();
      
       
      $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
      $row_count =$query->num_rows();

      return array('status' => TRUE, 'unread_count' => $row_count);

  }

    /*
     * Update the notification as readed
     * @param {obj} $reqData
     */
    function update_notification_as_readed($reqData) {
        if (isset($reqData) && isset($reqData->data->notification_id)) {
            $tbl_notification = TBL_PREFIX . 'notification';
            $this->db->where('id', $reqData->data->notification_id);
            $this->db->update($tbl_notification, array("status" => 1));
            $return = array('status'=> true, 'msg' => 'Successfully Updated');
        } else {
            $return = array('status'=> true, 'error' => 'Notification id is null');
        }
        return $return;
    }

    /*
     * Update the notification as readed
     * @param {obj} $reqData
     */
    function remove_notification($reqData) {
      if (isset($reqData) && isset($reqData->data->notification_id)) {
          $tbl_notification = TBL_PREFIX . 'notification';
          $this->db->where('id', $reqData->data->notification_id);
          $this->db->update($tbl_notification, array("status" => 2));
          $return = array('status'=> true, 'msg' => 'Successfully Updated');
      } else {
          $return = array('status'=> true, 'error' => 'Notification id is null');
      }
      return $return;
  }

  function mark_all_as_read( $adminId = 0, $member_id = 0) {

    $return = array('status'=> true, 'error' => 'Admin id is null');

    if($adminId != 0) {
      $this->db->where('specific_admin_user', $adminId);
    }
    elseif($member_id != 0) {
      $this->db->where('userId', $member_id);
    }

    if($adminId != 0 || $member_id != 0) {
      $this->db->where('status!=', 2);
      $this->db->update(TBL_PREFIX . 'notification', array("status" => 1));
      $return = array('status'=> true, 'msg' => 'Successfully Updated');
    }


    return $return;
  }

    function clear_all_notification($adminId=0) {
        $tbl_notification = TBL_PREFIX . 'notification';
        $this->db->where('sender_type', '1');
        $this->db->update($tbl_notification, array("status" => 1));

        if(!empty($adminId)){
            $tbl_notification = TBL_PREFIX . 'notification';
            $this->db->where('specific_admin_user', $adminId);
            $this->db->update($tbl_notification, array("status" => 1));
        }

        return array('status' => true);
    }

    public function get_external_imail_notification($currentAdminId) {


        $select_colown = array('tbl_external_message.id', 'tbl_external_message.title', 'tbl_external_message_content.content', 'tbl_external_message_content.created', 'tbl_external_message_content.sender_type', 'tbl_external_message_content.id as contentId');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_colown)), false);


        $this->db->select("CASE tbl_external_message_content.sender_type
            WHEN 1 THEN (select concat(firstname,' ',lastname,'||',profile_image,'||',gender,'||',c_m.id) from tbl_member as c_m INNER JOIN tbl_department as c_d on c_d.id = c_m.department AND c_d.short_code = 'internal_staff' where c_m.id = tbl_external_message_content.userId)
            WHEN 2 THEN (select concat(firstname,' ', middlename,' ',lastname,'||', profile_image,'||',gender,'||',id) from tbl_participant where id = tbl_external_message_content.userId)
            WHEN 3 THEN (select concat(firstname,' ', middlename,' ',lastname,'||', profile_image,'||',gender,'||',c_m.id) from tbl_member as c_m INNER JOIN tbl_department as c_d on c_d.id = c_m.department AND c_d.short_code = 'external_staff' where c_m.id = tbl_external_message_content.userId)
            WHEN 4 THEN (select concat(name,'||', logo_file,'||',id)  from tbl_organisation where id = tbl_external_message_content.userId)
            ELSE NULL
            END as user_data");


        $this->db->from('tbl_external_message');
        $this->db->join('tbl_external_message_content', 'tbl_external_message.id = tbl_external_message_content.messageId', 'INNER');
        $this->db->join('tbl_external_message_recipient', 'tbl_external_message_recipient.messageContentId = tbl_external_message_content.id AND tbl_external_message_recipient.recipinent_type = 1', 'INNER');


        $this->db->where('tbl_external_message_recipient.is_read', 0);
        $this->db->where('tbl_external_message_recipient.is_notify', 0);
        $this->db->where('tbl_external_message_recipient.recipinentId', $currentAdminId);
        $this->db->where('tbl_external_message_content.is_draft', 0);

        $this->db->order_by('tbl_external_message_content.created', 'desc');

        $query = $this->db->get();
//        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $result = $query->result();

//        last_query();
        $ext_msg = array();
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $x['id'] = $val->id;
                $x['title'] = $val->title;
                $x['mail_date'] = $val->created;
                $x['contentId'] = $val->contentId;
                $x['content'] = setting_length($val->content, 100);
                $x['redirect_uri'] = '/admin/imail/external/inbox/' . $val->id;
                $sender_type = $val->sender_type;
                $x['type'] = 'external';
                $user_data = explode('||', $val->user_data);

                if (count($user_data) > 1) {
                    // here 0 = user name
                    $x['user_name'] = $user_data[0];

                    if ($sender_type == 1) {

                        // here 3 = user id // 1 = profile img name // 2 = gender
                        $x['user_img'] = get_admin_img($user_data[3], $user_data[1], $user_data[2]);
                    } else if ($sender_type == 2) {

                        // here 3 = user id  // 1 = profile img name  // 2 = gender
                        $x['user_img'] = get_participant_img($user_data[3], $user_data[1], $user_data[2]);
                    } else if ($sender_type == 3) {

                        // here 3 = user id  // 1 = profile img name  // 2 = gender
                        $x['user_img'] = get_admin_img($user_data[3], $user_data[1], $user_data[2]);
                    } else if ($sender_type == 4) {

                        // here 2 = user id  // 1 = profile img name
                        $x['user_img'] = get_org_img($user_data[2], $user_data[1]);
                    }
                }

                $ext_msg[] = $x;
            }
        }

        return $ext_msg;
    }

    function get_internal_imail_notification($currentAdminId) {

        $this->db->select(array('tbl_internal_message.id', 'tbl_internal_message.title', 'concat(firstname," ",lastname) as user_name', 'tbl_member.gender', 'tbl_member.profile_image', 'tbl_internal_message_content.senderId', 'tbl_internal_message_content.content', 'tbl_internal_message_content.created as mail_date', 'tbl_internal_message_content.id as contentId'));


        $this->db->from('tbl_internal_message');
        $this->db->join('(tbl_internal_message_content)', 'tbl_internal_message.id = tbl_internal_message_content.messageId', 'INNER');
        $this->db->join('tbl_internal_message_action', 'tbl_internal_message_action.messageId = tbl_internal_message.id AND tbl_internal_message_action.userId = ' . $currentAdminId, 'INNER');
        $this->db->join('tbl_internal_message_recipient', 'tbl_internal_message_recipient.messageContentId = tbl_internal_message_content.id', 'INNER');
        $this->db->join('tbl_member', 'tbl_internal_message_content.senderId = tbl_member.id', 'INNER');
        $this->db->join('tbl_department', 'tbl_department.id = tbl_member.department AND tbl_department.short_code = "internal_staff"', 'INNER');

        $this->db->where('tbl_internal_message_recipient.recipientId', $currentAdminId);
        $this->db->where('tbl_internal_message_recipient.is_read', 0);
        $this->db->where('tbl_internal_message_recipient.is_notify', 0);
        $this->db->where('tbl_internal_message_content.is_draft', 0);
        $this->db->order_by('mail_date', 'desc');


        $query = $this->db->get();

//        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        $result = $query->result();
//        print_r($result);
//        last_query();

        $ext_msg = array();
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $x['id'] = $val->id;
                $x['title'] = $val->title;
                $x['mail_date'] = $val->mail_date;
                $x['contentId'] = $val->contentId;
                $x['content'] = setting_length($val->content, 100);
                $x['user_name'] = $val->user_name;
                $x['user_img'] = get_admin_img($val->senderId, $val->profile_image, $val->gender);
                $x['redirect_uri'] = '/admin/imail/internal/inbox/' . $val->id;
                $x['type'] = 'internal';
                $ext_msg[] = $x;
            }
        }

        return $ext_msg;
    }

    function clear_imail_notification($reqData, $currentAdminId) {
        $data = array('is_notify' => 1);

        $contentId = $reqData->contentId;

        if ($contentId === 'ALL') {

            $where = array('recipientId' => $currentAdminId);

            // clear notification from internal mail
            $this->basic_model->update_records('internal_message_recipient', $data, $where);

            // clear notification form external mail
            $where = array('recipinentId' => $currentAdminId);
            $this->basic_model->update_records('external_message_recipient', $data, $where);
        } elseif ($contentId) {
            $where = array('recipientId' => $currentAdminId, 'messageContentId' => $contentId);

            if ($reqData->type === 'internal') {

                // clear notification from internal mail
                $this->basic_model->update_records('internal_message_recipient', $data, $where);
            } elseif ($reqData->type === 'external') {

                // clear notification form external mail
                $where = array('recipinentId' => $currentAdminId);
                $this->basic_model->update_records('external_message_recipient', $data, $where);
            }
        }
    }

    function get_single_notification($notificationId) {
        $tbl_notification = TBL_PREFIX . 'notification';

        $notification_columns = array($tbl_notification . ".id", $tbl_notification . ".created", $tbl_notification . ".title", $tbl_notification . ".shortdescription");
        $this->db->select($notification_columns);

        $this->db->select("(case when(user_type = 1)
        THEN
        (select concat(firstname)  from tbl_member where id= userId)
        ELSE
          (select concat(firstname) from tbl_participant where id= userId)
        END) as username");

        $this->db->select("(case when(user_type = 1)
        THEN 'Member'
        ELSE
          'Participant'
        END) as user_type");


        $this->db->from($tbl_notification);
        $this->db->where('sender_type', '1');
        $this->db->where('status', '0');
        $this->db->order_by('created', 'DESC');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());


        $return = $query->row();
        return $return;
    }

    //Create update notification
    function create_notification($data) {

      $this->basic_model->insert_records('notification',
            [
              'userId'=> $data['userId'],
              'user_type'=> $data['user_type'],
              'title' => $data['title'],
              'shortdescription' => $data['shortdescription'],
              'created' => $data['created'],
              'status' => $data['status'],
              'sender_type' => $data['sender_type'],
              'specific_admin_user' => $data['specific_admin_user'],
              'entity_type' => $data['entity_type'],
              'entity_id' => $data['entity_id']
            ]
        );
    }

}
