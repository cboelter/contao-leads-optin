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

// Backend styles
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS']['leads_optin'] = 'bundles/contaoleadsoptin/css/leads-optin.css|static';
}

// Notifications
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['leads_optin'] = [
    'leads_optin_notification' => [
        'recipients' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_subject' => ['lead_*', 'form_*', 'formconfig_*', 'admin_email'],
        'email_text' => [
            'lead_*',
            'form_*',
            'formconfig_*',
            'formlabel_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
            'optin_token',
            'optin_url',
        ],
        'email_html' => [
            'lead_*',
            'form_*',
            'formconfig_*',
            'formlabel_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
            'optin_token',
            'optin_url',
        ],
        'email_sender_name' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_sender_address' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_recipient_cc' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_recipient_bcc' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_replyTo' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'attachment_tokens' => ['lead_*', 'form_*', 'formconfig_*'],
    ],
    'leads_optin_success_notification' => [
        'recipients' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_subject' => ['lead_*', 'form_*', 'formconfig_*', 'admin_email'],
        'email_text' => [
            'lead_*',
            'form_*',
            'formconfig_*',
            'formlabel_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
        ],
        'email_html' => [
            'lead_*',
            'form_*',
            'formconfig_*',
            'formlabel_*',
            'raw_data',
            'raw_data_filled',
            'filenames',
            'admin_email',
        ],
        'email_sender_name' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_sender_address' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_recipient_cc' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_recipient_bcc' => ['admin_email', 'lead_*', 'form_*', 'formconfig_*'],
        'email_replyTo' => ['admin_email', 'lead_*', 'formconfig_*'],
        'attachment_tokens' => ['lead_*', 'form_*', 'formconfig_*'],
    ],
];
