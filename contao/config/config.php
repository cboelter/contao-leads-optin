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

use Contao\System;
use Symfony\Component\HttpFoundation\Request;

// Backend styles
if (
    System::getContainer()->get('contao.routing.scope_matcher')
        ->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))
) {
    $GLOBALS['TL_CSS']['leads_optin'] = 'bundles/cgoitleadsoptin/css/leads-optin.css|static';
}
