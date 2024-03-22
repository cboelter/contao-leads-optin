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

namespace Cgoit\LeadsOptinBundle\Tests;

use Cgoit\LeadsOptinBundle\CgoitLeadsOptinBundle;
use PHPUnit\Framework\TestCase;

class CgoitLeadsOptinBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new CgoitLeadsOptinBundle();

        $this->assertInstanceOf(CgoitLeadsOptinBundle::class, $bundle);
    }
}
