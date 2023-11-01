<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin\Exporter;

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
