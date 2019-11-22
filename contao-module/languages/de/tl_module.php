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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['leadsoptin_legend'] = 'Anfragen speichern OptIn';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessMessage'] =
    array('OptIn Bestätigung', 'Füllen Sie dieses Feld aus, um dem Benutzer eine Bestätigung zu geben');

$GLOBALS['TL_LANG']['tl_module']['leadOptInErrorMessage'] =
    array('OptIn Fehler', 'Füllen Sie dieses Feld aus, um dem Benutzer eine Information über einen Fehler zu geben');

$GLOBALS['TL_LANG']['tl_module']['leadOptInSuccessNotification'] =
    array(
        'OptIn Erfolgreich Benachrichtigung',
        'Wählen Sie eine Benachrichtigung aus, um dem Benutzer den Erfolg des OptIn zu bestätigen',
    );

$GLOBALS['TL_LANG']['tl_module']['leadOptIndNeedsUserInteraction'] =
    array('Benutzeraktion erforderlich', 'Wahlen Sie dieses Feld aus, damit der User das OptIn noch einmal bestätigen muss.');

$GLOBALS['TL_LANG']['tl_module']['leadOptInUserInteractionMessage'] =
    array('Bestätigungstext', 'Füllen Sie dieses Feld aus, um dem Benutzer eine Information zur erneuten Bestätigung zu geben.');

$GLOBALS['TL_LANG']['tl_module']['leadOptInUserInteractionSubmit'] =
    array('Bestätigungsfeld', 'Geben Sie hier den TExt für den Bestätigungs-Button an.');