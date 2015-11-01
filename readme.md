-- SUMMARY --

Eventbrite SBOC allows a Drupal site to interact with the Eventbrite API to retrieve information about specific events and attendees. In its current form, it is labelled and customized for use on the Spelling Bee Of Canada website.

Version 7.2 is a complete rewrite with the following aims:

1) Comply with new Eventbrite REST API (API v3)
2) Use a more object oriented approach
3) Distribute major functions across discrete modules: main, attendee, mail (for e.g.)
4) Make use of Drupal features (such as entities) to benefit from Drupal's most endearing feautres

For a full description of the module, visit the project page on GitHub:
 https://github.com/Mark-M3511/Eventbrite_SBOC

To submit bug reports and feature suggestions, or to track changes on GitHub:
  https://github.com/Mark-M3511/eventbrite_sboc_7_2

-- REQUIREMENTS --

 * Drupal Core (v7.x)
 * Mime Mail
 * Mail System
 * Entity
 * xautoload 
 * Eventbrite API Access - PHP project at: http://eventbrite.github.com 
 
 -- RECOMMENDATIONS --
 * Ulitamte Cron project - http://drupal.org/project/ultimate_cron

-- INSTALLATION --
  
1) Copy the eventbrite_sboc folder to the modules folder in your installation.

2) Enable the modules using Administration -> Modules (/admin/modules).

3) Configure user permissions in Administration -> People, click on the 
   Permissions tab (admin/people/permissions), go to Eventbrite SBOC in the list 
   and check "Configure Eventbrite SBOC".
  
4) Configure Eventbrie SBOC with your credentials and other items at
   (admin/config/system/eventbrite_sboc).
   
--- TODO ---
1) Develop and implement test automated cases
2) Continuous code improvements/changes to remain compliant with API changes/improvements
3) Improve entity integration (i.e. allow more UI based interactions with local entities)
  

