<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoLeadsOptinBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
