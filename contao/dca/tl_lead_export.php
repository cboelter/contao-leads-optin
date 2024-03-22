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

// Palettes
$GLOBALS['TL_DCA']['tl_lead_export']['palettes']['optinCsv'] =
    '{name_legend},name,type,filename;{config_legend},headerFields,export;{date_legend:hide},lastRun,skipLastRun';
