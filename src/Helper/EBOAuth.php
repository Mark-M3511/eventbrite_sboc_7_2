<?php

// https://www.drupal.org/node/2473287
namespace Drupal\eventbrite_sboc\Helper;

use Drupal\eventbrite_sboc\Helper\EBConsts;

class EBOAuth{
  public $consumerKey;
  public $consumerSecret;
  public $clientId;
  public $accessToken;
  public $tokenType;
  
 /**
  * Returns a single user object
  *
  * @param string $consumer_key
  * @param string $consumer_secret
  *
  * Returns object
  */    
  public function __construct($consumer_key='', $consumer_secret=''){
    $this->consumerKey = $consumer_key;
    $this->consumerSecret = $consumer_secret;
    $this->accessToken = '';
    $this->tokenType = '';
    // clientId is an Eventbrite API alias for consumer Key
    $this->clientId = $this->consumerKey;
  }
  
  /**
  * Returns an object parsed from the data returned from Eventbrite API
  *
  * @param object $response_object
  *
  * Returns object
  */  
  public function parseResponse($response_object){
    $retval = null;
    if (is_object($response_object)) {
       if (!empty($response_object->data)){
         $temp_array = drupal_json_decode($response_object->data);
         $retval = (object)$temp_array;
       }
    }
    return $retval;
  }
  
  /**
  * Returns true/false after validating consumer public and secret keys
  *
  * @param none
  *
  * Returns boolean
  */ 
  protected function validCredentials(){
    $valid_credentials = !empty($this->consumerKey);
    $valid_credentials = ($valid_credentials && !empty($this->consumerSecret));
    $valid_credentials = ($valid_credentials && $this->consumerKey === variable_get('ebs_consumer_key', '')); 
    $valid_credentials = ($valid_credentials && $this->consumerSecret === variable_get('ebs_consumer_secret', ''));
    
    return $valid_credentials;
  }
  
  /**
  * Saves the access token in the config table
  *  We should save the access token per subscriber/site
  *
  * @param string $access_token
  * @param string $bearer_type
  *
  * Returns n/a
  */ 
  public function setAccessToken($access_token, $token_type="bearer"){
    /* Access Check */ 
    if (!$this->validCredentials()){
       return;
    }
    /* Access Check */ 
    variable_set(EBConsts::EBS_CONFIG_AUTHTOKEN, $access_token);
    variable_set(EBConsts::EBS_CONFIG_AUTHTOKEN_TYPE, $token_type);
  }
  
  /**
  * Returns authroization type 
  *  Currently "bearer"is the default and only used type
  * @param none
  *
  * Returns object of type EBOAuth
  */ 
  public function getAccessTokenType(){
    $this->tokenType = variable_get(EBConsts::EBS_CONFIG_AUTHTOKEN_TYPE, 'bearer');
    return $this;
  }
  
  /**
  * Returns Bearer token used to access Eventbride data on behalf of a subscriber
  *    Checks for valid consumer keys before returrning the authorization token
  * @param none
  *
  * Returns object of type EBOAuth
  */ 
  public function getAccessToken(){
   
    /* Access Check */ 
    if (!$this->validCredentials()){
       return '';
    }
    /* Access Check */
    
    $this->accessToken = variable_get(EBConsts::EBS_CONFIG_AUTHTOKEN, '');
    
    return $this;
  }
  
  /*
  public function getEBAccessAuthorization($oauth2_client){
    try {
      $oauth2_client = oauth2_client_load($oauth2_client);
      $access_token = $oauth2_client->getAccessToken();
    }catch (Exception $e) {
      watchdog_exception(EBConsts::EBS_APPNAME, $e);
    }
  }
  */
  
  
  /**
  * Initiates OAuth sequence with consumer keys (public/secret) obtained from issuer (Eventbrite)
  *  Requests response type of "code"
  *  Eventbrite client_id = OAuth consumer public key
  *
  * @param none
  *
  * Returns n/a
  */
  public function getEBAccessAuthorization(){
    $params = array(
      'response_type' => EBConsts::EBS_EB_RESPONSE_TYPE_CODE,
      'client_id' => $this->clientId,
    ); 
    $query = '?'. drupal_http_build_query($params); 
    try{
      $response = drupal_http_request(EBConsts::EBS_URL_EVENTBRITE_OAUTH. EBConsts::EBS_ENDPOINT_AUTHORIZE. $query);
      if ($response->code == EBConsts::EBS_HTTP_RESPONSE_CODE_OK && !empty($response->redirect_url)){
        drupal_goto($response->redirect_url);
      }else{
        throw new \Exception(format_string('Code: @code / Error: @error',
          array('@code' => $response->code, '@error' => $response->error, ))); 
      }
    }catch (Exception $e) {
      watchdog_exception(EBConsts::EBS_APPNAME, $e);
      drupal_goto(url('<front>'));
    }
  }
  
  public function getEBAccessToken($auth_code){   
    if (empty($auth_code)){
      drupal_goto(EBConsts::EBS_URL_OAUTH_FLOW_FAILURE); 
      return null;
    }
    
    $data = array(
      'code' => check_plain($auth_code),
      'client_secret' =>  $this->consumerSecret,
      'client_id' => $this->consumerKey,
      'grant_type' => EBConsts::EBS_EB_GRANT_TYPE_AUTH_CODE,
    );
    
    $headers = array(
      'Content-Type' => 'application/x-www-form-urlencoded',
    );
    
    $options = array(
      'data' => drupal_http_build_query($data),
      'headers' => $headers,
      'method' => 'POST',
    );
    
    $retval = null;
    try{
      $response = drupal_http_request(EBConsts::EBS_URL_EVENTBRITE_OAUTH. EBConsts::EBS_ENDPOINT_TOKEN, $options);
      $retval = $this->parseResponse($response);
      if (!empty($retval->access_token)){
        $this->setAccessToken($retval->access_token, $retval->token_type);
        drupal_goto(EBConsts::EBS_URL_OAUTH_FLOW_SUCCESS);
      }else{
        drupal_goto(EBConsts::EBS_URL_OAUTH_FLOW_FAILURE); 
      }
    }catch (Exception $e) {
      watchdog_exception(EBConsts::EBS_APPNAME, $e);
    }
    
    return $retval;
  }
  
}