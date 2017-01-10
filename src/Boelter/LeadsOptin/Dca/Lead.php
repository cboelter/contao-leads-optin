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

use Haste\Util\StringUtil;

/**
 * Provides several function for the optin functions.
 *
 * @package Boelter\LeadsOptin\Dca
 */
class Lead
{
    /**
     * Extend the default label function to also replace the optin_tstamp in the backend.
     *
     * @param $row
     * @param $label
     *
     * @return string
     */
    public function getLabel($row, $label)
    {
        $database = \Database::getInstance();
        $form     = $database->prepare("SELECT * FROM tl_form WHERE id=?")->execute($row['master_id']);

        // No form found, we can't format the label
        if (!$form->numRows) {
            return $label;
        }

        $tokens = array(
            'created'      => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['created']),
            'optin_tstamp' => ($row['optin_tstamp'] ? \Date::parse(
                $GLOBALS['TL_CONFIG']['datimFormat'],
                $row['optin_tstamp']
            ) : ''),
        );

        $data = $database->prepare("SELECT * FROM tl_lead_data WHERE pid=?")->execute($row['id']);

        while ($data->next()) {
            StringUtil::flatten(deserialize($data->value), $data->name, $tokens);
        }

        return StringUtil::recursiveReplaceTokensAndTags($form->leadLabel, $tokens);
    }

    /**
     * Add an icon to show the optin state visual in the backend.
     *
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     *
     * @return string
     */
    public function showOptInState($row, $href, $label, $title, $icon, $attributes)
    {
        if (!$row['optin_tstamp']) {
            $iconName = explode('.', $icon);
            $icon     = $iconName[0] . '_.' . $iconName[1];
        }

        return \Image::getHtml(
            'system/themes/default/images/' . $icon,
            'TEST',
            'title="' . sprintf(
                $GLOBALS['TL_LANG']['tl_lead']['optin_label'],
                ($row['optin_tstamp'] ? \Date::parse(
                    $GLOBALS['TL_CONFIG']['datimFormat'],
                    $row['optin_tstamp']
                ) : '')
            ) . '"'
        );
    }
}
