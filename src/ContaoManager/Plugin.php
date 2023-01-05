<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin\ContaoManager;

use Boelter\LeadsOptin\ContaoLeadsOptinBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

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
                    'leads'
                ])
                ->setReplace(['contao-leads-optin-bundle']),
        ];
    }
}
