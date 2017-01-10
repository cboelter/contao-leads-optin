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

namespace Boelter\LeadsOptin\Dca;

/**
 * Provides serveral helpers for handling callbacks and form related data.
 *
 * @package Boelter\LeadsOptin\Dca
 */
class Form
{
    /**
     * Update the palette to handle optin fields.
     *
     * @param $dc
     */
    public function updatePalette($dc)
    {
        $GLOBALS['TL_DCA']['tl_form']['palettes']['default'] =
            str_replace(
                'leadLabel',
                'leadLabel,leadOptIn',
                $GLOBALS['TL_DCA']['tl_form']['palettes']['default']
            );
    }

    /**
     * Get all notifications for the optin.
     *
     * @return array
     */
    public function getNotifications()
    {
        $notificationOptions = array();
        $database            = \Database::getInstance();
        $notifications       = $database->execute(
            "SELECT id,title FROM tl_nc_notification WHERE type='leads_optin_notification' ORDER BY title"
        );

        while ($notifications->next()) {
            $notificationOptions[$notifications->id] = $notifications->title;
        }

        return $notificationOptions;
    }
}
