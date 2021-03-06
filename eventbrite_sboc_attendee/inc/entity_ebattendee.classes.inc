<?php

/*
* Extending Entity base class to represent our new entity
*/
class EBAttendeeEntity extends Entity{
  protected function defaultLabel(){
    return $this->event_id. '-'. $this->attendee_id;
  }
  
  protected function defaultUri(){
    return array('path' => 'ebattendee/' . $this->identifier());
  }

}

/*
* Extending the EntityAPIController for the Attendee entity.
*/
class EBAttendeeEntityController extends EntityAPIController{
  protected function realLoad($ids, $conditions){
    $dba = new Drupal\eventbrite_sboc\Helper\SBOCDBMgr();
    $attendees_ids = $dba->legacyLoadAttendees($ids);
    return $attendees_ids;
  }

  public function save($entity, DatabaseTransaction $transaction = NULL){
    $retval = parent::save($entity);
    return $retval;
  }

  public function load($ids = array(), $conditions = array()){
    $attendee_ids = $this->realLoad($ids, $conditions);
    $retval = parent::load($attendee_ids, $conditions);
    return $retval;
  }
}

/*
* Extending the EntityDefaultMetadataController for the Attendee entity.
*/

class EBAttendeeMetadataController extends EntityDefaultMetadataController{
   public function entityPropertyInfo(){
     $info = parent::entityPropertyInfo();
     $properties = &$info[$this->type]['properties'];
     $properties['eid'] = array(
       'label' => t('Primary Id'),
       'description' => t('The primary identifier for records in this table'),
       'type' => 'integer',
       'schema field' => 'eid',
     );

     $properties['uid'] = array(
       'label' => t('Drupal User Id'),
       'description' => t('The related Drupal user account'),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'uid',
     );

     $properties['event_id'] = array(
       'label' => t('Event Id'),
       'description' => t('The primary identifier for an Eventbrite event'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'event_id',
     );

     $properties['attendee_id'] = array(
       'label' => t('Attendee Id'),
       'description' => t('Eventbrite Attendee Id'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'attendee_id',
     );

     $properties['order_id'] = array(
       'label' => t('Order Id'),
       'description' => t('Eventbrite Order Id'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'order_id',
     );

     $properties['ticket_class_id'] = array(
       'label' => t('Ticket Class Id'),
       'description' => t('Eventbrite Ticket Class Id'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'ticket_class_id',
     );

     $properties['create_date'] = array(
       'label' => t('Create Date'),
       'description' => t('Date and time attendee record was created'),
       'type' => 'date',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'create_date',
     );

     $properties['email_address'] = array(
       'label' => t('Email Address'),
       'description' => t('Email address of purchaser'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'email_address',
     );

     $properties['last_name'] = array(
       'label' => t('Purchaser Last Name'),
       'description' => t('Last name of attendee'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'last_name',
     );

     $properties['first_name'] = array(
       'label' => t('Purchaser First Name'),
       'description' => t('First name of attendee'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'first_name',
     );

     $properties['category'] = array(
       'label' => t('Category'),
       'description' => t('Category attendee will compete in'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'category',
     );

     $properties['category_nid'] = array(
       'label' => t('Category Nid'),
       'description' => t('Reference to node containing registration information'),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'category_nid',
     );

     $properties['order_type'] = array(
       'label' => t('Order Type'),
       'description' => t('Type of order(e.g. Paypal)'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'order_type',
     );

     $properties['email_sent'] = array(
       'label' => t('Email Sent'),
       'description' => t('Indicates status of first email contact'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'email_sent',
     );

     $properties['email_send_date'] = array(
       'label' => t('Email Sent Date'),
       'description' => t('Date email was sent'),
       'type' => 'date',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'email_sent',
     );

     $properties['reg_type'] = array(
       'label' => t('Registration Type'),
       'description' => t('Registration type (e.g. Premium, Standard, Unspecified)'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'reg_type',
     );

     $properties['region_name'] = array(
       'label' => t('Region Name'),
       'description' => t('Region name  (e.g. Toronto, Mississauga etc.)'),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'region_name',
     );

     $properties['region_nid'] = array(
       'label' => t('Region Node Id'),
       'description' => t('Reference to node containing region information'),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'region_nid',
     );

     $properties['contestant_last_name'] = array(
       'label' => t('Contestant Last Name'),
       'description' => t("Contestant’s Last name"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'contestant_last_name',
     );

     $properties['contestant_first_name'] = array(
       'label' => t('Contestant First Name'),
       'description' => t("Contestant’s First Name"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'contestant_first_name',
     );

     $properties['gender'] = array(
       'label' => t('Gender'),
       'description' => t("Contestant’s gender -- allowed values M/F"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'gender',
     );

     $properties['grade'] = array(
       'label' => t('Grade'),
       'description' => t("Contestant’s Grade"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'grade',
     );

     $properties['school'] = array(
       'label' => t('School'),
       'description' => t("Contestant’s School"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'school',
     );

     $properties['year_of_birth'] = array(
       'label' => t('Year Of Birth'),
       'description' => t("Contestant’s Year Of Birth"),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'year_of_birth',
     );

     $properties['month_of_birth'] = array(
       'label' => t('Month Of Birth'),
       'description' => t("Contestant’s Month Of Birth"),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'month_of_birth',
     );

     $properties['day_of_birth'] = array(
       'label' => t('Day Of Birth'),
       'description' => t("Contestant’s Day Of Birth"),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'day_of_birth',
     );

     $properties['home_address_line_1'] = array(
       'label' => t('Home Address Line 1'),
       'description' => t("Contestant’s Home Address Line 1"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'home_address_line_1',
     );

     $properties['home_address_line_2'] = array(
       'label' => t('Home Address Line 2'),
       'description' => t("Contestant’s Home Address Line 1"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'home_address_line_2',
     );

     $properties['home_city'] = array(
       'label' => t('Home City'),
       'description' => t("Contestant’s Home City"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'home_city',
     );

     $properties['home_prov_state'] = array(
       'label' => t('Home Postal Zip'),
       'description' => t("Contestant’s Home Postal or Zip Code"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'home_prov_state',
     );

     $properties['home_phone_1'] = array(
       'label' => t('Home Phone 1'),
       'description' => t("Contestant’s Home Phone 1"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'home_phone_1',
     );

     $properties['home_phone_2'] = array(
       'label' => t('Home Phone 2'),
       'description' => t("Contestant’s Home Phone 2"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'home_phone_2',
     );

     $properties['change_date'] = array(
       'label' => t('Change Date'),
       'description' => t("Date and time attendee record was updated in this table"),
       'type' => 'date',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'change_date',
     );

     $properties['email_consent'] = array(
       'label' => t('Email Consent'),
       'description' => t("Flag indicating consent to be contacted by email"),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'email_consent',
     );

     $properties['additional_info'] = array(
       'label' => t('Additional Notes'),
       'description' => t("Additional Notes"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'additional_info',
     );

     $properties['ts_create_date'] = array(
       'label' => t('Create Date Timestamp'),
       'description' => t("Timestamp value for create date column"),
       'type' => 'date',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'ts_create_date',
     );

     $properties['ts_change_date'] = array(
       'label' => t('Change Date Timestamp'),
       'description' => t("Timestamp value for change date column"),
       'type' => 'date',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'ts_change_date',
     );

     $properties['ts_email_send_date'] = array(
       'label' => t('Email Send Date Timestamp'),
       'description' => t("Timestamp value for email send date column"),
       'type' => 'date',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'ts_email_send_date',
     );

     $properties['language'] = array(
       'label' => t('Language'),
       'description' => t("Language selected for competition"),
       'type' => 'text',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'language',
     );

     $properties['link_id'] = array(
       'label' => t('Link Id'),
       'description' => t("Link id"),
       'type' => 'integer',
       'setter callback' => 'entity_property_verbatim_set',
       'schema field' => 'link_id',
     );

     return $info;
   }
}

// Draws heavily on samples from: https://api.drupal.org/api/views/views.api.php/function/hook_views_data/7.x-3.x
class EntityAttendeeViewsController extends EntityDefaultViewsController {
  public function views_data(){
    $data = parent::views_data();

    // The 'group' index will be used as a prefix in the UI for any of this
    // table's fields, sort criteria, etc. so it's easy to tell where they came
    // from.
    $data['eventbrite_sboc_attendees']['table']['group'] = 'Eventbrite SBOC Attendees';
    // Define this as a base table – a table that can be described in itself by
    // views
    $data['eventbrite_sboc_attendees']['table']['base'] = array(
      'field' => 'eid',
      'title' => t('Eventbrite SBOC Attendees'),
      'help' => t('Contains Attendee data imported from Eventbrite registrations'),
      'weight' => -10,
    );

    // This table references the {user} table. The declaration below creates an
    // 'implicit' relationship to the {user} table, so that when {user} is the base
    // table, the fields are automatically available.
    $data['eventbrite_sboc_attendees']['table']['join'] = array(
      // Index this array by the table name to which this table refers.
      // 'left_field' is the primary key in the referenced table.
      // 'field' is the foreign key in this table.
      'users' => array(
        'left_field' => 'uid',
        'field' => 'uid',
      ),
    );

    // uid matching node id (nid) in {node} table
    $data['eventbrite_sboc_attendees']['uid'] = array(
      'title' => t('Attendee User Id'),
      'help' => t('Links an attendee to a registered system user'),
      // Define a relationship to the {node} table
      'relationship' => array(
        'base' => 'users',
        'base field' => 'uid',
        'handler' => 'views_handler_relationship',
        'label' => t('Attendee User Id'),
        'title' => t('Attendee User Id'),
        'help' => t('Links an attendee to a registered system user'),
      ),
    );

    // category_nid matching node id (nid) in {node} table
    $data['eventbrite_sboc_attendees']['category_nid'] = array(
      'title' => t('Attendee Category'),
      'help' => t('Links an attendee to a category'),
      // Define a relationship to the {node} table
      'relationship' => array(
        'base' => 'node',
        'base field' => 'nid',
        'handler' => 'views_handler_relationship',
        'label' => t('Attendee Category'),
        'title' => t('Attendee Category'),
        'help' => t('Links an attendee to a category'),
      ),
    );

    // region_nid matching node id (nid) in {node} table
    $data['eventbrite_sboc_attendees']['region_nid'] = array(
      'title' => t('Attendee Region'),
      'help' => t('Links an attendee to a region'),
      // Define a relationship to the {node} table
      'relationship' => array(
        'base' => 'node',
        'base field' => 'nid',
        'handler' => 'views_handler_relationship',
        'label' => t('Attendee Region'),
        'title' => t('Attendee Region'),
        'help' => t('Links an attendee to a region'),
      ),
    );

    // link_nid matching node id (nid) in {node} table
    $data['eventbrite_sboc_attendees']['link_nid'] = array(
      'title' => t('Attendee Link'),
      'help' => t('Connects an external link to attendee registration'),
      // Define a relationship to the {node} table
      'relationship' => array(
        'base' => 'node',
        'base field' => 'nid',
        'handler' => 'views_handler_relationship',
        'label' => t('Attendee Links'),
        'title' => t('Attendee Links'),
        'help' => t('Connects an attendee to a linked resource'),
      ),
    );

   // For dates: http://www.rahulsingla.com/blog/2013/03/drupal-7-exposing-date-time-fields-in-custom-tables-to-views-3

    return $data;
  }
}