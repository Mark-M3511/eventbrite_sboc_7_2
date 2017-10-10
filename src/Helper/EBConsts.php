<?php
/*
*  Constants used throughout the application
*/

namespace Drupal\eventbrite_sboc\Helper;

class EBConsts{
  // App Identification
  const EBS_APPNAME = 'eventbrite_sboc';
  const EBS_APP_NAME_MAIN = self::EBS_APPNAME;
//   const EBS_APPNAME_ATTENDEES = 'eventbrite_sboc_attendees';
  const EBS_APPNAME_ATTENDEE = 'eventbrite_sboc_attendee';
  const EBS_APPNAME_ATTENDEES = self::EBS_APPNAME_ATTENDEE;
  const EBS_APPNAME_MAIL = 'eventbrite_sboc_mail';
  // Permissions
  const EBS_ADMINISTER_MAIN_MODULE = 'administer ebs main module';
  const EBS_ADMINISTER_ATTENDEES_MODULE = 'administer ebs attendees module';
  const EBS_ADMINISTER_MAILHANDLER_MODULE = 'administer ebs mailhandler module';
  // variables/configs
  const EBS_CONFIG_CONSUMER_KEY = 'ebs_consumer_key';
  const EBS_CONGIFG_EB_CLIENT_ID = self::EBS_CONFIG_CONSUMER_KEY;
  const EBS_CONFIG_CONSUMER_SECRET = 'ebs_consumer_secret';
  const EBS_CONFIG_EVENT_ID = 'ebs_event_id';
  const EBS_CONFIG_AUTHTOKEN = 'ebs_authtoken';
  const EBS_AUTHTOKEN_TYPE = 'ebs_authtoken_type';
  const EBS_CONFIG_AUTHTOKEN_TYPE = self::EBS_AUTHTOKEN_TYPE;
  const EBS_CONFIG_CHANGED_SINCE_DAYS = 'ebs_changed_since_days';
  const EBS_CONFIG_CHANGED_SINCE_HOURS = 'ebs_changed_since_hours';
  const EBS_CONFIG_EMAIL_WELCOME_MESSAGE = 'ebs_email_message';  
  const EBS_CONFIG_DEFAULT_CHANGED_SINCE_DAYS = 7;
  const EBS_CONFIG_DEFAULT_CHANGED_SINCE_HOURS = 1;
  const EBS_CONFIG_PARAM_CHANGED_SINCE = 'changed_since';
  const EBS_CONFIG_EMAIL_MESSAGE_NODE_ID = 'ebs_email_msg_node_id';
  const EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_1 = self::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID;
  const EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_2 = 'ebs_email_msg_node_id_2';
  const EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_3 = 'ebs_email_msg_node_id_3';
  // Callbacks
  const EBS_FUNC_EMAIL_CALLBACK = '_eventbrite_sboc_invoke_mail';
  const EBS_FUNC_EMAIL_CALLBACK_2 = '_eventbrite_sboc_invoke_mail_internal';
  // webhook json api data
  const EBS_JSON_PARAM_API_URL = 'api_url';
  // Title
  const EBS_APPLICATION_TITLE = 'Eventbrite SBOC v2';
  // Oauth
  const EBS_OAUTH2_SETTINGS_LABEL = 'ebv3';
  const EBS_EB_RESPONSE_TYPE_CODE = 'code';
  const EBS_EB_GRANT_TYPE_AUTH_CODE = 'authorization_code';
  // Eventbrite Tests
  const EBS_WEBHOOK_TEST_URL_PATH = '{api-endpoint-to-fetch-object-details}';
  // Eventbrite Webhook API
  const EBS_WEBHOOK_CONFIG_ACTION_ORDER_PLACED = 'order.placed';
  const EBS_WEBHOOK_CONFIG_ACTION_ORDER_UPDATED = 'order.updated';
  // db table
  const EBS_DBTABLE_ATTENDEES = 'eventbrite_sboc_attendees';  
  // entity name
  const EBS_ENTITY_CLASS_NAME = 'EBAttendeeEntity';
  const EBS_ENTITY_TYPE_NAME = 'eventbrite_sboc_attendee';
  const EBS_ENTITY_TYPE_ATTENDEE = self::EBS_ENTITY_TYPE_NAME;
  // urls & endpoints   
  const EBS_ADMINMENUROOT = 'admin/config/system/eventbrite_sboc';
  const EBS_EVENTBRITEOAUTH = 'https://www.eventbrite.com/oauth';
  const EBS_URL_EVENTBRITE_OAUTH = self::EBS_EVENTBRITEOAUTH;
  const EBS_EVENTBRITERESTAPIv3 = 'https://www.eventbriteapi.com/v3';
  const EBS_URL_EVENTBRITE_REST_API = self::EBS_EVENTBRITERESTAPIv3;
  const EBS_APPLICATIONDOMAIN_SITE_APP_DEV = 'http://dev1508.holbrookgoodman.com';
  const EBS_APPLICATIONDOMAIN_SITE_DEV = 'http://sboc2015.holbrookgoodman.com';
  const EBS_APPLICATIONDOMAIN_SITE_LIVE = 'http://spellingbeeofcanada.ca';
  const EBS_APPLICATIONDOMAIN = self::EBS_APPLICATIONDOMAIN_SITE_APP_DEV;
  const EBS_ENDPOINT_TOKEN = '/token';
  const EBS_ENDPOINT_AUTHORIZE = '/authorize';
  const EBS_ENDPOINT_EVENTS_SINGLE = '/events/@event_id';
  const EBS_ENDPOINT_ATTENDEES = '/events/@event_id/attendees!query';
  const EBS_ENDPOINT_ORDERS = '/orders/@order_id';
  // expansions
  const EBS_ORDER_EXPANSIONS = 'attendees,event';
  const EBS_ORDER_EXPANSIONS_ATTENDEES = 'attendees';
  // urls & callbacks
  const EBS_WEBHOOK_ORDER_PLACED = 'sbocevt/presto/2015';
  const EBS_URL_WEBHOOK_ORDER_PLACED = self::EBS_WEBHOOK_ORDER_PLACED;
  const EBS_APPLICATIONCALLBACKURL = '/admin/config/system/eventbrite_sboc/oauthcallback';
  const EBS_URL_OAUTH_CALLBACK = self::EBS_APPLICATIONCALLBACKURL;
  const EBS_URL_OAUTH_FLOW_SUCCESS = 'sbocevt/oauthflowsuccess';
  const EBS_URL_OAUTH_FLOW_FAILURE = 'sbocevt/oauthflowfailure';
  // custom protocol: php input stream
  const EBS_PHP_INPUT_STREAM = 'php://input';
  // date formats
  const EBS_MYSQLDATEFORMAT = 'Y-m-d H:i:s';
  /* A datetime represented as a string in ISO8601 combined date and time format, always in UTC. */
  const EBS_EBDATEFORMAT = 'Y-m-d\TH:i:s\Z';
  const EBS_EBDATEFORMAT_UTC = self::EBS_EBDATEFORMAT;
  /* A datetime represented as a string in Naive Local ISO8601 date and time format, in the timezone of the event. */
  const EBS_EBDATEFORMAT_NAIVE_LOCAL = 'Y-m-d\TH:i:s';
  // Queue worker information
  const EBS_QUEUE_EMAIL_WELCOME = 'EBS_QUEUE_EMAIL_WELCOME';
  const EBS_QUEUE_PROCESS_CHANGED_RECORDS = 'ebs_process_changed_records';
  const EBS_CRONJOB_TITLE = 'Eventbrite SBOC v2';
  const EBS_CRON_JOB_KEY1 = 'EBS_CRON_KEY1';
  const EBS_CRONRULE_DEFAULT = '*/5 * * * *';
  // Email
  const EBS_SBOC_ATTENDEES_MAIL_KEY = 'sboc-mail';
  const EBS_SBOC_ATTENDEES_MAIL_KEY_INTERNAL = 'sboc-mail-internal';
  const EBS_SBOC_MAILER_MODULE = 'eventbrite_sboc_mailer';
  const EBS_SBOC_EMAIL_ADDRESS = 'info@spellingbeeofcanada.ca';
  const EBS_TESTSUBJECT = 'What is your real subject?';
  const EBS_TESTBODY = 'Howdy, buddy <b>*|FIRST_NAME|* *|LAST_NAME|*</b> !!';
  const EBS_EMAILSENT_STATUS_SUCCESS = 'sent';
  const EBS_EMAIL_NODE_ID = 1;
  const EBS_ENTITY_EMAIL_FIELD = 'emailAddress';
  // Eventbrite DB Fields
  const EBS_FIELDS_EID = 'eid';
  const EBS_FIELDS_EVENT_ID = 'event_id';
  const EBS_FIELDS_ORDER_ID = 'order_id';
  const EBS_FIELDS_ATTENDEE_ID = 'attendee_id';
  // keys
  const EBS_FIELDS_EVENTBRITE_KEY = self::EBS_FIELDS_ATTENDEE_ID;
  const EBS_FIELDS_ENTITY_KEY = self::EBS_FIELDS_EID;
  // EB Questions
  const EBS_QA_101 = "Contestant's Category (b.b. = born between)";
  const EBS_QA_102 = "Contestant's Region";
  const EBS_QA_103 = "Contestant's Last Name";
  const EBS_QA_104 = "Contestant's First Name";
  const EBS_QA_105 = "Contestant's Gender";
  const EBS_QA_106 = "Contestant's Grade";  
  const EBS_QA_107 = "Contestant's School";    
  const EBS_QA_108 = "Contestant's Year of Birth";
  const EBS_QA_109 = "Contestant's Month Of Birth";  
  const EBS_QA_110 = "Contestant's Day Of Birth";  
  const EBS_QA_111 = "Contestant's Address Line 1";    
  const EBS_QA_112 = "Contestant's Address Line 2";    
  const EBS_QA_113 = "Contestant's City";    
  const EBS_QA_114 = "Contestant's Province";        
  const EBS_QA_115 = "Contestant's Postal Code";  
  const EBS_QA_116 = "Contestant's Phone Number";  
  const EBS_QA_117 = "Contestant's Phone Number 2";    
  const EBS_QA_118 = "I consent to receiving email communications from SBOC";      
  const EBS_QA_119 = "Additional information for SBOC";
  const EBS_QA_120 = "Language";
  // EB Values
  const EBS_UNSPECIFIED_REGION = 'Unspecified Region';
  const EBS_ORDER_CANCELLED = 'RC';
  const EBS_ORDER_COMPLETED = 'PC';
  const EBS_REGTYPE_PREMIUM = 'Premium';
  const EBS_UNSPECIFIED_VALUE = 'Unspecified';
  const EBS_UNSPECIFIED_YOB = 1900;
  const EBS_UNSPECIFIED_MOB = 1;
  const EBS_UNSPECIFIED_DOB = 1;
  const EBS_EMPTY_STRING = '';
  const EBS_UNSPECIFIED_PROV = 'ON';
  // files
//  const EBS_ENTITY_PROPS_FILE = 'inc/entity_ebattendee.properties.inc';
  const EBS_ENTITY_CLASS_FILE = 'inc/entity_ebattendee.classes.inc'; 
  // roles
  const EBS_DRUPAL_ROLE_AUTHENTICATED_USER = 'authenticated user';    
  const EBS_DRUPAL_ROLE_ENROLLED_USER = 'enrolled user';
  // http response code
  const EBS_HTTP_RESPONSE_CODE_OK = 200;    
  // Error messages
  const EBS_ERROR_NO_MATCHING_RECORD = 'No matching record found.';
  //Email message
  const EBS_EMAIL_DEFAULT_SUBJECT = 'Message from Spelling Bee Of Canada';
  const EBS_EMAIL_DEFAULT_MESSAGE = 'Dear Reader:<br /><br />Thank you for your interest. Please visit us at <a href="http://spellingbeeofcanada.ca">spellingbeeofcanada.ca</a><br /><br />Sincerely,<br />Spelling Bee Of Canada';
  // drupal_http_query options
  const EBS_DRUPAL_HTTP_QUERY_MAX_REDIRECTS = 10;
  // Timezomes
  const tz_UTC = 'UTC';
  const tz_Site_Default = 'America/Toronto';
}