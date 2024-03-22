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

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * Provides several helpers for handling callbacks and form related data.
 */
class Form
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Update the palette to handle optin fields.
     */
    #[AsCallback(table: 'tl_form', target: 'config.onload')]
    public function updatePalette(DataContainer $dc): void
    {
        if ($dc->id && $this->db->fetchOne('SELECT leadMain FROM tl_form WHERE id=?', [$dc->id])) {
            return;
        }

        PaletteManipulator::create()
            ->addField('leadOptIn', null, PaletteManipulator::POSITION_APPEND)
            ->applyToSubpalette('leadEnabled', 'tl_form')
        ;
    }

    /**
     * Get all notifications for the optin.
     *
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_form', target: 'fields.leadOptInNotification.options')]
    public function getNotifications(DataContainer $dc): array
    {
        $notificationOptions = [];
        $notifications = $this->db->prepare(
            "SELECT id,title FROM tl_nc_notification WHERE type='leads_optin_notification' ORDER BY title",
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
