<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-leads-optin-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @author     Christopher Bölter
 * @license    LGPL-3.0-or-later
 */

// Keys
$GLOBALS['TL_DCA']['tl_lead']['config']['sql']['keys']['optin_token'] = 'index';
$GLOBALS['TL_DCA']['tl_lead']['config']['sql']['keys']['post_data'] = 'index';

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
