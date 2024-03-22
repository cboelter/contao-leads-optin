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

namespace Cgoit\LeadsOptinBundle\ContaoManager;

use Cgoit\LeadsOptinBundle\CgoitLeadsOptinBundle;
use Codefog\HasteBundle\CodefogHasteBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Terminal42\LeadsBundle\Terminal42LeadsBundle;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(CgoitLeadsOptinBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    Terminal42LeadsBundle::class,
                    CodefogHasteBundle::class,
                    Terminal42NotificationCenterBundle::class,
                ])
                ->setReplace(['contao-leads-optin-bundle']),
        ];
    }
}
