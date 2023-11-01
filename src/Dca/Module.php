<?php

declare(strict_types=1);

/**
 * The leads optin extension allows you to store leads with double optin function.
 *
 * PHP version ^7.4 || ^8.0
 *
 * @copyright  Christopher Bölter 2017
 * @license    LGPL.
 * @filesource
 */

namespace Boelter\LeadsOptin\Dca;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * Provides several function for the module datacontainer.
 */
class Module
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Get all notifications for the optin.
     *
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.leadOptInSuccessNotification.options')]
    public function getNotifications(DataContainer $dc): array
    {
        $notificationOptions = [];
        $notifications = $this->db->prepare(
            "SELECT id,title FROM tl_nc_notification WHERE type='leads_optin_success_notification' ORDER BY title"
        )
            ->executeQuery()
            ->fetchAllAssociative()
        ;

        foreach ($notifications as $notification) {
            $notificationOptions[$notification['id']] = $notification['title'];
        }

        return $notificationOptions;
    }
}
