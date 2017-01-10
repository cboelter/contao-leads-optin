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
$GLOBALS['TL_DCA']['tl_module']['metapalettes']['leadsoptin'] = array(
    'type'       => array('name', 'headline', 'type'),
    'leadsoptin' => array('leadOptInSuccessMessage', 'leadOptInErrorMessage'),
    'template'   => array(':hide', 'customTpl'),
    'protected'  => array(':hide', 'protected'),
    'expert'     => array(':hide', 'guests', 'cssID', 'space'),
);

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInSuccessMessage'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessMessage'],
    'exclude'   => true,
    'inputType' => 'textarea',
    'eval'      => array('tl_class' => 'long', 'rte' => 'tinyMCE'),
    'sql'       => "text NOT NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInErrorMessage'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInErrorMessage'],
    'exclude'   => true,
    'inputType' => 'textarea',
    'eval'      => array('tl_class' => 'long', 'rte' => 'tinyMCE'),
    'sql'       => "text NOT NULL",
);
