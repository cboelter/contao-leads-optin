<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-leads-optin for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @author     Christopher Bölter
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\LeadsOptinBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Date;
use Contao\FormModel;
use Contao\Image;

/**
 * Provides several function for the optin functions.
 */
class Lead
{
    /**
     * Add an icon to show the optin state visual in the backend.
     *
     * @param array<mixed>      $row
     * @param array<mixed>      $rootRecordIds
     * @param array<mixed>|null $childRecordIds
     */
    #[AsCallback(table: 'tl_lead', target: 'list.operations.leadsoptin.button')]
    public function showOptInState(array $row, string|null $href, string $label, string $title, string|null $icon, string $attributes, string $table, array $rootRecordIds, array|null $childRecordIds, bool $circularReference, string|null $previous, string|null $next, DataContainer $dc): string
    {
        $objForm = FormModel::findById($row['form_id']);

        if (empty($objForm) || !isset($objForm->leadEnabled) || !$objForm->leadEnabled || !isset($objForm->leadOptIn) || !$objForm->leadOptIn) {
            return '';
        }

        $iconPath = 'system/themes/flexible/icons/';

        if (!$row['optin_tstamp']) {
            $iconName = explode('.', (string) $icon);
            $icon = $iconName[0].'_.'.$iconName[1];
        }

        return Image::getHtml(
            $iconPath.$icon,
            '',
            'title="'.sprintf(
                $GLOBALS['TL_LANG']['tl_lead']['optin_label'],
                $row['optin_tstamp'] ? Date::parse(
                    $GLOBALS['TL_CONFIG']['datimFormat'],
                    $row['optin_tstamp'],
                ) : '',
            ).'"',
        );
    }
}
