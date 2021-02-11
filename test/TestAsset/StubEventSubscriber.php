<?php

declare(strict_types=1);

namespace KaduDutraTest\PsrContainerDoctrine\TestAsset;

use Doctrine\Common\EventSubscriber;

class StubEventSubscriber implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents() : array
    {
        return ['foo'];
    }
}
