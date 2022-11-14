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

namespace Boelter\LeadsOptin\Module;

use Boelter\LeadsOptin\Notification\OptinMessage;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\FormModel;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Date;
use Haste\Form\Form;
use Haste\Util\StringUtil as HasteStringUtil;
use Input;
use NotificationCenter\Model\Notification;

/**
 * Provides the frontend module to handle the optin process.
 *
 * @package Boelter\LeadsOptin\Module
 */
class OptIn extends Module
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
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard =
                '### ' . mb_strtoupper($GLOBALS['TL_LANG']['FMD']['leadsoptin'][0], 'UTF-8') . ' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module and handle the opt-in process.
     */
    protected function compile()
    {
        $token = Input::get('token');
        $this->Template->errorMessage = $this->leadOptInErrorMessage;

        if (!$token)
        {
            return $this->Template->isError = true;
        }

        $database = Database::getInstance();

        $lead = $database->prepare("SELECT * FROM tl_lead Where optin_token = ? AND optin_token <> ? AND optin_tstamp = ?")
            ->limit(1)
            ->execute($token, '', '0');

        if (0 === $lead->numRows || null === ($form = FormModel::findById($lead->form_id)))
        {
            return $this->Template->isError = true;
        }

        // check if we need to generate a form to confirm a real user uses the optIn link
        if ($this->leadOptIndNeedsUserInteraction)
        {

            $tokenForm = new Form('optin-check', 'POST', function($objHaste) {
                return Input::post('FORM_SUBMIT') === $objHaste->getFormId();
            });

            $tokenForm->addFormField('mandatory', [
                'inputType' => 'explanation',
                'eval' => ['text' => $this->leadOptInUserInteractionMessage]
            ]);

            $tokenForm->addFormField('submit', [
                'label'     => $this->leadOptInUserInteractionSubmit,
                'inputType' => 'submit'
            ]);

            // Checks whether a form has been submitted
            if (!$tokenForm->validate())
            {
                // show token form if form has not been submitted
                $this->Template->tokenForm = $tokenForm->generate();
                return $this->Template->showTokenForm = true;
            }
        }

        $set = [
            'optin_tstamp' => time(),
            'optin_token'  => ''
        ];

        if ($form->leadOptInStoreIp)
        {
            $set['optin_ip'] = Environment::get('ip');
        }

        $updated = $database->prepare("UPDATE tl_lead %s Where id = ? AND optin_token = ? AND optin_tstamp = ?")
            ->set($set)
            ->execute($lead->id, $token, '0');

        if (0 === $updated->affectedRows)
        {
            return $this->Template->isError = true;
        }

        $formConfig = $form->row();
        $tokens     = [];

        HasteStringUtil::flatten($formConfig, 'formconfig', $tokens);

        $tokens['lead_created'] = Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $lead->created);
        $tokens['optin_tstamp'] =
            ($set['optin_tstamp'] ? Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $set['optin_tstamp']) : '');
        $leadData = $database->prepare("SELECT * FROM tl_lead_data WHERE pid=?")
            ->execute($lead->id);

        while ($leadData->next())
        {
            HasteStringUtil::flatten(StringUtil::deserialize($leadData->value), 'lead_' . $leadData->name, $tokens);
        }

        $this->onOptInSuccess($leadData, $tokens);

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['onLeadOptinSuccess']) && is_array($GLOBALS['TL_HOOKS']['onLeadOptinSuccess']))
        {
            foreach ($GLOBALS['TL_HOOKS']['onLeadOptinSuccess'] as $callback)
            {
                if (is_array($callback))
                {
                    System::importStatic($callback[0])->{$callback[1]}($leadData, $tokens);
                }
                elseif (is_callable($callback))
                {
                    $callback($leadData, $tokens);
                }
            }
        }

        if (null !== ($objNotification = Notification::findByPk($this->leadOptInSuccessNotification)))
        {
            (new OptinMessage)->send($objNotification, $tokens);
        }

        if (
            $this->leadOptInSuccessType === 'redirect' &&
            $this->leadOptInSuccessJumpTo !== 0 &&
            ($page = PageModel::findWithDetails($this->leadOptInSuccessJumpTo)) !== null
        )
        {
            Controller::redirect($page->getFrontendUrl());
        }

        return $this->Template->successMessage =
            HasteStringUtil::recursiveReplaceTokensAndTags($this->leadOptInSuccessMessage, $tokens);
    }

    // OnOpt-In success function for extending Opt-In class
    protected function onOptInSuccess(&$leadData, &$tokens) {}
}
