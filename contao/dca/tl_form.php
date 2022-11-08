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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'leadOptIn';
$GLOBALS['TL_DCA']['tl_form']['subpalettes']['leadOptIn']   = 'leadOptInNotification,leadOptInStoreIp,leadOptInTarget';

/**
 * Callbacks
 */
$GLOBALS['TL_DCA']['tl_form']['config']['onload_callback'][] =
    array('Boelter\\LeadsOptin\\Dca\\Form', 'updatePalette');

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form']['fields']['leadOptIn'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_form']['leadOptIn'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'w50 m12', 'submitOnChange' => true),
    'sql'       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadOptInStoreIp'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_form']['leadOptInStoreIp'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'w50 m12'),
    'sql'       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadOptInNotification'] = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_form']['leadOptInNotification'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => array('Boelter\\LeadsOptin\\Dca\\Form', 'getNotifications'),
    'eval'             => array('tl_class' => 'w50 m12', 'includeBlankOption' => true, 'mandatory' => true),
    'sql'              => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadOptInTarget'] = array(
    'label'      => &$GLOBALS['TL_LANG']['tl_form']['leadOptInTarget'],
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => array(
        'fieldType' => 'radio',
        'tl_class'  => 'w50',
    ),
    'sql'        => "int(10) unsigned NOT NULL default '0'",
    'relation'   => array(
        'type' => 'hasOne',
        'load' => 'eager',
    ),
);
