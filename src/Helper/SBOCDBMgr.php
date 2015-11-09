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
  
  /**
  * Creates an instance of SBOCDBMgr and returns that object to the caller
  *
  * @params string $entity_name (default = "eventbrite_sboc_attendee")
  * @params array $atendees (default = empty array)
  *
  * Returns implicit object of type SBOCDBMgr
  */ 
  public function __construct($entity_name=EBConsts::EBS_ENTITY_TYPE_ATTENDEE, array $attendees = array(),
      $save_changed_only = FALSE){
    $this->entityName = $entity_name;
    $this->attendees = $attendees;
    $this->saveChangedOnly = $save_changed_only;
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
    // Property names must match field names in base table(s) 
    $rec = array();
    $a = null;
    try{
      $rec = entity_load($this->entityName, array($attendee->attendeeId,)); 
      if (empty($rec)){
        if ($this->saveChangedOnly){
          return $a;
        }
        $a = entity_create($this->entityName, array('attendee_id' => $attendee->attendeeId,));
      }else{
        $a = current($rec);
      }
      $a->event_id = $attendee->eventId;
      $a->order_id = $attendee->orderId;
      $a->ticket_class_id = $attendee->ticketClassId;
      $a->create_date = self::convert_date_tz($attendee->createDate);
      $a->change_date = self::convert_date_tz($attendee->changeDate);
      $a->email_address = $attendee->emailAddress;
      $a->last_name = self::no_overflow($attendee->lastName,50);
      $a->first_name = self::no_overflow($attendee->firstName,50);
      $a->category = $attendee->category;
      $a->category_nid = $attendee->categoryNid;
      $a->order_type = $attendee->orderType;
      // Should only be updated from mail process
      // $a->email_sent = $attendee->emailSent; 
      // $a->email_send_date = $attendee->emailSendDate;
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
      
      $a->category_nid = $this->getCategoryNodeId($a->category);
      
      $a->save();      
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
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
      /*$values['contestant_last_name'] = 'Test7';
      $values['contestant_first_name'] = 'Test7';*/
      $q->fields($values);
      $q->execute();
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
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
  * Iterates over all Attendee records passed to the object and calls the realSave method to do the work
  *
  * @params None
  *
  * Returns array of Attendee records
  */
  public function save(){
    $retval = array(); 
    foreach($this->attendees as $id => $attendee){
      // realSave could return null if the "changes only" flag is set to true 
      // and a record does not exist with the current attendee id
      $attendee_rec = $this->realSave($attendee);
      if (isset($attendee_rec)){
        $retval[] = $attendee_rec;
      }
    }
    return $retval;
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
      $key = $attendee->attendeeId;
      $rec = entity_load($this->entityName, array($key,)); 
      $a = current($rec);
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
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '->'. __METHOD__, $e);
    }
    return $a;
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
      watchdog_exception(EBConsts::EBS_APPNAME_ATTENDEES, $e);
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
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
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
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
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
  public function getCategoryNodeId($category){
    $node_ids = array();
    $ret_val = 0;
    try{
      $q = new \EntityFieldQuery();
      $q->entityCondition('entity_type', 'node');
      $q->fieldCondition('field_participant_category', 'value', $category, '=');
      $result = $q->execute();
      if (!empty($result['node'])){
        $node_ids = array_keys($result['node']);
        $nodes = node_load_multiple($node_ids);
        if (count($nodes) > 0){
          $ret_val = current($nodes)->nid;
        }
      }
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
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
      while ($ret_val == 0 && $ctr <= 2){
        $q = new \EntityFieldQuery();
        $q->entityCondition('entity_type', 'node');
        $q->propertyCondition('title', $region_name, '=');
        $result = $q->execute();
        if (!empty($result['node'])){
          $node_ids = array_keys($result['node']);
          $nodes = node_load_multiple($node_ids);
          if (count($nodes) > 0){
            $ret_val = current($nodes)->nid;
          }
        }else{
          $region_name = EBConsts::EBS_UNSPECIFIED_REGION;
        }
        $ctr++;
      }
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
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
      $q->propertyCondition('status', 1, '=');
      $result = $q->execute();
      if (!empty($result['node'])){
        $node_ids = array_keys($result['node']);
        $nodes = node_load_multiple($node_ids);
        foreach($nodes as $node){
          $ret_val[$node->nid] = check_plain($node->title);
        }
      }
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
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
  public static function convert_date_tz($date_time, $tz=''){
    $tz = (empty($tz) ? variable_get('date_default_timezone', '') : $tz);
    $dt = new \DateTime($date_time, new \DateTimeZone($tz));
    $retval = $dt->format(EBConsts::EBS_MYSQLDATEFORMAT);
    
    return $retval;
  }
  
}

  