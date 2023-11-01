<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin\Exporter;

use Codefog\HasteBundle\StringParser;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;

#[AsLeadsExporter('optinCsv')]
class CsvExporter extends \Terminal42\LeadsBundle\Export\CsvExporter
{
    use ExporterTrait;

    public function __construct(
        ServiceLocator $formatters,
        Connection $connection,
        private readonly TranslatorInterface $translator,
        StringParser $parser,
        ExpressionLanguage|null $expressionLanguage = null,
    ) {
        parent::__construct($formatters, $connection, $this->translator, $parser, $expressionLanguage);
    }

    /**
     * @return array<mixed>
     */
    protected function getColumns(): array
    {
        return $this->addColumns($this->translator, parent::getColumns());
    }

    protected function getFileExtension(): string
    {
        return '.csv';
    }
}
