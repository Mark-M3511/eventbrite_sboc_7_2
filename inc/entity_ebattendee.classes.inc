<?php

/*
* Extending Entity base class to represent our new entity
*/
class EBAttendeeEntity extends Entity{
  protected function defaultLabel(){
    return $entity->event_id. '-'. $entity->attendee_id; 
  }
  
  protected function defaultUri(){
    return array('path' => 'ebattendee/' . $this->identifier());
  }
}

/*
* Extending the EntityAPIController for the Attendee entity.
*/
class EBAttendeeEntityController extends EntityAPIController{
   public function save($entity){
     $retval = parent::save($entity);
     return $retval;
   }
   
   protected function realLoad($ids, $conditions){
     $dba = new Drupal\eventbrite_sboc\Helper\SBOCDBMgr();
     $attendees_ids = $dba->legacyLoadAttendees($ids);
     return $attendees_ids;
   }
   
   public function load($ids = array(), $conditions = array()){
     $attendee_ids = $this->realLoad($ids, $conditions);
     $retval = parent::load($attendee_ids, $conditions);
     return $retval;
   }
}