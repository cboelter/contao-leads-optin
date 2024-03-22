<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-leads-optin for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @author     Christopher Bölter
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\LeadsOptinBundle\Exporter;

use Codefog\HasteBundle\StringParser;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;

#[AsLeadsExporter('optinXlsx')]
#[AsLeadsExporter('optinXls')]
#[AsLeadsExporter('optinExcel_csv')]
#[AsLeadsExporter('optinOds')]
#[AsLeadsExporter('optinHtml')]
class PhpSpreadsheetExporter extends \Terminal42\LeadsBundle\Export\PhpSpreadsheetExporter
{
    use ExporterTrait;

    public function __construct(
        string $projectDir,
        ServiceLocator $formatters,
        Connection $connection,
        private readonly TranslatorInterface $translator,
        StringParser $parser,
        ExpressionLanguage|null $expressionLanguage = null,
    ) {
        parent::__construct($projectDir, $formatters, $connection, $this->translator, $parser, $expressionLanguage);
    }

    /**
     * @return array<mixed>
     */
    protected function getColumns(): array
    {
        return $this->addColumns($this->translator, parent::getColumns());
    }

    /**
     * @return array<mixed>
     */
    protected function getConfig(): array
    {
        $arrConfig = parent::getConfig();
        $arrConfig['type'] = lcfirst(str_replace('optin', '', $arrConfig['type']));

        return $arrConfig;
    }
}
