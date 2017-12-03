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

namespace Boelter\LeadsOptin\Module;

use Haste\Util\StringUtil;
use NotificationCenter\Model\Notification;

/**
 * Provides the frontend module to handle the optin process.
 *
 * @package Boelter\LeadsOptin\Module
 */
class OptIn extends \Module
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_leads_optin';


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard =
                '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['leadsoptin'][0]) . ' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * generate the module itself and handle the optin process.
     */
    protected function compile()
    {
        $token = \Input::get('token');

        if (!$token) {
            return;
        }

        $database = \Database::getInstance();
        $lead     =
            $database->prepare("SELECT * FROM tl_lead Where optin_token = ? AND optin_token <> ? AND optin_tstamp = ?")
                ->limit(1)
                ->execute(
                    $token,
                    '',
                    '0'
                );

        $this->Template->errorMessage = $this->leadOptInErrorMessage;

        if ($lead->numRows == 0) {
            $this->Template->isError = true;

            return;
        }

        $form = \FormModel::findById($lead->form_id);

        if (!$form) {
            $this->Template->isError = true;

            return;
        }

        $set                 = array();
        $set['optin_tstamp'] = time();
        $set['optin_token']  = '';

        if ($form->leadOptInStoreIp) {
            $set['optin_ip'] = \Environment::get('ip');
        }

        $updated = $database->prepare("UPDATE tl_lead %s Where id = ? AND optin_token = ? AND optin_tstamp = ?")
            ->set($set)
            ->execute(
                $lead->id,
                $token,
                '0'
            );

        if ($updated->affectedRows == 0) {
            $this->Template->isError = true;

            return;
        }

        $tokens     = array();
        $formConfig = $form->row();
        StringUtil::flatten($formConfig, 'formconfig', $tokens);

        $tokens['lead_created'] = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $lead->created);
        $tokens['optin_tstamp'] =
            ($set['optin_tstamp'] ? \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $set['optin_tstamp']) : '');
        $leadData               =
            \Database::getInstance()->prepare("SELECT * FROM tl_lead_data WHERE pid=?")->execute($lead->id);

        while ($leadData->next()) {
            StringUtil::flatten(deserialize($leadData->value), 'lead_' . $leadData->name, $tokens);
        }

        $objNotification = Notification::findByPk($this->leadOptInSuccessNotification);
        if (null !== $objNotification) {
            $objNotification->send($tokens);
        }

        $this->Template->successMessage =
            StringUtil::recursiveReplaceTokensAndTags($this->leadOptInSuccessMessage, $tokens);
    }
}
