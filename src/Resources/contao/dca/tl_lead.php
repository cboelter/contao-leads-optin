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

// Keys
$GLOBALS['TL_DCA']['tl_lead']['config']['sql']['keys']['optin_token'] = 'index';

// Callbacks
//$GLOBALS['TL_DCA']['tl_lead']['list']['label']['label_callback'] = [Lead::class, 'getLabel'];

// Operations
$GLOBALS['TL_DCA']['tl_lead']['list']['operations']['leadsoptin'] = [
    'icon' => 'member.svg',
];

// Fields
$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_token'] = [
    'sql' => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_tstamp'] = [
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_files'] = [
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_labels'] = [
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_ip'] = [
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_notification_tstamp'] = [
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
