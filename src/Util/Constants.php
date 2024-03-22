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

namespace Cgoit\LeadsOptinBundle\Util;

class Constants
{
    public static string $OPTIN_FORMFIELD_NAME = 'leads-opt-in-id';

    public static int $TOKEN_VALID_PERIOD = 3 * 24 * 60 * 60;
}
