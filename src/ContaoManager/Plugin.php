<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin\ContaoManager;

use Boelter\LeadsOptin\ContaoLeadsOptinBundle;
use Codefog\HasteBundle\CodefogHasteBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Terminal42\LeadsBundle\Terminal42LeadsBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoLeadsOptinBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    Terminal42LeadsBundle::class,
                    CodefogHasteBundle::class,
                ])
                ->setReplace(['contao-leads-optin-bundle']),
        ];
    }
}
