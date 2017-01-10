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
 * Keys
 */
$GLOBALS['TL_DCA']['tl_lead']['config']['sql']['keys']['optin_token'] = 'index';

/**
 * Callbacks
 */
$GLOBALS['TL_DCA']['tl_lead']['list']['label']['label_callback'] =
    array('Boelter\\LeadsOptin\\Dca\\Lead', 'getLabel');

/**
 * Operations
 */
$GLOBALS['TL_DCA']['tl_lead']['list']['operations']['leadsoptin'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_lead']['leadsoptin'],
    'icon'  => 'member.gif',
    'button_callback' => array('Boelter\\LeadsOptin\\Dca\\Lead', 'showOptInState')
);

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_token'] = array(
    'sql' => "varchar(32) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_tstamp'] = array(
    'sql' => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_ip'] = array(
    'sql' => "varchar(32) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_lead']['fields']['optin_notification_tstamp'] = array(
    'sql' => "int(10) unsigned NOT NULL default '0'",
);
