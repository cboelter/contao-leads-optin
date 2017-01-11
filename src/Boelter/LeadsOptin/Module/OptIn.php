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

        $this->Template->errorMessage   = $this->leadOptInErrorMessage;
        $this->Template->successMessage = $this->leadOptInSuccessMessage;

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
    }
}
