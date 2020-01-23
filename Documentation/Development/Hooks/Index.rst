.. include:: ../../Includes.txt

.. highlight:: rst

.. _hooks:


Hooks
=====

Next hooks are available in Frontend editing

Frontend Editing Dropzone Modifier
----------------------------------

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
