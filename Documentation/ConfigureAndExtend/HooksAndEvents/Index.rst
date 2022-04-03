.. include:: /Includes.rst.txt

.. highlight:: rst

.. _hooksandevents:


================
Hooks and events
================


.. _events:

PSR-14 Events
=============

.. _events-register:

Registering event handlers
--------------------------

For compatibility with TYPO3 v9, PSR-14 events are also supported through
Signals and Slots. In our implementation the slots are actually implemented as
PSR-14-compatible event handler classes. The same class is used both for Events
in TYPO3 v10 and Signals and Slots in TYPO3 v9.

PSR-14 in TYPO3 v10 events are registered the usual way, in
`Configuration/Services.yaml`. See :ref:`t3coreapi:registering-the-event-listener`.

For registering the same event handler as a Slot in TYPO3 v9, we recommend using
the convenience method :php:`\TYPO3\CMS\FrontendEditing\Utility\CompatibilityUtility::registerEventHandlerAsSignalSlot`.

Example
~~~~~~~

Registering an event handler as a signal slot in TYPO3 v9. (Added to
`ext_localconf.php`.)

.. code-block:: php

   CompatibilityUtility::registerEventHandlerAsSignalSlot(
       \TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEvent::class,
       \Foo\Bar\EventHandler\RemoveUtf8CharactersPrepareFieldUpdateEventHandler::class
   );

.. _events-preparefieldupdateevent:

PrepareFieldUpdateEvent
-----------------------

:aspect:`Event Class`
   \TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEvent

:aspect:`Event Handler Interface`
   \TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEventHandlerInterface

Called before saved data from Frontend Editing is persisted to the database.


Example event handler
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace Foo\Bar\EventHandler;

   use TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEvent;
   use TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEventHandlerInterface;

   class RemoveUtf8CharactersPrepareFieldUpdateEventHandler implements PrepareFieldUpdateEventHandlerInterface
   {
       public function __invoke(PrepareFieldUpdateEvent $event): void
       {
         $event->setContent(utf8_decode($event->getContent()))
       }
   }


.. _hooks:

Hooks
=====

This is used in case you need to influence on a process of wrapping with drop zone of some specific content elements

- Register your hook in ext_localconf.php

  .. code-block:: php

    <?php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['frontend_editing']['FrontendEditingPanel']['dropzoneModifiers'][] = \Your\NameSpace\YourClass::class;

- Create hook class to control drop zone wrapping process

  .. code-block:: php

    <?php

    use Your\NameSpace;

    class YourClass implements \TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingDropzoneModifier
    {
        /**
        * @param string $table
        * @param integer $editUid
        * @param array $dataArr
        * @param string $content
        * @return string $content
        */
        public function wrapWithDropzone(
          string $table,
          int $editUid,
          array $dataArr,
          string &$content
        ): bool {
            // TODO: Implement wrapWithDropzone() method.
            // return true if no need for further processing, otherwise false
        }
    }
