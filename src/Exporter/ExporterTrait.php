<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-leads-optin-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @author     Christopher Bölter
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\LeadsOptinBundle\Exporter;

use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\Export\AbstractExporter;

trait ExporterTrait
{
    /**
     * @param array<mixed> $arrColumns
     *
     * @return array<mixed>
     */
    protected function addColumns(TranslatorInterface $translator, array $arrColumns): array
    {
        $arrOptinKeys = [
            [
                'id' => '_optin_token',
                'name' => $translator->trans('tl_lead_export.optin_token', [], 'contao_tl_lead_export'),
                'output' => AbstractExporter::OUTPUT_VALUE,
                'value' => static fn ($lead) => $lead['optin_token'],
                'label' => static fn ($lead) => '',
            ],
            [
                'id' => '_optin_tstamp',
                'name' => $translator->trans('tl_lead_export.optin_tstamp', [], 'contao_tl_lead_export'),
                'output' => AbstractExporter::OUTPUT_VALUE,
                'format' => 'datim',
                'value' => static fn ($lead) => $lead['optin_tstamp'],
                'label' => static fn () => '',
            ],
            [
                'id' => '_optin_ip',
                'name' => $translator->trans('tl_lead_export.optin_ip', [], 'contao_tl_lead_export'),
                'output' => AbstractExporter::OUTPUT_VALUE,
                'value' => static fn ($lead) => $lead['optin_ip'],
                'label' => static fn () => '',
            ],
        ];

        return array_merge($arrColumns, $arrOptinKeys);
    }
}
