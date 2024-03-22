<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-leads-optin-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @author     Christopher Bölter
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\LeadsOptinBundle\EventListener\DataContainer;

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
            "SELECT id,title FROM tl_nc_notification WHERE type='leads_optin_success_notification' ORDER BY title",
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
