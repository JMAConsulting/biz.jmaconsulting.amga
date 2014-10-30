<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.5                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * new version of civicrm apis. See blog post at
 * http://civicrm.org/node/131
 * @todo Write sth
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Job
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id: Contact.php 30879 2010-11-22 15:45:55Z shot $
 *
 */

function civicrm_api3_job_create_participants($params) {
  $con = getDbConn($params);
  
  $result = mysqli_query($con,"SELECT * FROM program_registrations r LEFT JOIN programs p ON r.program_id = p.id 
    LEFT JOIN statuses s ON s.id = r.status_id LEFT JOIN participant_types t ON t.id = r.participant_type_id");
  $roles = CRM_Core_OptionGroup::values('participant_role', TRUE);
  $status = array_flip(CRM_Event_PseudoConstant::participantStatus(NULL, NULL, 'label'));
  while($row = mysqli_fetch_assoc($result)) {
    $contactID = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $row['member_id'], 'id', 'external_identiifier');
    $event = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $row['program_code'], 'id', 'title');
    $params = array(
      'status_id' => $status[$row['status']],
      'role_id' => $roles[$row['participant_type']],
      'contact_id' => $contactID,
      'event_id' => $event,
      'fee_amount' => $row['price'],
      'registered_date' => $row['created_at'],
    );
    try{
      $participants = civicrm_api3('Participant', 'create', $params);
    }
    catch (CiviCRM_API3_Exception $e) {
      // handle error here
      $errorMessage = $e->getMessage();
      $errorCode = $e->getErrorCode();
      $errorData = $e->getExtraParams();
      $errors = array('error' => $errorMessage, 'error_code' => $errorCode, 'error_data' => $errorData);
      CRM_Core_Error::debug_var( 'ERROR CAUGHT:', $errors );
    }
  }
  
}