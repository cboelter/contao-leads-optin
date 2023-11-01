<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin\Trait;

use Boelter\LeadsOptin\Util\Constants;
use Codefog\HasteBundle\StringParser;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use NotificationCenter\Util\Form;

trait TokenTrait
{
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
    protected function generateTokens(Connection $db, StringParser $stringParser, array $arrData, array $arrForm, array $arrFiles, array $arrLabels, string $delimiter = ','): array
    {
        $arrTokens = [];
        $arrTokens['raw_data'] = '';
        $arrTokens['raw_data_filled'] = '';

        foreach ($arrData as $k => $v) {
            if (Constants::$OPTIN_FORMFIELD_NAME === $k) {
                continue;
            }

            $stringParser->flatten($v, 'form_'.$k, $arrTokens, $delimiter);
            $arrTokens['formlabel_'.$k] = $arrLabels[$k] ?? ucfirst($k);
            $arrTokens['raw_data'] .= ($arrLabels[$k] ?? ucfirst($k)).': '.(\is_array($v) ? implode(', ', $v) : $v)."\n";

            if (\is_array($v) || \strlen($v)) {
                $arrTokens['raw_data_filled'] .= ($arrLabels[$k] ?? ucfirst($k)).': '.(\is_array($v) ? implode(', ', $v) : $v)."\n";
            }
        }

        foreach ($arrForm as $k => $v) {
            $stringParser->flatten($v, 'formconfig_'.$k, $arrTokens, $delimiter);
        }

        $arrLeadData = $this->getLeadData($db, $arrForm, $arrData);

        foreach ($arrLeadData as $k => $v) {
            $stringParser->flatten($v, 'lead_'.$k, $arrTokens, $delimiter);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        // Upload fields
        $arrFileNames = [];

        foreach ($arrFiles as $fieldName => $file) {
            $arrTokens['form_'.$fieldName] = Form::getFileUploadPathForToken($file);
            $arrFileNames[] = $file['name'];
        }
        $arrTokens['filenames'] = implode($delimiter, $arrFileNames);

        return $arrTokens;
    }

    /**
     * @param array<mixed> $formConfig
     * @param array<mixed> $postData
     *
     * @throws Exception
     *
     * @return array<mixed>
     */
    private function getLeadData(Connection $db, array $formConfig, array $postData): array
    {
        $leadData = [];
        $fields = $this->getFormFields($db, (int) $formConfig['id'], (int) $formConfig['leadMain']);

        foreach ($fields as $field) {
            if (\array_key_exists($field['name'], $postData) && $postData[$field['name']]) {
                $leadData[$field['name']] = $postData[$field['name']];
            }
        }

        return $leadData;
    }

    /**
     * @throws Exception
     *
     * @return array<mixed>
     */
    private function getFormFields(Connection $db, int $formId, int $mainId): array
    {
        if ($mainId > 0) {
            return $db->fetchAllAssociative(
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

        return $db->fetchAllAssociative(
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
