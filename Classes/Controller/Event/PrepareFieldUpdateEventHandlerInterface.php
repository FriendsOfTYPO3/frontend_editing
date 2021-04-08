<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Controller\Event;

/**
 * Interface for handling PrepareFieldUpdate events.
 */
interface PrepareFieldUpdateEventHandlerInterface
{
    /**
     * Handle a PrepareFieldUpdateEvent.
     *
     * @param PrepareFieldUpdateEvent $event
     */
    public function __invoke(PrepareFieldUpdateEvent $event): void;
}
