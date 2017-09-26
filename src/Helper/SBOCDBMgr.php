<?php

namespace Drupal\eventbrite_sboc\Helper;

use Drupal\eventbrite_sboc\Helper\EBConsts;

/**
* Interface iDBMgr
*    Outlines the public methods and properties that must be present in clasees
*    implementing this interface
* @function save()
* @function legacySave()
* @function loadAttendeesSave(array @attendee_ids)
*/
interface iDBMgr{
  public function save();
  public function saveChangedOnly();
  public function saveWithvalues(array $values);
  public function legacySave();
  public function loadAttendees(array $attendee_ids);
}

/**
* Class SBOCDBMgr
*    Implements interface iDBMgr
*    Manages saving and loading of Attendee objects/data to and from the database
*/
class SBOCDBMgr implements iDBMgr{
  protected $saveChangedOnly;
  public $entityName;
  public $attendees;
  public $func_email_callback;
  public $func_email_callback_2;

  /**
  * Creates an instance of SBOCDBMgr and returns that object to the caller
  *
  * @params string $entity_name (default = "eventbrite_sboc_attendee")
  * @params array $atendees (default = empty array)
  *
  * Returns implicit object of type SBOCDBMgr
  */
  public function __construct($entity_name = EBConsts::EBS_ENTITY_TYPE_ATTENDEE, array $attendees = array(),
      $save_changed_only = FALSE){
    $this->entityName = $entity_name;
    $this->attendees = $attendees;
    $this->saveChangedOnly = $save_changed_only;
    $this->func_email_callback = EBConsts::EBS_FUNC_EMAIL_CALLBACK;
    $this->func_email_callback_2 = EBConsts::EBS_FUNC_EMAIL_CALLBACK_2;
  }

  /**
  * Writes an attendee data stored in an object to the database.
  *
  * @params object $attendee
  *   This method uses the more Drupal like Entity API to save values to the database
  *
  * Returns array of attendee entity objects
  */
  protected function realSave($attendee){
    /*** ****************************************************** ***
    1: Property names must match field names in base table(s)
    2: The following fields should only be updated by the email prcess
        $a->email_sent = $attendee->emailSent;
        $a->email_send_date = $attendee->emailSendDate;
    ************************************************************ ***/
    $rec = array();
    $a = null;
    try{
      $rec = entity_load($this->entityName, array($attendee->attendeeId,));
      if (empty($rec)){
        $a = entity_create($this->entityName, array('attendee_id' => $attendee->attendeeId,));
      }else{
        $a = reset($rec);
      }
      $a->event_id = $attendee->eventId;
      $a->order_id = $attendee->orderId;
      $a->ticket_class_id = $attendee->ticketClassId;
      $a->create_date = $attendee->createDate;
      $a->change_date = $attendee->changeDate;
      $a->email_address = $attendee->emailAddress;
      $a->last_name = self::no_overflow($attendee->lastName,50);
      $a->first_name = self::no_overflow($attendee->firstName,50);
      $a->category = $attendee->category;
      $a->category_nid = $attendee->categoryNid;
      $a->order_type = $attendee->orderType;
      $a->reg_type = $attendee->regType;
      $a->region_name = $attendee->regionName;
      $a->contestant_last_name = self::no_overflow($attendee->contestantLastName,50);
      $a->contestant_first_name = self::no_overflow($attendee->contestantFirstName,50);
      $a->gender = $attendee->gender;
      $a->grade = $attendee->grade;
      $a->school = self::no_overflow($attendee->school,255);
      $a->year_of_birth = $attendee->yearOfBirth;
      $a->month_of_birth = $attendee->monthOfBirth;
      $a->day_of_birth = $attendee->dayOfBirth;
      $a->home_address_line_1 = self::no_overflow($attendee->homeAddressLine1,255);
      $a->home_address_line_2 = self::no_overflow($attendee->homeAddressLine2,255);
      $a->home_city = self::no_overflow($attendee->homeCity,100);
      $a->home_prov_state = $attendee->homeProvState;
      $a->home_postal_zip = self::no_overflow($attendee->homePostalZip,20);
      $a->home_phone_1 = self::no_overflow($attendee->homePhone1,20);
      $a->home_phone_2 = self::no_overflow($attendee->homePhone2,20);
      $a->email_consent = $attendee->emailConsent;
      $a->additional_info = self::no_overflow($attendee->additionalInfo,2500);
      $a->ts_create_date = strtotime($attendee->createDate);
      $a->ts_change_date = strtotime($attendee->changeDate);
      $a->category_nid = $this->getCategoryNodeId($a->category, $attendee->eventId, $attendee->language);
      $a->language = $attendee->language;

      $a->save();
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $a;
  }

  /**
  * Writes an attendee data stored in an object to the database.
  *
  * @params object $attendee
  *   This method uses the DBTNG API to save values to the database
  *
  * Returns array of Attendee records
  */
  protected function realLegacySave($attendee){
    $values = $retval = array();
    try{
      $values = self::mapValues($attendee);
      $retval = $values;
      // remove key values
      unset($values['event_id'], $values['order_id'], $values['attendee_id']);
      $q = db_merge(EBConsts::EBS_DBTABLE_ATTENDEES);
      $q->key(
        array(
          'event_id' => $attendee->eventId,
          'order_id' => $attendee->orderId,
          'attendee_id' => $attendee->attendeeId,
        )
      );
      $q->fields($values);
      $q->execute();
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $retval;
  }

  /**
  * Iterates over all Attendee records passed to the object and calls the legacySave method to do the work
  *
  * @params None
  *
  * Returns array of Attendee records
  */
  public function legacySave(){
    $retval = array();
    foreach($this->attendees as $id => $attendee){
      $retval[] = $this->realLegacySave($attendee);
    }

    return $retval;
  }

  /**
   * Convenience function to improve self-documentation of the class. This function essentially
   * calls the save method.
   * @param No parameters
   * @return Array of attendee objects
   */
  public function saveAttendees(){
    return $this->save();
  }

  /**
  * Iterates over all Attendee records passed to the object and calls the realSave method to do the work
  * @params None
  * @return array of Attendee records
  */
  public function save(){
    $retval = array();
    foreach($this->attendees as $id => $attendee){
      $retval[] = $this->realSave($attendee);
    }

    return $retval;
  }

  /**
   * Convenience function to improve self-documentation of the class. This function essentially
   * calls the saveChangedOnly method.
   * @param bool $strict
   * @return array of Attendee objects
   */
  public function saveChangedAttendeesOnly($strict = TRUE){
    return $this->saveChangedOnly($strict);
  }

  /**
   * Iterates over all Attendee records passed to the object and calls the realSave method to do the work
   *  Only allows changes to be saved and does not create new records
   *
   * Returns array of Attendee records
   * @param bool $strict
   * @return array
   * @throws \Exception
   */
  public function saveChangedOnly($strict = TRUE){
    $retval = array();
    foreach($this->attendees as $id => $attendee){
      $rec = entity_load($this->entityName, array($attendee->attendeeId,));
      if (!empty($rec)){
        $attendee_rec = reset($rec);
        // Record exists let's check the fields we need to in order to trigger an email
        $this->populateChangedFieldsList($attendee, $attendee_rec);
        $retval[] = $this->realSave($attendee);
        if (!empty($attendee->changedFields)){
          $params = array(
            array($attendee),
            EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_2,
          );
          $this->executeCallback($this->func_email_callback, $params);

          $params = array(
            array($attendee),
            EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_3,
          );
          $this->executeCallback($this->func_email_callback_2, $params);
        }
      }else{
        if (!$strict){
          $retval[] = $this->realSave($attendee);
        }
      }
    }

    return $retval;
  }

  /**
   * @param $callback
   * @param $params
   * @throws \Exception
   */
  public function executeCallback($callback, $params){
    if (function_exists($callback)){
      try{
        $callback_val = call_user_func_array($callback, $params);
        if ($callback_val === FALSE){
          throw new \Exception('Exception thrown in call to: '. $callback);
        }
      }catch(Exception $e){
        watchdog_exception(EBConsts::EBS_APP_NAME_MAIN , $e);
      }
    }
  }

  /**
  * Populates attendee object properties with new/changed data for selected fields
  *
  * @param object $attendeeFromSource
  *    EBAttendee object populated with data from source such as: Eventbrite API
  * @param object $attendeeSaved)
  *   EBAttendeeEntity object populated with data from application database
  *
  * Returns N/A
  */
  public function populateChangedFieldsList($attendeeFromSource, $attendeeSaved){
    $pf_map = array(
      'emailAddress' => 'email_address',
      'additionalInfo' => 'additional_info',
      'regionName' => 'region_name',
      'category' => 'category',
    );

    if (!is_array($attendeeFromSource->changedFields)) {
      $attendeeFromSource->changedFields = array();
    }

    foreach($pf_map as $property => $field){
      if ($attendeeFromSource->{$property} != $attendeeSaved->{$field}) {
        $attendeeFromSource->changedFields[$property] = array(
          'old' => $attendeeSaved->{$field},
          'new' => $attendeeFromSource->{$property},
        );
      }
    }
  }
  /**
  * Writes an attendee data stored in an object to the database.
  *
  * @params object $attendee
  *   This method uses the more Drupal like Entity API to save values to the database
  *
  * Returns array of attendee entity objects
  */
  protected function realSaveWithValues($attendee, $values){
    try{
      $rec = entity_load($this->entityName, array($attendee->attendeeId,));
      $a = reset($rec);
      $over_flows = self::over_flows();
      // Global namespace designator = \ClassName
      if (empty($a)){
        throw new \Exception(EBConsts::EBS_ERROR_NO_MATCHING_RECORD);
      }
      $r = new \ReflectionObject($a);
      foreach($values as $field => $value){
        if ($r->hasProperty($field)) {
          $a->{$field} = $value;
          if (in_array($field, array_keys($over_flows))){
            $a->{$field} = self::no_overflow($a->{$field}, $over_flows[$field]);
          }
        }
      }
      $a->save();
      $this->updateTimestamps($attendee);
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $a;
  }

  /**
  * Updates timestamp fields which are not wholly managed by the system
  *
  * @params object $attendee
  *
  * Returns N/A
  */
  protected function updateTimestamps($attendee){
    $emw = entity_metadata_wrapper(EBConsts::EBS_ENTITY_TYPE_NAME, $attendee->attendeeId);
    $emw->ts_create_date = strtotime($attendee->createDate);
    $emw->ts_change_date = strtotime($attendee->changeDate);
    $emw->ts_email_send_date = !empty($attendee->emailSendDate) ? strtotime($attendee->emailSendDate) : $emw->ts_email_send_date;
    $emw->save();
  }

  /**
  * Iterates over all Attendee records passed to the object and calls the realSave method to do the work
  *
  * @params array $values
  *
  * Returns array of Attendee records
  */
  public function saveWithvalues(array $values){
    $retval = array();
    foreach($this->attendees as $id => $attendee){
      $retval[] = $this->realSaveWithValues($attendee, $values);
    }

    return $retval;
  }

 /**
  * Returns an associative arary of Attendee Entity objects
  *
  * @param array $attendee_ids
  *
  * Returns array of Attendees
  */
  public function loadAttendees(array $ids){
    $attendees = array();
    try{
      $attendees = entity_load(EBConsts::EBS_ENTITY_TYPE_ATTENDEE, $ids);
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $attendees;
  }

  /**
  * Returns an associative arary of Attendee Entity objects
  *
  * @param array $attendee_ids
  *
  * Returns array of Attendee Ids
  */
  public function loadOrders(array $order_ids){
    $retval = array();
    $order_ids = (empty($order_ids) ? array(0) : $order_ids);
    try{
      $q = db_select(EBConsts::EBS_DBTABLE_ATTENDEES, 'o');
      $q->fields('o', array(EBConsts::EBS_FIELDS_EVENTBRITE_KEY,));
      $q->condition(EBConsts::EBS_FIELDS_ORDER_ID, $order_ids,'IN');
      $recs = $q->execute()->fetchAll();
      foreach($recs as $rec){
        $retval[] = $rec->{EBConsts::EBS_FIELDS_EVENTBRITE_KEY};
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }
    return $retval;
  }

  /**
  * Returns an associative arary of Attendee Entity objects
  *
  * @param array $attendee_ids
  *
  * Returns array of Attendee Ids
  */
  public function legacyLoadAttendees(array $ids){
    $retval = array();
    $ids = (empty($ids) ? array(0) : $ids);
    try{
      $q = db_select(EBConsts::EBS_DBTABLE_ATTENDEES, 'a');
      $q->fields('a', array(EBConsts::EBS_FIELDS_ENTITY_KEY,));
      $q->condition(EBConsts::EBS_FIELDS_EVENTBRITE_KEY, $ids,'IN');
      $recs = $q->execute()->fetchAll();
      foreach($recs as $rec){
        $retval[] = (int)$rec->{EBConsts::EBS_FIELDS_ENTITY_KEY};
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $retval;
  }

  /**
  * Returns a node id corresonding to the participant category
  *
  * @param string $category
  *
  * Returns int
  */
  public function getCategoryNodeId($category, $event_id, $language = 'en'){
    $node_ids = array();
    $ret_val = 0;
    try{
      $q = new \EntityFieldQuery();
      $q->entityCondition('entity_type', 'node');
      $q->entityCondition('bundle', 'contestant_category');
      $q->fieldCondition('field_participant_category', 'value', $category, '=');
      $q->fieldCondition('field_participant_language', 'value', $language, '=');
      $q->fieldCondition('field_eventbrite_event_id', 'value', $event_id, '=');
      $q->propertyCondition('status', 1);
      $q->propertyOrderBy('created', 'DESC');
      $result = $q->execute();
      if (!empty($result['node'])){
        $node_ids = array_keys($result['node']);
        $nodes = node_load_multiple($node_ids);
        if (count($nodes) > 0){
          $ret_val = reset($nodes)->nid;
        }
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $ret_val;
  }

  /**
  * Returns a node id corresonding to the participant region
  *
  * @param array $region_name
  *
  * Returns int
  */
  public function getRegionNodeId($region_name){
    $node_ids = array();
    $ret_val = 0;
    $ctr = 1;
    try{
      /* We are post-incrementing inline: $ctr++ */
      while ($ret_val == 0 && $ctr++ <= 2){
        $q = new \EntityFieldQuery();
        $q->entityCondition('entity_type', 'node');
        $q->entityCondition('bundle', 'region');
        $q->propertyCondition('title', $region_name, '=');
        $result = $q->execute();
        if (!empty($result['node'])){
          $node_ids = array_keys($result['node']);
          $nodes = node_load_multiple($node_ids);
          if (count($nodes) > 0){
            $ret_val = reset($nodes)->nid;
          }
        }else{
          $region_name = EBConsts::EBS_UNSPECIFIED_REGION;
        }
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $ret_val;
  }

  /**
  * Returns a node ids and Node titles for email message content type
  *
  * @param none
  *
  * Returns array
  */
  public function getEmailMessages(){
    $ret_val = $node_ids = array();
    try{
      $q = new \EntityFieldQuery();
      $q->entityCondition('entity_type', 'node');
      $q->entityCondition('bundle', 'email_message');
      $result = $q->execute();
      if (!empty($result['node'])){
        $node_ids = array_keys($result['node']);
        $nodes = node_load_multiple($node_ids);
        foreach($nodes as $node){
          $ret_val[$node->nid] = check_plain($node->title);
        }
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APP_NAME_MAIN, $e);
    }

    return $ret_val;
  }


  /**
  * Returns an associative arary of field names and values to be used in INSERT/UPDATE/MERGE operations
  *
  * @param object $attendee
  *
  * Returns array of values
  */
  public static function mapValues($attendee){
    $mapped_values =  array(
      'event_id' => $attendee->eventId,
      'order_id' => $attendee->orderId,
      'attendee_id' => $attendee->attendeeId,
      'ticket_class_id' => $attendee->ticketClassId,
      'change_date' => $attendee->changeDate,
      'create_date' => $attendee->createDate,
      'email_address' => $attendee->emailAddress,
      'last_name' => self::no_overflow($attendee->lastName,50),
      'first_name' => self::no_overflow($attendee->firstName,50),
      'category' => $attendee->category,
      'category_nid' => $attendee->categoryNid,
      'order_type' => $attendee->orderType,
      'email_sent' => $attendee->emailSent,
      'email_send_date' => $attendee->emailSendDate,
      'reg_type' => $attendee->regType,
      'region_name' => $attendee->regionName,
      'region_nid' => $attendee->regionNid,
      'contestant_last_name' => self::no_overflow($attendee->contestantLastName,50),
      'contestant_first_name' => self::no_overflow($attendee->contestantFirstName,50),
      'gender' => $attendee->gender,
      'grade' => $attendee->grade,
      'school' => self::no_overflow($attendee->school,255),
      'year_of_birth' => $attendee->yearOfBirth,
      'month_of_birth' => $attendee->monthOfBirth,
      'day_of_birth' => $attendee->dayOfBirth,
      'home_address_line_1' => self::no_overflow($attendee->homeAddressLine1,255),
      'home_address_line_2' => self::no_overflow($attendee->homeAddressLine2,255),
      'home_city' => self::no_overflow($attendee->homeCity,100),
      'home_prov_state' => $attendee->homeProvState,
      'home_postal_zip' => self::no_overflow($attendee->homePostalZip,20),
      'home_phone_1' => self::no_overflow($attendee->homePhone1, 20),
      'home_phone_2' => self::no_overflow($attendee->homePhone2, 20),
      'email_consent' => $attendee->emailConsent,
      'additional_info' => self::no_overflow($attendee->additionalInfo,2500),
      'language' => $attendee->language,
    );

    // _eventbrite_sboc_debug_output($attendee);
    if (isset($attendee->passwordResetUrl)){
      $mapped_values['password_reset_url'] = $attendee->passwordResetUrl;
    }

    return $mapped_values;
  }

  /**
  * Calculates the maximum number of characters allowed for the field in the db
  *
  * @param string $value
  * @param int length
  *
  * Returns string
  */
  public static function no_overflow($value, $length){
    if (empty($length)){
      $length = strlen($value);
    }

    return substr($value, 0, $length);
  }

  /**
  * Converts an attendee id to a numeric value e.g. 12345-1 to 12345+1 = 12346
  *
  * @param string $attendee_id
  *   A string representing the unique attendee id received through the Eventbrite API
  *   Drupal's Entity system expects a numeric (int) value as the id: See hook_entity_info
  *
  * Returns string
  */
  public static function fix_attendee_id($attendee_id){
    $retval = 0; // initialize to empty value
    $pos = strpos($attendee_id, '-');
    if ($pos === FALSE){
      $retval = $attendee_id;
    }else{
      // look for "-" character
      $retval = (int)$attendee_id;
      $pos++;
      $num2 = (int)substr($attendee_id, $pos);
      $retval += $num2;
    }

    return $retval;
  }

  /**
  * Converts an attendee id to a numeric value e.g. 12345-1 to 12345+1 = 12346
  *
  * @param string $attendee_id
  *   A string representing the unique attendee id received through the Eventbrite API
  *   Drupal's Entity system expects a numeric (int) value as the id: See hook_entity_info
  *
  * Returns string
  */
  public static function over_flows(){
    $over_flow = array(
      'last_name' => 50,
      'first_name' => 50,
      'contestant_last_name' => 50,
      'contestant_first_name' => 50,
      'school' => 255,
      'home_address_line_1' => 255,
      'home_address_line_2' => 255,
      'home_city' => 100,
      'home_phone_1' => 20,
      'home_phone_2' => 20,
      'additional_info' => 2500,
    );

    return $over_flow;
  }

  /**
  * Returns a date matching the timezone specified
  *
  * @param DateTime $date_time
  * @param String $tz
  *
  * Returns DateTime (formatted string)
  */
  public static function convert_date_tz($date_time, $tz = ''){
    $tz = (empty($tz) ? variable_get('date_default_timezone', EBConsts::tz_Site_Default) : $tz);
    $dt = new \DateTime($date_time, new \DateTimeZone($tz));
    $retval = $dt->format(EBConsts::EBS_MYSQLDATEFORMAT);

    return $retval;
  }

  /**
   * Returns a date matching the target timezone specified
   *
   * @param DateTime $date_time
   * @param String $current_tz
   * @param String $new_tz
   *
   * Returns DateTime (formatted string)
   */
  public static function convert_from_date_tz($date_time, $current_tz = '', $new_tz = ''){
    $current_tz = (empty($current_tz) ? variable_get('date_default_timezone', EBConsts::tz_Site_Default) : $current_tz);
    $new_tz = (empty($new_tz) ? $current_tz : $new_tz);

    $dt = new \DateTime($date_time, new \DateTimeZone($current_tz));
    $dt->setTimezone(new \DateTimeZone($new_tz));
    $retval = $dt->format(EBConsts::EBS_MYSQLDATEFORMAT);

    return $retval;
  }

  /**
  * Returns a numeric value for month
  *
  * @param Month as string or number
  *
  * Returns Number
  */
  public static function month_num($month){
    if (is_numeric($month)){
      return $month;
    }

    $m = array();
    $ret_val = 0;
    if (is_string($month)){
      /* $m = array(1 => 'january', 'february', 'march', 'april', 'may', 'june', 'july',
      'august', 'september', 'october', 'november', 'december',); */
      $month = drupal_strtolower($month);
      for($ctr = 1; $ctr <= 12; $ctr++){
        $m[$ctr] = drupal_strtolower(date('F', mktime(0, 0, 0, $ctr, 1, date('Y'))));
        if ($m[$ctr] == $month){
          $ret_val = $ctr;
          break;
        }
      }
    }

    return $ret_val;

  }

}


