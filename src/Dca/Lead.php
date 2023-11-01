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
        $objForm = FormModel::findByPk($row['form_id']);

        if (empty($objForm) || !isset($objForm->leadEnabled) || !$objForm->leadEnabled || !isset($objForm->leadOptIn) || !$objForm->leadOptIn) {
            return '';
        }

        $iconPath = 'system/themes/flexible/icons/';

        if (!$row['optin_tstamp']) {
            $iconName = explode('.', $icon);
            $icon = $iconName[0].'_.'.$iconName[1];
        }

        return Image::getHtml(
            $iconPath.$icon,
            '',
            'title="'.sprintf(
                $GLOBALS['TL_LANG']['tl_lead']['optin_label'],
                ($row['optin_tstamp'] ? Date::parse(
                    $GLOBALS['TL_CONFIG']['datimFormat'],
                    $row['optin_tstamp']
                ) : '')
            ).'"'
        );
    }
}
