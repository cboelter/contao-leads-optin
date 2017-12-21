<?php

/**
 * The leads optin extension allows you to store leads with double optin function.
 *
 * PHP version 5
 *
 * @package    LeadsOptin
 * @author     Christopher Bölter <kontakt@boelter.eu>
 * @copyright  Christopher Bölter 2017
 * @license    LGPL.
 * @filesource
 */

/**
 * Frontend-Modules
 */
array_insert(
    $GLOBALS['FE_MOD']['leads'],
    (count($GLOBALS['FE_MOD']['leads']) - 1),
    array
    (
        'leadsoptin' => 'Boelter\\LeadsOptin\\Module\\OptIn',
    )
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['storeLeadsData'][] = array('Boelter\\LeadsOptin\\Handler\\Hook', 'appendOptInData');

/**
 * Export types
 */
$GLOBALS['LEADS_EXPORT']['optinCsv'] = 'Boelter\\LeadsOptin\\Exporter\\Csv';

/**
 * Notifications
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['leads_optin'] = array
(
    'leads_optin_notification'         => array
    (
        'recipients'           => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_subject'        => array('lead_*', 'formconfig_*', 'admin_email'),
        'email_text'           => array(
            'lead_*',
            'formconfig_*',
            'raw_data',
            'admin_email',
            'optin_token',
            'optin_url',
        ),
        'email_html'           => array(
            'lead_*',
            'formconfig_*',
            'raw_data',
            'admin_email',
            'optin_token',
            'optin_url',
        ),
        'email_sender_name'    => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_sender_address' => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_recipient_cc'   => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_recipient_bcc'  => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_replyTo'        => array('admin_email', 'lead_*', 'formconfig_*'),
    ),
    'leads_optin_success_notification' => array
    (
        'recipients'           => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_subject'        => array('lead_*', 'formconfig_*', 'admin_email'),
        'email_text'           => array(
            'lead_*',
            'formconfig_*',
            'admin_email',
        ),
        'email_html'           => array(
            'lead_*',
            'formconfig_*',
            'admin_email',
        ),
        'email_sender_name'    => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_sender_address' => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_recipient_cc'   => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_recipient_bcc'  => array('admin_email', 'lead_*', 'formconfig_*'),
        'email_replyTo'        => array('admin_email', 'lead_*', 'formconfig_*'),
    ),
);
