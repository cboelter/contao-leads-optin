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

namespace Boelter\LeadsOptin\Controller\Module;

use Boelter\LeadsOptin\Handler\Hook;
use Codefog\HasteBundle\Form\Form;
use Codefog\HasteBundle\StringParser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\Date;
use Contao\Environment;
use Contao\FormModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Doctrine\DBAL\Connection;
use NotificationCenter\Model\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides the frontend module to handle the optin process.
 *
 * @property string|null $leadOptInErrorMessage
 * @property string|null $leadOptInSuccessMessage
 * @property bool        $leadOptIndNeedsUserInteraction
 * @property string|null $leadOptInUserInteractionMessage
 * @property string      $leadOptInUserInteractionSubmit
 * @property int         $leadOptInSuccessNotification
 * @property string      $leadOptInSuccessType
 * @property int         $leadOptInSuccessJumpTo
 */
#[AsFrontendModule(type: LeadsOptInModule::TYPE, category: 'leads', template: 'mod_leads_optin')]
class LeadsOptInModule extends AbstractFrontendModuleController
{
    public const TYPE = 'leadsoptin';

    public function __construct(private readonly Connection $db, private readonly StringParser $stringParser)
    {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response|null
    {
        $token = Input::get('token');
        $template->errorMessage = $model->leadOptInErrorMessage;

        if (!$token) {
            $template->isError = true;

            return $template->getResponse();
        }

        $arrLead = $this->db->prepare('SELECT * FROM tl_lead Where optin_token = ? AND optin_token <> ? AND optin_tstamp = ?')
            ->executeQuery([$token, '', '0'])
            ->fetchAssociative()
        ;

        if (!$arrLead || null === ($form = FormModel::findById($arrLead['form_id']))) {
            $template->isError = true;

            return $template->getResponse();
        }

        // check if we need to generate a form to confirm a real user uses the optIn link
        if ($model->leadOptIndNeedsUserInteraction) {
            $tokenForm = new Form('optin-check', 'POST', static fn ($objHaste) => Input::post('FORM_SUBMIT') === $objHaste->getFormId());

            $tokenForm->addFormField('mandatory', [
                'inputType' => 'explanation',
                'eval' => ['text' => $model->leadOptInUserInteractionMessage],
            ]);

            $tokenForm->addFormField('submit', [
                'label' => $model->leadOptInUserInteractionSubmit,
                'inputType' => 'submit',
            ]);

            // Checks whether a form has been submitted
            if (!$tokenForm->validate()) {
                // show token form if form has not been submitted
                $template->tokenForm = $tokenForm->generate();
                $template->showTokenForm = true;

                return $template->getResponse();
            }
        }

        $set = [
            'optin_tstamp' => time(),
            'optin_token' => '',
        ];

        if ($form->leadOptInStoreIp) {
            $set['optin_ip'] = Environment::get('ip');
        }

        $updated = $this->db->update('tl_lead', $set, ['id' => $arrLead['id'], 'optin_token' => $token, 'optin_tstamp' => '0']);

        if (0 === $updated) {
            $template->isError = true;

            return $template->getResponse();
        }

        $formConfig = $form->row();
        $tokens = $this->generateTokens(
            StringUtil::deserialize($arrLead['post_data'], true),
            $formConfig,
            StringUtil::deserialize($arrLead['optin_files'], true),
            StringUtil::deserialize($arrLead['optin_labels'], true),
            ','
        )
        ;

        $tokens['lead_created'] = Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $arrLead['created']);
        $tokens['optin_tstamp'] = Date::parse(Config::get('datimFormat'), $set['optin_tstamp']);

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['onLeadOptinSuccess']) && \is_array($GLOBALS['TL_HOOKS']['onLeadOptinSuccess'])) {
            foreach ($GLOBALS['TL_HOOKS']['onLeadOptinSuccess'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($arrLead, $tokens, $this);
                } elseif (\is_callable($callback)) {
                    $callback($arrLead, $tokens, $this);
                }
            }
        }

        if (null !== ($objNotification = Notification::findByPk($model->leadOptInSuccessNotification))) {
            $objNotification->send($tokens, $GLOBALS['TL_LANGUAGE']);
        }

        if (
            'redirect' === $model->leadOptInSuccessType &&
            0 !== $model->leadOptInSuccessJumpTo &&
            ($page = PageModel::findWithDetails($model->leadOptInSuccessJumpTo)) !== null
        ) {
            Controller::redirect($page->getFrontendUrl());
        }

        $template->successMessage = $this->stringParser->recursiveReplaceTokensAndTags($model->leadOptInSuccessMessage, $tokens);

        return $template->getResponse();
    }

    /**
     * Generate the tokens.
     *
     * @param array<mixed> $arrData
     * @param array<mixed> $arrForm
     * @param array<mixed> $arrFiles
     * @param array<mixed> $arrLabels
     *
     * @return array<mixed>
     */
    private function generateTokens(array $arrData, array $arrForm, array $arrFiles, array $arrLabels, string $delimiter): array
    {
        $arrTokens = [];
        $arrTokens['raw_data'] = '';
        $arrTokens['raw_data_filled'] = '';

        foreach ($arrData as $k => $v) {
            if (Hook::$OPTIN_FORMFIELD_NAME === $k) {
                continue;
            }

            $this->stringParser->flatten($v, 'form_'.$k, $arrTokens, $delimiter);
            $arrTokens['formlabel_'.$k] = $arrLabels[$k] ?? ucfirst($k);
            $arrTokens['raw_data'] .= ($arrLabels[$k] ?? ucfirst($k)).': '.(\is_array($v) ? implode(', ', $v) : $v)."\n";

            if (\is_array($v) || \strlen($v)) {
                $arrTokens['raw_data_filled'] .= ($arrLabels[$k] ?? ucfirst($k)).': '.(\is_array($v) ? implode(', ', $v) : $v)."\n";
            }
        }

        foreach ($arrForm as $k => $v) {
            $this->stringParser->flatten($v, 'formconfig_'.$k, $arrTokens, $delimiter);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        // Upload fields
        $arrFileNames = [];

        foreach ($arrFiles as $fieldName => $file) {
            $arrTokens['form_'.$fieldName] = \NotificationCenter\Util\Form::getFileUploadPathForToken($file);
            $arrFileNames[] = $file['name'];
        }
        $arrTokens['filenames'] = implode($delimiter, $arrFileNames);

        return $arrTokens;
    }
}