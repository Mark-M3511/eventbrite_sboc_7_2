<?php

use Drupal\eventbrite_sboc\Helper\EBConsts;

function _eventbrite_sboc_form_oauth_credentials(){
  $form = array();
  
  $form['ebs_oauth_credentials'] = array(
    '#type' => 'fieldset',
    '#title' => t('OAuth Credentials'),  
  );
  
  $form['ebs_oauth_credentials'][EBConsts::EBS_CONFIG_CONSUMER_KEY] = array(
    '#type' => 'textfield',
    '#title' => t('Consumer Key/Client ID'),
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_CONSUMER_KEY, ''),
    '#size' => '80',
    '#maxlength' => 128,
    '#required' => TRUE,
  );
  
   $form['ebs_oauth_credentials'][EBConsts::EBS_CONFIG_CONSUMER_SECRET] = array(
    '#type' => 'textfield',
    '#title' => t('Consumer Secret'),
    '#default_value' => variable_get(EBConsts::EBS_CONFIG_CONSUMER_SECRET, ''),
    '#size' => '80',
    '#maxlength' => 128,
    '#required' => TRUE,
  );
  
  return system_settings_form($form);
}

function _eventbrite_sboc_oauth_success(){
  $form = array();
  
  $form['ebs_oauth_success'] = array(
    '#type' => 'item',
    '#title' => t('OAuth Success'),
    '#markup' => t('You have successfully authorized the Eventbrite SBOC application'),  
  );
   
  return $form;
}

function _eventbrite_sboc_oauth_failure(){
  $form = array();
  
  $form['ebs_oauth_failure'] = array(
    '#type' => 'item',
    '#title' => t('OAuth Failure'),
    '#prefix' => '<span style="color: #F00;">',
    '#suffix' => '</span>',
    '#markup' => t('Sorry, you have NOT successfully authorized the Eventbrite SBOC application'),  
  );
   
  return $form;
}