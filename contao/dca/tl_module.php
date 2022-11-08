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
    '{title_legend},name,headline,type;{leadsoptin_legend},leadOptInSuccessType,leadOptInErrorMessage,leadOptInSuccessNotification,leadOptIndNeedsUserInteraction;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'leadOptInSuccessType';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'leadOptIndNeedsUserInteraction';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['leadOptIndNeedsUserInteraction'] =
    'leadOptInUserInteractionMessage,leadOptInUserInteractionSubmit';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['leadOptInSuccessType_message'] = 'leadOptInSuccessMessage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['leadOptInSuccessType_redirect'] = 'leadOptInSuccessJumpTo';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInSuccessType'] = array
(
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessType'],
    'exclude'    => true,
    'inputType'  => 'select',
    'options'    => ['message', 'redirect'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval'       => array('tl_class' => 'w50', 'submitOnChange' => true),
    'sql'        => "varchar(8) COLLATE utf8_bin NOT NULL default 'message'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInSuccessMessage'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessMessage'],
    'exclude'   => true,
    'inputType' => 'textarea',
    'eval'      => array('tl_class' => 'long', 'rte' => 'tinyMCE'),
    'sql'       => "text NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInSuccessJumpTo'] = array
(
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessJumpTo'],
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => array('fieldType' => 'radio', 'tl_class' => 'clr'),
    'sql'        => "int(10) unsigned NOT NULL default 0",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInErrorMessage'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInErrorMessage'],
    'exclude'   => true,
    'inputType' => 'textarea',
    'eval'      => array('tl_class' => 'clr long', 'rte' => 'tinyMCE'),
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

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptIndNeedsUserInteraction'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptIndNeedsUserInteraction'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'w50 clr', 'submitOnChange' => true),
    'sql'       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInUserInteractionMessage'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInUserInteractionMessage'],
    'exclude'   => true,
    'inputType' => 'textarea',
    'eval'      => array('tl_class' => 'long', 'rte' => 'tinyMCE'),
    'sql'       => "text NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['leadOptInUserInteractionSubmit'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['leadOptInUserInteractionSubmit'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'w50'),
    'sql'       => "varchar(128) COLLATE utf8_bin NOT NULL default ''",
);
