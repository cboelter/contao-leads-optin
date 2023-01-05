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

namespace Boelter\LeadsOptin\Exporter;

use Contao\Date;
use Contao\System;
use Haste\IO\Reader\ArrayReader;
use Haste\IO\Writer\CsvFileWriter;
use Leads\DataCollector;
use Leads\Exporter\ExportFailedException;
use Leads\Exporter\Utils\File;
use Leads\Exporter\Utils\Row;

class Csv extends \Leads\Exporter\Csv
{

    /**
     * Exports a given set of data row ids using a given configuration.
     *
     * @param \Database\Result $config
     * @param array|null       $ids
     *
     * @throws ExportFailedException
     */
    public function export($config, $ids = null)
    {
        $config->type   = 'csv';

        $dataCollector = $this->prepareOptInDataCollector($config, $ids);

        $reader      = new ArrayReader($dataCollector->getExportData());
        $writer      = new CsvFileWriter('system/tmp/'.File::getName($config));
        $optInFields = $this->getOptInFields();

        // Add header fields
        if ($config->headerFields)
        {
            $headerFields = $this->prepareDefaultHeaderFields($config, $dataCollector);

            foreach ($optInFields as $field)
            {
                $headerFields[] = $field['name'];
            }

            $reader->setHeaderFields($headerFields);
            $writer->enableHeaderFields();
        }

        $exportConfig = $this->prepareDefaultExportConfig($config, $dataCollector);

        foreach ($optInFields as $field => $config)
        {
            $exportConfig[] = $config;
        }

        $row = new Row($config, $exportConfig);

        $writer->setRowCallback(function($data) use ($row) {
            return $row->compile($data);
        });

        $this->handleDefaultExportResult($writer->writeFrom($reader));

        $this->updateLastRun($config);

        $objFile = new \Contao\File($writer->getFilename());
        $objFile->sendToBrowser();
    }

    /**
     * Prepares the default DataCollector instance based on the configuration.
     *
     * @param \Database\Result $config
     * @param array|null       $ids
     *
     * @return DataCollector
     */
    protected function prepareOptInDataCollector($config, $ids = null)
    {
        $dataCollector = new OptInDataCollector($config->master);

        if (null !== $ids)
        {
            $dataCollector->setLeadDataIds($ids);
        }

        $this->newLastRun = Date::floorToMinute();

        if ($config->skipLastRun)
        {
            $dataCollector->setFrom($config->lastRun);
            $dataCollector->setTo($this->newLastRun - 1);
        }

        return $dataCollector;
    }

    protected function getOptInFields()
    {
        System::loadLanguageFile('tl_lead_export');

        return [
            'optin_token'  => [
                'field'       => 'optin_token',
                'name'        => $GLOBALS['TL_LANG']['tl_lead_export']['optin_token'],
                'value'       => 'value',
                'valueColRef' => 'optin_token',
            ],
            'optin_tstamp' => [
                'field'       => 'optin_tstamp',
                'name'        => $GLOBALS['TL_LANG']['tl_lead_export']['optin_tstamp'],
                'format'      => 'datim',
                'value'       => 'value',
                'valueColRef' => 'optin_tstamp',
            ],
            'optin_ip'     => [
                'field'       => 'optin_ip',
                'name'        => $GLOBALS['TL_LANG']['tl_lead_export']['optin_ip'],
                'value'       => 'value',
                'valueColRef' => 'optin_ip',
            ],
        ];
    }
}
