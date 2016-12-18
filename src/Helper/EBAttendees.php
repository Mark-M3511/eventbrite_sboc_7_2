<?php

namespace Drupal\eventbrite_sboc\Helper;
// http://www.eventbrite.com/developer/v3/reference/parameters

use Drupal\eventbrite_sboc\Helper\EBConsts;
use Drupal\eventbrite_sboc\Helper\EBOAuth;
use Drupal\eventbrite_sboc\Helper\EBEvents;
use Drupal\eventbrite_sboc\Helper\SBOCDBMgr;

class EBAttendee{
  public $eventId;
  public $uid;
  public $attendeeId;
  public $orderId;
  public $ticketClassId;
  public $createDate;
  public $emailAddress;
  public $lastName;
  public $firstName;
  public $category;
  public $categoryNid;
  public $orderType;
  public $emailSent;
  public $emailSendDate;
  public $regType;
  public $regionName;
  public $regionNid;
  public $contestantLastName;
  public $contestantFirstName;  
  public $gender;
  public $grade;
  public $school;
  public $yearOfBirth;
  public $monthOfBirth;
  public $dayOfBirth;
  public $homeAddressLine1;
  public $homeAddressLine2;
  public $homeCity;
  public $homeProvState;
  public $homePostalZip;
  public $homePhone1;
  public $homePhone2;
  public $changeDate;
  public $emailConsent;
  public $additionalInfo;
  public $passwordResetUrl;
  public $changedFields;
  public $language;
}

class EBAttendees{
  public $attendees;
  public $event;
  
  public function __construct($event = NULL){
    $this->attendees = array();
    $this->event = $event;
  }
  
  protected function getAnswer(array $answers, $question, $default){
    $ret_val = $default;
    foreach($answers as $id => $answer){
      if ($question == $answer['question'] && !empty($answer['answer'])){
        $ret_val = $answer['answer'];
        switch($answer['question']){
          case EBConsts::EBS_QA_101:
            $ret_val = $answer['answer'][0];
            break; 
          case EBConsts::EBS_QA_105:
            $ret_val = $answer['answer'][0];
            break; 
          case EBConsts::EBS_QA_118:
            $ret_val = (drupal_strtoupper($answer['answer']) == 'YES' ? 1 : 0);
            break;
          case EBConsts::EBS_QA_120:
            $ret_val = drupal_strtolower(drupal_substr($answer['answer'], 0, 2));
            $ret_val = empty($ret_val) ? 'en' : $ret_val;
            break;
          default:
            break;
        }
      }
    }
    return $ret_val;
  }
  
  public function loadAttendeesFromResource(array $params){
    if (empty($this->event)){
      return $this->attendees;
    }
    
    $response = $this->event->getEventAttendees($params);
    
    if (isset($response) && !isset($response->attendees)){
      return $this->attendees;
    }
    
    foreach($response->attendees as $attendee){
      $a = new EBAttendee();
      $create_date = date(EBConsts::EBS_MYSQLDATEFORMAT, strtotime(str_ireplace('Z', '', $attendee['created'])));
      $change_date = date(EBConsts::EBS_MYSQLDATEFORMAT, strtotime(str_ireplace('Z', '', $attendee['changed'])));
//      $create_date = SBOCDBMgr::convert_date_tz($create_date);
//      $change_date = SBOCDBMgr::convert_date_tz($change_date);
      $a->eventId = $attendee['event_id'];
      $a->attendeeId = $attendee['id'];
      $a->orderId = $attendee['order_id'];
      $a->ticketClassId = $attendee['ticket_class_id'];
      $a->createDate = $create_date;
      $a->changeDate = $change_date;
      $a->emailAddress = $attendee['profile']['email'];
      $a->lastName = $attendee['profile']['last_name'];
      $a->firstName = $attendee['profile']['first_name'];
      $a->category = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_101, 'P');
      $a->orderType = ((!empty($attendee['cancelled']) || !empty($attendee['refunded'])) ? EBConsts::EBS_ORDER_CANCELLED: EBConsts::EBS_ORDER_COMPLETED);
      $a->emailSent = 0;      
      $a->emailSendDate = NULL;
      $a->regType = EBConsts::EBS_REGTYPE_PREMIUM;
      $a->regionName = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_102, EBConsts::EBS_UNSPECIFIED_REGION);
      $a->contestantLastName = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_103, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->contestantFirstName = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_104, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->gender = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_105, 'M');
      $a->grade = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_106, 0);
      $a->school = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_107, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->yearOfBirth = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_108, EBConsts::EBS_UNSPECIFIED_YOB);
      $a->monthOfBirth = SBOCDBMgr::month_num($this->getAnswer($attendee['answers'], EBConsts::EBS_QA_109, EBConsts::EBS_UNSPECIFIED_MOB));
      $a->dayOfBirth = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_110, EBConsts::EBS_UNSPECIFIED_DOB);
      $a->homeAddressLine1 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_111, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->homeAddressLine2 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_112, EBConsts::EBS_EMPTY_STRING);
      $a->homeCity = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_113, EBConsts::EBS_UNSPECIFIED_PROV);
      $a->homeProvState = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_114, 'ON');;
      $a->homePostalZip = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_115, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->homePhone1 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_116, EBConsts::EBS_EMPTY_STRING);
      $a->homePhone2 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_117, EBConsts::EBS_EMPTY_STRING);
      $a->emailConsent = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_118, 0);
      $a->additionalInfo = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_119, EBConsts::EBS_EMPTY_STRING);
      $a->language =  $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_120, EBConsts::EBS_EMPTY_STRING);
      
      $this->attendees[$a->attendeeId] = $a;
    }
    return $this->attendees;
  }
  
  function loadAttendeesFromArray(array $attendees){
    foreach($attendees as $attendee){
      $a = new EBAttendee();
      $create_date = date(EBConsts::EBS_MYSQLDATEFORMAT, strtotime(str_ireplace('Z', '', $attendee['created'])));
      $change_date = date(EBConsts::EBS_MYSQLDATEFORMAT, strtotime(str_ireplace('Z', '', $attendee['changed'])));
//      $create_date = SBOCDBMgr::convert_date_tz($create_date);
//      $change_date = SBOCDBMgr::convert_date_tz($change_date);
      $a->eventId = $attendee['event_id'];
      $a->attendeeId = $attendee['id'];
      $a->orderId = $attendee['order_id'];
      $a->ticketClassId = $attendee['ticket_class_id'];
      $a->createDate = $create_date;
      $a->changeDate = $change_date;
      $a->emailAddress = $attendee['profile']['email'];
      $a->lastName = $attendee['profile']['last_name'];
      $a->firstName = $attendee['profile']['first_name'];
      $a->category = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_101, 'P');
      $a->orderType = ((!empty($attendee['cancelled']) || !empty($attendee['refunded'])) ? EBConsts::EBS_ORDER_CANCELLED: EBConsts::EBS_ORDER_COMPLETED);
      $a->emailSent = 0;      
      $a->emailSendDate = NULL;
      $a->regType = EBConsts::EBS_REGTYPE_PREMIUM;
      $a->regionName = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_102, EBConsts::EBS_UNSPECIFIED_REGION);
      $a->contestantLastName = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_103, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->contestantFirstName = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_104, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->gender = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_105, 'M');
      $a->grade = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_106, 0);
      $a->school = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_107, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->yearOfBirth = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_108, EBConsts::EBS_UNSPECIFIED_YOB);
      $a->monthOfBirth = SBOCDBMgr::month_num($this->getAnswer($attendee['answers'], EBConsts::EBS_QA_109, EBConsts::EBS_UNSPECIFIED_MOB));
      $a->dayOfBirth = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_110, EBConsts::EBS_UNSPECIFIED_DOB);
      $a->homeAddressLine1 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_111, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->homeAddressLine2 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_112, EBConsts::EBS_EMPTY_STRING);
      $a->homeCity = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_113, EBConsts::EBS_UNSPECIFIED_PROV);
      $a->homeProvState = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_114, 'ON');;
      $a->homePostalZip = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_115, EBConsts::EBS_UNSPECIFIED_VALUE);
      $a->homePhone1 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_116, EBConsts::EBS_EMPTY_STRING);
      $a->homePhone2 = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_117, EBConsts::EBS_EMPTY_STRING);
      $a->emailConsent = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_118, 0);
      $a->additionalInfo = $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_119, EBConsts::EBS_EMPTY_STRING);
      $a->language =  $this->getAnswer($attendee['answers'], EBConsts::EBS_QA_120, EBConsts::EBS_EMPTY_STRING);
      
      $this->attendees[$a->attendeeId] = $a;
    }
    return $this->attendees;
  }
  
  function mapEBAttendeeEntitytoEBAttendee(array $attendees){
    foreach($attendees as $attendee){
      $r = (empty($r) ? new \ReflectionClass($attendee) : $r);
      // If for any reason we have a class without properties
      // go to the top of the loop
      // ***** bof: Check instance
      if (count($r->getProperties()) == 0){
        continue; 
      }
      // ***** eof: Check instance
      $a = new EBAttendee();
      $a->eventId = $attendee->event_id;
      if ($r->hasProperty('uid')){
        $a->uid = $attendee->uid;
      }
      if ($r->hasProperty('category_nid')){
        $a->categoryNid = $attendee->category_nid;
      }
      if ($r->hasProperty('region_nid')){
        $a->regionNid = $attendee->region_nid;
      }
      
      if ($r->hasProperty('email_sent')){
        $a->emailSent = $attendee->email_sent;      
      }  
      
      if ($r->hasProperty('email_send_date')){
        $a->emailSendDate = $attendee->email_send_date;
      }
      
      $a->attendeeId = $attendee->attendee_id;
      $a->orderId = $attendee->order_id;
      $a->ticketClassId = $attendee->ticket_class_id;
      $a->createDate = $attendee->create_date;
      $a->changeDate = $attendee->change_date;
      $a->emailAddress = $attendee->email_address;
      $a->lastName = $attendee->last_name;
      $a->firstName = $attendee->first_name;
      $a->category = $attendee->category;
      $a->orderType = $attendee->order_type;
      $a->regType = $attendee->reg_type;
      $a->regionName = $attendee->region_name;
      $a->contestantLastName = $attendee->contestant_last_name;
      $a->contestantFirstName = $attendee->contestant_first_name;
      $a->gender = $attendee->gender;
      $a->grade = $attendee->grade;
      $a->school = $attendee->school;
      $a->yearOfBirth = $attendee->year_of_birth;
      $a->monthOfBirth = $attendee->month_of_birth;
      $a->dayOfBirth = $attendee->day_of_birth;
      $a->homeAddressLine1 = $attendee->home_address_line_1;
      $a->homeAddressLine2 = $attendee->home_address_line_2;
      $a->homeCity = $attendee->home_city;
      $a->homeProvState = $attendee->home_prov_state;
      $a->homePostalZip = $attendee->home_postal_zip;
      $a->homePhone1 = $attendee->home_phone_1;
      $a->homePhone2 = $attendee->home_phone_2;
      $a->emailConsent = $attendee->email_consent;
      $a->additionalInfo = $attendee->additional_info;
      $a->language = $attendee->language;
      
      $this->attendees[$a->attendeeId] = $a;
    }
    return $this->attendees;
  }
  
  public function getCategoryNodeId($category, $event_id, $language){
    return (new SBOCDBMgr())->getCategoryNodeId($category, $event_id, $language);
  }
  
  public function getRegionNid($region_nid){
    return (new SBOCDBMgr())->getRegionNid($region_nid);
  }
  
}