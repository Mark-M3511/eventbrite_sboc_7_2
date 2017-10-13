<?php

use Drupal\eventbrite_sboc\Helper\EBConsts;
use Drupal\eventbrite_sboc\Helper\SBOCDBMgr;

function _eventbrite_sboc_form_event_setup($form, &$form_state){
  $form = array();
  
  $form['ebs_event_setup'] = array(
    '#type' => 'fieldset',
    '#title' => t('Event Setup'),  
  );
  
  $form['ebs_event_setup'][EBConsts::EBS_CONFIG_EVENT_ID] = array(
    '#type' => 'textfield',
    '#title' => t('Event ID'),
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_EVENT_ID, ''),
    '#size' => '80',
    '#maxlength' => 128,
    '#required' => TRUE,
  );

  $form['ebs_event_setup'][EBConsts::EBS_CONFIG_WEBHOOK_USERT_ID] = array(
    '#type' => 'textfield',
    '#title' => t('Webhook User ID'),
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_WEBHOOK_USERT_ID, ''),
    '#size' => '80',
    '#maxlength' => 128,
    '#required' => TRUE,
  );

  $form['ebs_event_setup'][EBConsts::EBS_CONFIG_CHANGED_SINCE_HOURS] = array(
    '#type' => 'select',
    '#title' => t('Number of hours of changed records to retrieve'),
    '#options' => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23),
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_CHANGED_SINCE_HOURS, EBConsts::EBS_CONFIG_DEFAULT_CHANGED_SINCE_HOURS),
    '#required' => TRUE,
  );

  $email_messages = array(-1 => '--'. t('None'). '--');
  $email_messages = array_merge($email_messages,(new SBOCDBMgr())->getEmailMessages());

  $form['ebs_event_setup'][EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID] = array(
    '#type' => 'select',
    '#title' => t('Email Message 1'),
    '#description' => t('Usually used for the main welcome message (i.e. new registrations)'),
    '#options' => $email_messages,
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID, EBConsts::EBS_EMAIL_NODE_ID),
    '#required' => TRUE,
  );
  
  $form['ebs_event_setup'][EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_2] = array(
    '#type' => 'select',
    '#title' => t('Email Message 2'),
    '#description' => t('Usually used for a secondary welcome message (i.e. existing registrations)'),
    '#options' => $email_messages,
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_2, 
      variable_get(EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID, EBConsts::EBS_EMAIL_NODE_ID)),
    '#required' => TRUE,
  );

  $form['ebs_event_setup'][EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_3] = array(
    '#type' => 'select',
    '#title' => t('Email Message 3'),
    '#description' => t('Usually used for messages sent to SBOC (i.e. registration notes)'),
    '#options' => $email_messages,
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID_3,
      variable_get(EBConsts::EBS_CONFIG_EMAIL_MESSAGE_NODE_ID, EBConsts::EBS_EMAIL_NODE_ID)),
    '#required' => TRUE,
  );
  
  return system_settings_form($form);
}

function _eventbrite_sboc_form_select_order($form, &$form_state){
  $form['ebs_get_order'] = array(
    '#type' => 'fieldset',
    '#title' => t('Get Order'),  
  );
  
  $form['ebs_get_order']['ebs_order_num'] = array(
    '#type' => 'textfield',
    '#title' => t('Order Number'),
    '#default_value' => variable_get('ebs_select_order', ''),
    '#size' => '80',
    '#maxlength' => 128,
    '#required' => TRUE,
  );
  
  $form['ebs_get_order']['ebs_submit_order'] = array(
    '#type' => 'submit',
    '#value' => t('Get Order'),
    '#submit' => array('_eventbrite_sboc_submit_order_num',),
  );
  
  return $form; 
}

function _eventbrite_sboc_form_select_attendee_order($form, &$form_state){
  $form['ebs_get_order'] = array(
    '#type' => 'fieldset',
    '#title' => t('Get Attendee Order'),  
  );
  
  $form['ebs_get_order']['ebs_attendee_order_num'] = array(
    '#type' => 'textfield',
    '#title' => t('Order Number'),
    '#default_value' => variable_get('ebs_attendee_order_num', ''),
    '#size' => '80',
    '#maxlength' => 128,
    '#required' => TRUE,
  );
  
  $form['ebs_get_order']['ebs_submit_attendee_order'] = array(
    '#type' => 'submit',
    '#value' => t('Get Order'),
    '#submit' => array('_eventbrite_sboc_submit_attendee_order_num',),
  );
  
  return $form; 
}


function _eventbrite_sboc_form_event_info($form, &$form_state, $event){
  $form = array();
  
  $form['ebs_event_info'] = array(
    '#type' => 'fieldset',
    '#title' => t('Event Information'),
  );
  
  $value = empty($event->name['text']) ? '' : $event->name['text'];
  $form['ebs_event_info']['event_name_label'] = array(
    '#type' => 'item',
    '#description' => t('Name'),
    '#markup' => check_plain($value),
  );
  
  $value = empty($event->description['text']) ? '' : $event->description['text'];
  $form['ebs_event_info']['event_title_value'] = array(
    '#type' => 'item',
    '#description' => t('Description'),
    '#markup' => check_plain($value),
  );
  
  $value = empty($event->id) ? '' : $event->id;
  $form['ebs_event_info']['id'] = array(
    '#type' => 'item',
    '#description' => t('Id'),
    '#markup' => check_plain($value),
  );
  
  $value = empty($event->start['local']) ? '' : $event->start['local'];
  $form['ebs_event_info']['start-time'] = array(
    '#type' => 'item',
    '#description' => t('Start Time'),
    '#markup' => check_plain($value),
  );
  
  $value = empty($event->end['local']) ? '' : $event->end['local'];
  $form['ebs_event_info']['end_time'] = array(
    '#type' => 'item',
    '#description' => t('End Time'),
    '#markup' => check_plain($value),
  );
  
  $value = empty($event->start['timezone']) ? '' : $event->start['timezone'];
  $form['ebs_event_info']['time_zone'] = array(
    '#type' => 'item',
    '#description' => t('Timezone'),
    '#markup' => check_plain($value),
  );
  
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'eventbrite_sboc'). '/css/admin_ui_event_info.css',
  );
   
  return $form;
}
