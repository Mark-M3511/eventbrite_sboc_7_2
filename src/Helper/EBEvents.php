<?php
namespace Drupal\eventbrite_sboc\Helper;
// http://www.eventbrite.com/developer/v3/reference/parameters

use Drupal\eventbrite_sboc\Helper\EBConsts;
use Drupal\eventbrite_sboc\Helper\EBOAuth;

class EBEvents{

  protected $oauthClient;
  public $eventId;
  
  /**
  * Creates an instance of EBEvents and returns that object to the caller
  *
  * @params object 
  *
  * Returns implicit object of type EBEvents
  */ 
  public function __construct($oauth_client, $event_id=""){
    $this->oauthClient = $oauth_client;
    $this->eventId = $event_id;
  }
  
  /**
  * Retrieves athorization/bearer token from database
  *
  * @params None 
  *
  * Returns String in the format Bearer XXXXXXXXXXX
  */
  protected function getBearerCredentials(){
    $token = $this->oauthClient->getAccessToken()->accessToken; 
    $token_type = $this->oauthClient->getAccessTokenType()->tokenType;
    if ($token_type === 'bearer') {
      $token_type = 'Bearer';
    }
    
    return "{$token_type} {$token}";
  }
  
  /**
  * Retrieves Event information from Eventbrite
  *
  * @params String $event_id 
  *
  * Returns Object Eventbrite Event
  */
  public function getEventInfo(){     
    $headers = array(
      'Authorization' => $this->getBearerCredentials(),
    );
    
    $options = array( 
      'headers' => $headers,
    ); 
    
    $eb_params = format_string(EBConsts::EBS_ENDPOINT_EVENTS_SINGLE, array('@event_id' => $this->eventId,));
    
    try{
      $response = drupal_http_request(EBConsts::EBS_URL_EVENTBRITE_REST_API. $eb_params, $options);
      $retval = $this->oauthClient->parseResponse($response);
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
    }     
    return $retval;     
  }
  
  /**
  * Retrieves Event Attendees information from Eventbrite
  *
  * @params String $event_id 
  * @params Array $params 
  *
  * Returns Array of Eventbrite Attendee objects
  */ 
  public function getEventAttendees($params=array()){  
    $headers = array(
      'Authorization' => $this->getBearerCredentials(),
    );
    
    $options = array(
      'headers' => $headers,
    ); 
    
    $query = '';
    if (!empty($params)){
      $query = '?'. drupal_http_build_query($params);
    }
    
    try{
      $eb_params = format_string(EBConsts::EBS_ENDPOINT_ATTENDEES, array('@event_id' => $this->eventId, '!query' => $query,));
      $response = drupal_http_request(EBConsts::EBS_URL_EVENTBRITE_REST_API. $eb_params, $options);
      $retval = $this->oauthClient->parseResponse($response);
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
    }
    return $retval;    
  }
  
  /**
  * Retrieves Event Attendees information from Eventbrite
  *
  * @params String $event_id 
  * @params Array $params 
  *
  * Returns Array of Eventbrite Attendee objects
  */
  public function getOrderByNumber($order_id, $expansions = ''){
    $headers = array(
      'Authorization' => $this->getBearerCredentials(),
    );
    
    $options = array(
      'headers' => $headers,
    );
    
    $query = '';
    if (!empty($expansions)){
      $query = '?expand='. $expansions;
    }
     
    try{
      $eb_params = format_string(EBConsts::EBS_ENDPOINT_ORDERS, array('@order_id' => $order_id,));
      $response = drupal_http_request(EBConsts::EBS_URL_EVENTBRITE_REST_API. $eb_params. $query, $options);
      $retval = $this->oauthClient->parseResponse($response);
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
    }  
    return $retval;
  }
  
  /**
  * Retrieves Eventbrite Orders by url
  *  Used with order webhook to retireve attendees by order
  * @params String $url 
  *   Must be a fully qualified url
  * @params String $expansions
  *   Comma separated list of additional fields/objects
  * Returns Array of Eventbrite Attendee objects
  */
  public function getOrderByUrl($url, $expansions=''){
    $headers = array(
      'Authorization' => $this->getBearerCredentials(),
    );
    
    $options = array(
      'headers' => $headers,
    );
    
    if (!empty($expansions)){
      $url .= '?expand='. $expansions;
    }
    
    $url = format_string('@url', array('@url' => $url,));
    
    try{
      $response = drupal_http_request($url, $options);
      $retval = $this->oauthClient->parseResponse($response);
    }catch(Exception $e){
      watchdog_exception(__CLASS__. '~'. __METHOD__, $e);
    } 
    return $retval;
  }

}