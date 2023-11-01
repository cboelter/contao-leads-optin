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

namespace Boelter\LeadsOptin\Module;

use Boelter\LeadsOptin\Handler\Hook;
use Codefog\HasteBundle\Form\Form;
use Codefog\HasteBundle\StringParser;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller;
use Contao\Date;
use Contao\Environment;
use Contao\FormModel;
use Contao\Input;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use NotificationCenter\Model\Notification;

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
class OptIn extends Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_leads_optin';
    private StringParser|null $stringParser;
    private Connection $db;

    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        $this->stringParser = System::getContainer()->get(StringParser::class);
        $this->db = System::getContainer()->get('database_connection');
    }

    /**
     * Display a wildcard in the back end.
     */
    public function generate(): string
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard =
                '### '.mb_strtoupper($GLOBALS['TL_LANG']['FMD']['leadsoptin'][0], 'UTF-8').' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
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
    public function generateTokens(array $arrData, array $arrForm, array $arrFiles, array $arrLabels, string $delimiter): array
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

    /**
     * Generate the module and handle the opt-in process.
     */
    protected function compile(): void
    {
        $token = Input::get('token');
        $this->Template->errorMessage = $this->leadOptInErrorMessage;

        if (!$token) {
            $this->Template->isError = true;

            return;
        }

        $arrLead = $this->db->prepare('SELECT * FROM tl_lead Where optin_token = ? AND optin_token <> ? AND optin_tstamp = ?')
            ->executeQuery([$token, '', '0'])
            ->fetchAssociative()
        ;

        if (!$arrLead || null === ($form = FormModel::findById($arrLead['form_id']))) {
            $this->Template->isError = true;

            return;
        }

        // check if we need to generate a form to confirm a real user uses the optIn link
        if ($this->leadOptIndNeedsUserInteraction) {
            $tokenForm = new Form('optin-check', 'POST', static fn ($objHaste) => Input::post('FORM_SUBMIT') === $objHaste->getFormId());

            $tokenForm->addFormField('mandatory', [
                'inputType' => 'explanation',
                'eval' => ['text' => $this->leadOptInUserInteractionMessage],
            ]);

            $tokenForm->addFormField('submit', [
                'label' => $this->leadOptInUserInteractionSubmit,
                'inputType' => 'submit',
            ]);

            // Checks whether a form has been submitted
            if (!$tokenForm->validate()) {
                // show token form if form has not been submitted
                $this->Template->tokenForm = $tokenForm->generate();

                $this->Template->showTokenForm = true;

                return;
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
            $this->Template->isError = true;

            return;
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

        if (null !== ($objNotification = Notification::findByPk($this->leadOptInSuccessNotification))) {
            $objNotification->send($tokens, $GLOBALS['TL_LANGUAGE']);
        }

        if (
            'redirect' === $this->leadOptInSuccessType &&
            0 !== $this->leadOptInSuccessJumpTo &&
            ($page = PageModel::findWithDetails($this->leadOptInSuccessJumpTo)) !== null
        ) {
            Controller::redirect($page->getFrontendUrl());
        }

        $this->Template->successMessage = $this->stringParser->recursiveReplaceTokensAndTags($this->leadOptInSuccessMessage, $tokens);
    }
}
