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
$GLOBALS['TL_DCA']['tl_module']['palettes']['leadsoptin'] =
    '{title_legend},name,headline,type;{leadsoptin_legend},leadOptInSuccessMessage,leadOptInErrorMessage,leadOptInSuccessNotification;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInSuccessMessage'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessMessage'],
    'exclude'   => true,
    'inputType' => 'textarea',
    'eval'      => array('tl_class' => 'long', 'rte' => 'tinyMCE'),
    'sql'       => "text NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInErrorMessage'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInErrorMessage'],
    'exclude'   => true,
    'inputType' => 'textarea',
    'eval'      => array('tl_class' => 'long', 'rte' => 'tinyMCE'),
    'sql'       => "text NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInSuccessNotification'] = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessNotification'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => array('Boelter\\LeadsOptin\\Dca\\Module', 'getNotifications'),
    'eval'             => array('tl_class' => 'w50 m12', 'includeBlankOption' => true, 'mandatory' => false),
    'sql'              => "int(10) unsigned NOT NULL default '0'",
);
