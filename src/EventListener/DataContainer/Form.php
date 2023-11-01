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

namespace Boelter\LeadsOptin\EventListener\DataContainer;

use Boelter\LeadsOptin\Handler\Hook;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\FormFieldModel;
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
     *
     * @param $dc
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

    #[AsCallback(table: 'tl_form', target: 'config.onsubmit')]
    public function addFormField(DataContainer $dc): void
    {
        if ($dc->id && $dc->activeRecord->leadMain) {
            return;
        }

        if ($dc->activeRecord->leadEnabled) {
            $arrFields = FormFieldModel::findBy(
                ['pid=?', 'type=?', 'name=?', 'invisible!=1'],
                [$dc->id, 'hidden', Hook::$OPTIN_FORMFIELD_NAME]
            )
            ;

            if (empty($arrFields)) {
                $this->db->insert('tl_form_field', [
                    'pid' => $dc->id,
                    'type' => 'hidden',
                    'sorting' => 1,
                    'tstamp' => time(),
                    'name' => Hook::$OPTIN_FORMFIELD_NAME,
                    'leadStore' => '1',
                    'invisible' => '',
                ]);
            } else {
                $field = $arrFields->getModels()[0];
                $field->invisible = '';
                $field->save();
            }
        }
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
            "SELECT id,title FROM tl_nc_notification WHERE type='leads_optin_notification' ORDER BY title"
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
