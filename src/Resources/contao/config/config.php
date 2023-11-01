<?php

declare(strict_types=1);

/**
 * The leads optin extension allows you to store leads with double optin function.
 *
 * PHP version ^7.4 || ^8.0
 *
 * @copyright  Christopher Bölter 2017
 * @license    LGPL.
 * @filesource
 */

use Boelter\LeadsOptin\Module\OptIn;
use Contao\ArrayUtil;

// Frontend modules
ArrayUtil::arrayInsert(
    $GLOBALS['FE_MOD']['leads'],
    (is_array($GLOBALS['FE_MOD']['leads']) ? count($GLOBALS['FE_MOD']['leads']) - 1 : 0),
    [
        'leadsoptin' => OptIn::class,
    ]
);

// Hooks
//$GLOBALS['TL_HOOKS']['storeLeadsData'][] = ['Boelter\LeadsOptin\Handler\Hook', 'appendOptInData'];

// Export types
//$GLOBALS['LEADS_EXPORT']['optinCsv'] = 'Boelter\LeadsOptin\Exporter\Csv';

// Notifications
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['leads_optin'] = [
    'leads_optin_notification' => [
        'recipients' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_subject' => ['lead_*', 'formconfig_*', 'admin_email'],
        'email_text' => [
            'lead_*',
            'formconfig_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
            'optin_token',
            'optin_url',
        ],
        'email_html' => [
            'lead_*',
            'formconfig_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
            'optin_token',
            'optin_url',
        ],
        'email_sender_name' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_sender_address' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_recipient_cc' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_recipient_bcc' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_replyTo' => ['admin_email', 'lead_*', 'formconfig_*'],
    ],
    'leads_optin_success_notification' => [
        'recipients' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_subject' => ['lead_*', 'formconfig_*', 'admin_email'],
        'email_text' => [
            'lead_*',
            'formconfig_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
        ],
        'email_html' => [
            'lead_*',
            'formconfig_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
        ],
        'email_sender_name' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_sender_address' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_recipient_cc' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_recipient_bcc' => ['admin_email', 'lead_*', 'formconfig_*'],
        'email_replyTo' => ['admin_email', 'lead_*', 'formconfig_*'],
    ],
];
