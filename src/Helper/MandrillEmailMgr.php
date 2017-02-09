<?php

namespace Drupal\eventbrite_sboc\Helper;

use Drupal\eventbrite_sboc\Helper\EBConsts;
use Drupal\eventbrite_sboc\Helper\SBOCDBMgr;

/**
* Interface iEmailMgr  
*    Outlines the public methods and properties that must be present in clasees
*    implementing this interface
* @function send()
*/ 
interface iEmailMgr{
  public function send();
  public function sendInternal();
}

/**
* Class MandrillEmailMgr  
*    Implements interface iEmailMgr
*    Manages email transactions using MailChimp's mandrill.com API
*/
class MandrillEmailMgr implements iEmailMgr{
  public $attendees;
  public $moduleName;
  public $mailKey;
  public $params;
  public $templateId;
  
  /**
  * Creates an instance of MandrillEmailMgr and returns that object to the caller
  *
  * @params string $entity_name (default = "eventbrite_sboc_attendee")
  * @params array $atendees (default = empty array)
  *
  * Returns implicit object of type MandrillEmailMgr
  */ 
  public function __construct(array $attendees = array(), $module_name = EBConsts::EBS_SBOCATTENDEES_MODULE,
      $mail_key = EBConsts::EBS_SBOCATTENDEES_MAIL_KEY){
      
    $this->attendees = $attendees;
    $this->moduleName = $module_name;
    $this->mailKey = $mail_key;
    $this->params = array();
    $this->templateId = '';
  }
  
  /**
  * Initializes global variables for use by Mandrill
  *
  * @params array $$mandrill_params
  * @params array $message
  *
  * Returns array
  */
  public function setGlobalMergeVars(&$mandrill_params, $message){
    $global_merge_vars = array();

    /* -- Also availabe -- */
    /* $attendee = $message['params']['attendee']; */
    
    $global_merge_vars[] = array(
      'name' => 'subject',
	    'content' => check_plain($message['params']['subject']),
    );

    if (isset($message['params']['from'])) {
      $global_merge_vars[] = array(
        'name' => 'from',
        'content' => $message['params']['from'],
      );
    }

    if (isset($message['params']['reply-to'])) {
      $global_merge_vars[] = array(
        'name' => 'headers',
        'content' => array(
          'Reply-To' => $message['params']['reply-to'],
        ),
      );
    }


    $global_merge_vars[] = array(
      'name' => 'current_year',
      'content' => date('Y'),
    );
    
    $mandrill_params['message']['global_merge_vars'] = $global_merge_vars;
    
    return $mandrill_params['message']['global_merge_vars'];
  }
  
  /**
  * Initializes recipient specific variables for use by Mandrill
  *
  * @params array $$mandrill_params
  * @params array $message
  *
  * Returns array
  */
  public function setMergeVars(&$mandrill_params, $message){
    $attendee = $message['params']['attendee'];
    
    /* an array of arrays of per recipient values */
    $merge_vars = array();       
    /* We should use a simple object - set up recipient values */

    /* per recipient values */
    $vars = array(); 
    $fields = SBOCDBMgr::mapValues($attendee);
    foreach($fields as $field => $value) {
      if (!is_array($value) && !is_object($value)){
        $vars[] = array(
          'name' => $field,
          'content' => check_plain($value),
	      );
	    }
    }    
    
    $merge_vars[] = array(
      'rcpt' => $fields['email_address'],
      'vars' => $vars,
    );  

    $mandrill_params['message']['merge_vars'] = $merge_vars;
    
    return $mandrill_params['message']['merge_vars'];    
  }
  
  /**
  * Sends emails via Mandrils API
  *
  * Returns n/a
  */
  public function send(){
    $language = language_default();
    $params = array();
    $params['mail_object'] = $this;
    $params += $this->params;
    $from = variable_get('site_mail', EBConsts::EBS_SBOCEMAILADDRESS);
    $send = FALSE;
    try{
      foreach($this->attendees as $attendee){
        $params['attendee'] = clone $attendee;
        $to = $attendee->emailAddress;
        $result = drupal_mail($this->moduleName, $this->mailKey, $to, $language, $params, $from, $send);
        $system = drupal_mail_system($this->moduleName, $this->mailKey);
        $message = $system->format($result);
        $system->mail($result);
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APPNAME_MAIL, $e);
    }
  }

  public function sendInternal() {
    // TODO: 1: Add template to Mandrill dashboard 2:Import template 3: Add node/post with message 4:Test
    $language = language_default();
    $params = array();
    $params['mail_object'] = $this;
    $params += $this->params;
    $to = variable_get('site_mail', EBConsts::EBS_SBOCEMAILADDRESS);
    $send = FALSE;
    try{
      foreach ($this->attendees as $attendee){
        if (!empty($attendee->additionalInfo)) {
          $params['attendee'] = clone $attendee;
          $from = $attendee->emailAddress;
          if (isset($params['reply-to'])){
            $params['reply-to'] = $from;
          }
          if (!isset($params['from'])){
            $params['from'] = $from;
          }
          $result = drupal_mail($this->moduleName, $this->mailKey, $to, $language, $params, $from, $send);
          $system = drupal_mail_system($this->moduleName, $this->mailKey);
          $message = $system->format($result);
          $system->mail($result);
        }
      }
    }catch(Exception $e){
      watchdog_exception(EBConsts::EBS_APPNAME_MAIL, $e);
    }
  }

}

  