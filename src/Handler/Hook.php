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

use Codefog\HasteBundle\StringParser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\PageModel;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use NotificationCenter\Model\Notification;

/**
 * Provides several function to access leads hooks and send notifications.
 */
class Hook
{
    public static string $OPTIN_FORMFIELD_NAME = 'leads-opt-in-id';

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
        $submittedData[self::$OPTIN_FORMFIELD_NAME] = $uniqueId;
    }

    /**
     * Access the processFormData hook and handle the optin.
     *
     * @param array<mixed>      $postData
     * @param array<mixed>      $formConfig
     * @param array<mixed>|null $files
     * @param array<mixed>      $labels
     */
    #[AsHook('processFormData')]
    public function appendOptInData(array $postData, array $formConfig, array|null $files, array $labels, Form $form): void
    {
        if (!$formConfig['leadEnabled'] || !isset($formConfig['leadOptIn']) || !$formConfig['leadOptIn']) {
            return;
        }

        if (!empty($postData[self::$OPTIN_FORMFIELD_NAME])) {
            $arrLead = $this->db->prepare('SELECT id FROM tl_lead WHERE main_id=? and form_id=? and post_data=?')
                ->executeQuery([$formConfig['leadMain'] ?: $formConfig['id'], $formConfig['id'], serialize($postData)])
                ->fetchAssociative()
            ;

            if (empty($arrLead)) {
                return;
            }

            $lead = $arrLead['id'];

            $token = md5(uniqid((string) mt_rand(), true));
            $set = [
                'optin_token' => $token,
                'optin_notification_tstamp' => time(),
                'optin_files' => !empty($files) ? serialize($files) : null,
                'optin_labels' => serialize($labels),
            ];

            $this->db->update('tl_lead', $set, ['id' => $lead]);

            $formData = [];
            $fields = $this->getFormFields((int) $formConfig['id'], (int) $formConfig['leadMain']);

            foreach ($fields as $field) {
                if (\array_key_exists($field['name'], $postData) && $postData[$field['name']]) {
                    $formData[$field['name']] = $postData[$field['name']];
                }
            }

            $tokens = [];
            $this->stringParser->flatten($formData, 'lead', $tokens);
            unset($tokens['form']);

            $tokens['optin_token'] = $token;
            $tokens['optin_url'] = $this->generateOptInUrl($token, $formConfig);

            $objNotification = Notification::findByPk($formConfig['leadOptInNotification']);
            $objNotification?->send($tokens); // @phpstan-ignore-line
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

    /**
     * @throws Exception
     *
     * @return array<mixed>
     */
    private function getFormFields(int $formId, int $mainId): array
    {
        if ($mainId > 0) {
            return $this->db->fetchAllAssociative(
                <<<'SQL'
                        SELECT
                            main_field.*,
                            form_field.id AS field_id,
                            form_field.name AS postName
                        FROM tl_form_field form_field
                            LEFT JOIN tl_form_field main_field ON form_field.leadStore=main_field.id
                        WHERE
                            form_field.pid=?
                          AND main_field.pid=?
                          AND form_field.leadStore>0
                          AND main_field.leadStore='1'
                          AND form_field.invisible=''
                        ORDER BY main_field.sorting;
                    SQL,
                [$formId, $mainId]
            );
        }

        return $this->db->fetchAllAssociative(
            <<<'SQL'
                    SELECT
                        *,
                        id AS field_id,
                        name AS postName
                    FROM tl_form_field
                    WHERE pid=?
                      AND leadStore='1'
                      AND invisible=''
                    ORDER BY sorting
                SQL,
            [$formId]
        );
    }
}
