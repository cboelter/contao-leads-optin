<?php

/**
 * The leads optin extension allows you to store leads with double optin function.
 *
 * PHP version ^7.4 || ^8.0
 *
 * @package    LeadsOptin
 * @author     Christopher Bölter <kontakt@boelter.eu>
 * @copyright  Christopher Bölter 2017
 * @license    LGPL.
 * @filesource
 */

// Palettes
$GLOBALS['TL_DCA']['tl_lead_export']['palettes']['optinCsv'] =
    '{name_legend},name,type,filename;{config_legend},headerFields,export;{date_legend:hide},lastRun,skipLastRun';
