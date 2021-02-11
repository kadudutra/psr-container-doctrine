<?php

declare(strict_types=1);

namespace KaduDutraTest\PsrContainerDoctrine\TestAsset;

use Doctrine\ORM\Event\OnFlushEventArgs;

class StubEventListener
{
    public function onFlush(OnFlushEventArgs $args) : void
    {
    }
}
