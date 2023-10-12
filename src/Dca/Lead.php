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

use Codefog\HasteBundle\StringParser;
use Contao\Database;
use Contao\Date;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;

/**
 * Provides several function for the optin functions.
 *
 * @package Boelter\LeadsOptin\Dca
 */
class Lead
{
    private StringParser|null $stringParser;

    public function __construct()
    {
        $this->stringParser = System::getContainer()->get(StringParser::class);
    }

    /**
     * Extend the default label function to also replace the optin_tstamp in the backend.
     */
    public function getLabel($row, $label): string
    {
        $database = Database::getInstance();
        $form     = $database->prepare("SELECT * FROM tl_form WHERE id=?")
            ->execute($row['master_id']);

        // No form found, we can't format the label
        if (0 === $form->numRows)
        {
            return $label;
        }

        $tokens = [
            'created'      => Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['created']),
            'optin_tstamp' => ($row['optin_tstamp'] ? Date::parse(
                $GLOBALS['TL_CONFIG']['datimFormat'],
                $row['optin_tstamp']
            ) : ''),
        ];

        $data = $database->prepare("SELECT * FROM tl_lead_data WHERE pid=?")->execute($row['id']);

        while ($data->next())
        {
            $this->stringParser->flatten(StringUtil::deserialize($data->value), $data->name, $tokens);
        }

        return $this->stringParser->recursiveReplaceTokensAndTags($form->leadLabel, $tokens);
    }

    /**
     * Add an icon to show the optin state visual in the backend.
     */
    public function showOptInState($row, $href, $label, $title, $icon, $attributes): string
    {
        $iconPath = 'system/themes/flexible/icons/';

        if (!$row['optin_tstamp']) {
            $iconName = explode('.', $icon);
            $icon     = $iconName[0].'_.'.$iconName[1];
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
