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

namespace Boelter\LeadsOptin\Handler;

use Boelter\LeadsOptin\Trait\TokenTrait;
use Boelter\LeadsOptin\Util\Constants;
use Codefog\HasteBundle\StringParser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\PageModel;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use NotificationCenter\Model\Notification;

/**
 * Provides several function to access leads hooks and send notifications.
 */
class Hook
{
    use TokenTrait;

    public function __construct(private readonly Connection $db, private readonly StringParser $stringParser)
    {
    }

    /**
     * @param array<mixed>  $submittedData
     * @param array<mixed>  $labels
     * @param array<Widget> $fields
     * @param Form          $form
     */
    #[AsHook('prepareFormData')]
    public function markPostData(array &$submittedData, array $labels, array $fields, Form $form): void
    {
        if (!isset($form->leadEnabled) || !$form->leadEnabled || !isset($form->leadOptIn) || !$form->leadOptIn) {
            return;
        }

        $uniqueId = md5(uniqid((string) mt_rand(), true));
        $submittedData[Constants::$OPTIN_FORMFIELD_NAME] = $uniqueId;
    }

    /**
     * Access the processFormData hook and handle the optin.
     *
     * @param array<mixed>      $postData
     * @param array<mixed>      $formConfig
     * @param array<mixed>|null $arrFiles
     * @param array<mixed>      $arrLabels
     */
    #[AsHook('processFormData')]
    public function appendOptInData(array $postData, array $formConfig, array|null $arrFiles, array $arrLabels, Form $form): void
    {
        if (!$formConfig['leadEnabled'] || !isset($formConfig['leadOptIn']) || !$formConfig['leadOptIn']) {
            return;
        }

        if (!empty($postData[Constants::$OPTIN_FORMFIELD_NAME])) {
            $arrLead = $this->db->fetchAssociative(
                'SELECT id FROM tl_lead WHERE main_id=? and form_id=? and post_data=?',
                [$formConfig['leadMain'] ?: $formConfig['id'], $formConfig['id'], serialize($postData)]
            );

            if (empty($arrLead)) {
                return;
            }

            $lead = $arrLead['id'];

            $token = md5(uniqid((string) mt_rand(), true));
            $set = [
                'optin_token' => $token,
                'optin_notification_tstamp' => time(),
                'optin_files' => !empty($arrFiles) ? serialize($arrFiles) : null,
                'optin_labels' => serialize($arrLabels),
            ];

            $this->db->update('tl_lead', $set, ['id' => $lead]);

            $tokens = $this->generateTokens(
                $this->db,
                $this->stringParser,
                $postData,
                $formConfig,
                $arrFiles ?: [],
                $arrLabels
            );

            $tokens['optin_token'] = $token;
            $tokens['optin_url'] = $this->generateOptInUrl($token, $formConfig);

            $objNotification = Notification::findByPk($formConfig['leadOptInNotification']);
            $objNotification?->send($tokens, $GLOBALS['TL_LANGUAGE']); // @phpstan-ignore-line
        }
    }

    /**
     * Generate the optin target url and pass it back.
     *
     * @param $token
     * @param array<mixed> $formConfig
     */
    private function generateOptInUrl(string $token, array $formConfig): string
    {
        $page = $GLOBALS['objPage'];

        if ($formConfig['leadOptInTarget']) {
            $page = PageModel::findWithDetails($formConfig['leadOptInTarget']);
        }

        $url = $page->getAbsoluteUrl();
        $parameter = '?token='.$token;

        return $url.$parameter;
    }
}
