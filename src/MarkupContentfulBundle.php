<?php

namespace Markup\ContentfulBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarkupContentfulBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        //don't register commands through the bundle as they are registered as services
    }
}
