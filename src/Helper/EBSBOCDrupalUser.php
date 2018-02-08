<?php

namespace Drupal\eventbrite_sboc\Helper;

use Drupal\eventbrite_sboc\Helper\EBConsts;
use Drupal\eventbrite_sboc\Helper\SBOCDBMgr;
use Drupal\eventbrite_sboc\Helper\EBAttendees;


class EBSBOCDrupalUser{
  public $attendee;
  public $user;
  
  /**
  * Returns a single user object
  *
  * @param EBAttendee $attendee
  *
  * Returns object
  */
  public function __construct(EBAttendee $attendee){
    $this->attendee = $attendee;
    $this->user = NULL;
  }
  
  /**
  * Assembles an array of field values suitable for saving in a custom field
  *
  * @param none
  *
  * Returns Array
  */
  protected function assembleFieldValues(){
    $fields = array(
      'field_order_number' => array(
        'und' => array(
          0 => array(
              'value' => $this->attendee->orderId,
            ),
          ),
        ),
      'field_attendee_number' => array(
        'und' => array(
          0 => array(
              'value' => $this->attendee->attendeeId,
            ),
          ),
        ),
    ); 
    return $fields;
  }
  
  public function userExists(EBAttendee $attendee){
    $ret_val = FALSE; 
    $this->attendee = $attendee;
    $values = array(
      'value' => $this->attendee->{EBConsts::EBS_ENTITY_EMAIL_FIELD},
      'operation' => '=',
    );

    $user = self::searchUser(array('mail' => $values));
    $ret_val = !empty($user);

    // Find user by name
    if (!$ret_val){
      $user = self::searchUser(array('name' => $values));
      $ret_val = !empty($user);
    }

    if ($ret_val){
      $this->user = reset($user);
    }

    return $ret_val;
  }
  
  /**
  * Creates a Drupal user account for the order associated with the attendee record
  *
  * @param none
  *
  * Returns Boolean
  */
  public function createUser(EBAttendee $attendee){
    $this->attendee = $attendee;
    if (empty($this->attendee)) {
      return FALSE;
    }   
    $new_user = new \StdClass;
    $new_user->is_new = TRUE;
    $new_user->status = 1;
    $new_user->name = $this->attendee->{EBConsts::EBS_ENTITY_EMAIL_FIELD};
    $new_user->mail = $this->attendee->{EBConsts::EBS_ENTITY_EMAIL_FIELD};
    $new_user->init = $this->attendee->{EBConsts::EBS_ENTITY_EMAIL_FIELD};
    $new_user->pass = user_password(8);
    $new_user->timezone = variable_get('date_default_timezone', '');
    $new_user->language = empty($attendee->language) ? 'en' : $attendee->language;
    try{
      $uid = user_save($new_user)->uid;
      $this->user = $this->loadUser($uid);
      $role = user_role_load_by_name(EBConsts::EBS_DRUPAL_ROLE_ENROLLED_USER);
      $roles = $this->user->roles + array($role->rid => $role->name,);
      $this->user = user_save($this->user, array('roles' => $roles,));
      // Use Entity methods instead e.g. $emw = entity_metadata_wrapper('user', $uid);
      // Followed by user_load($uid) to update stored object
      $fields = $this->assembleFieldValues();
      foreach($fields as $key => $value){
        $this->user = user_save($this->user, array($key => $value,));
      }
      watchdog(EBConsts::EBS_APPNAME, t('New user account created').': @name', array('@name' => $new_user->name,), WATCHDOG_INFO);
    }catch(Exception $e){
      $this->user = NULL;
      watchdog_exception(EBConsts::EBS_APPNAME_ATTENDEES, $e);
    }
    
    return !empty($this->user);
  }
  
  /**
  * Sends an email to the registered user upon successful registration
  *
  * @param none
  *
  * Returns same result as drupal_mail()
  */
  public function userMailNotify(){
    if (!empty($this->user)){
      _user_mail_notify('register_admin_created', $this->user);
    }
  }
  
  /**
  * Returns a single user object
  *
  * @param integer $user_id
  *
  * Returns object
  */
  public function loadUser($user_id){
    return user_load($user_id);
  }

  /**
   * @param integer $user_id
   *   User account id
   * @return string
   *   Url with language context
   */
  public function passwordResetUrl($user_id = -1){
    $ret_val = '';
    $account = $this->loadUser($user_id);

    if ($account === FALSE) {
      return $ret_val;
    }

    $langs = language_list();
    $lang = language_default();

    if (!empty($langs[$account->language]) && $langs[$account->language]->enabled){
      $lang = $langs[$account->language];
    }

    $options = array(
      'absolute' => TRUE,
      'language' => $lang,
    );

    $timestamp = time();
    $hash = user_pass_rehash($account->pass, $timestamp, $account->login, $account->uid);
    $ret_val = url("user/reset/$account->uid/$timestamp/$hash", $options);

    return $ret_val;
  }
  
  /**
  * Returns an array of user objects
  *
  * @param array $property_condition
  * @param array $field_condition
  *   Each array should be in the form: array('field_or_property_name' => array('value' => $value, 'operation' = $op),)
  * Returns array of values
  * @return array ret_val
  */
  public static function searchUser(array $property_condition, array $field_condition = array()){
    $ret_val = $user_ids = array();
    try{
      $q = new \EntityFieldQuery();
      $q->entityCondition('entity_type', 'user');
      foreach($property_condition as $key => $value){
        $input_value = $value['value'];
        $op = ($value['operation'] ? $value['operation'] : '=');
        $q->propertyCondition($key, $input_value, $op);
      }
      foreach($field_condition as $key => $value){
        $input_value = $value['value'];
        $op = ($value['operation'] ? $value['operation'] : '=');
        $q->fieldCondition($key, 'value', $input_value, $op);
      }
      $result = $q->execute();
      if (!empty($result['user'])){
        $user_ids = array_keys($result['user']);
        $ret_val = user_load_multiple($user_ids);
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APPNAME_ATTENDEES, $e);
    }
    
    return $ret_val;
  }

  /**
   * @param \Drupal\eventbrite_sboc\Helper\EBAttendee $attendee
   * @return user object or null;
   */
  public function updateUserLanguage(EBAttendee $attendee){
    $ret_val = NULL;
    try {
      if ($this->userExists($attendee)) {
        $user = $this->loadUser($this->user->uid);
        $user->language = $attendee->language;
        $ret_val = user_save($user);
      }
    }catch (Exception $e){
      watchdog_exception(EBConsts::EBS_APPNAME_ATTENDEES, $e);
    }
    return $ret_val;
  }
  
}