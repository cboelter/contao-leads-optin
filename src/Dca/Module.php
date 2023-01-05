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

namespace Boelter\LeadsOptin\Dca;

use Contao\Database;

/**
 * Provides several function for the module datacontainer
 *
 * @package Boelter\LeadsOptin\Dca
 */
class Module
{
    /**
     * Get all notifications for the optin success notification.
     */
    public function getNotifications(): array
    {
        $notificationOptions = [];
        $database            = Database::getInstance();
        $notifications       = $database->execute(
            "SELECT id,title FROM tl_nc_notification WHERE type='leads_optin_success_notification' ORDER BY title"
        );

        while ($notifications->next())
        {
            $notificationOptions[$notifications->id] = $notifications->title;
        }

        return $notificationOptions;
    }
}
