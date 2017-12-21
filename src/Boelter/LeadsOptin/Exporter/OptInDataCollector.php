<?php

/**
 * The leads optin extension allows you to store leads with double optin function.
 *
 * PHP version 5
 *
 * @package    LeadsOptin
 * @author     Christopher Bölter <kontakt@boelter.eu>
 * @copyright  Christopher Bölter 2017
 * @license    LGPL.
 * @filesource
 */

namespace Boelter\LeadsOptin\Exporter;

use Leads\DataCollector;

class OptInDataCollector extends DataCollector
{
    /**
     * Cache for getExportData()
     *
     * @var array
     */
    private $getExportDataCache = array();

    /**
     * Fetches the export (tl_lead_data) data. Use setLeadDataIds() if you want to limit the
     * result to a given array of tl_lead_data ids.
     *
     * @return array
     */
    public function getExportData()
    {
        $cacheKey = $this->getCacheKey();

        if (array_key_exists($cacheKey, $this->getExportDataCache)) {
            return $this->getExportDataCache[$cacheKey];
        }

        $where = array('tl_lead.master_id=?');

        if (0 !== count($this->getLeadDataIds())) {
            $where[] = 'tl_lead.id IN('.implode(',', $this->getLeadDataIds()).')';
        }

        if (null !== $this->getFrom()) {
            $where[] = 'tl_lead.created >= '.$this->getFrom();
        }

        if (null !== $this->getTo()) {
            $where[] = 'tl_lead.created <= '.$this->getTo();
        }

        $data = array();
        $db   = \Database::getInstance()->prepare(
            "
            SELECT
                tl_lead_data.*,
                tl_lead.optin_tstamp,
                tl_lead.optin_ip,
                tl_lead.optin_token,
                tl_lead.created,
                tl_lead.form_id AS form_id,
                (SELECT title FROM tl_form WHERE id=tl_lead.form_id) AS form_name,
                tl_lead.member_id AS member_id,
                IFNULL((SELECT CONCAT(firstname, ' ', lastname) FROM tl_member WHERE id=tl_lead.member_id), '') AS member_name
            FROM tl_lead_data
            LEFT JOIN tl_lead ON tl_lead.id=tl_lead_data.pid
            WHERE ".implode(' AND ', $where)."
            ORDER BY tl_lead.created DESC
        "
        )->execute($this->getFormId());

        while ($db->next()) {
            $data[$db->pid][$db->field_id] = $db->row();
        }

        $this->getExportDataCache[$cacheKey] = $data;

        return $data;
    }
}