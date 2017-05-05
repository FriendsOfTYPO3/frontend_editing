.. include:: ../../Includes.txt

.. highlight:: rst

========
Hooks
========

Next hooks are available in Frontend editing panel

Frontend Editing Dropzone Modifier
----------------------------------

- Register your hook

  .. code-block:: php
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['frontend_editing']['FrontendEditingPanel']['dropzoneModifiers'][] = \Your\NameSpace\YourClass::class;

- Create hook class to control drop zone wrapping process
    class Your\NameSpace\YourClass implements \TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingDropzoneModifier
    {
        public function wrapWithDropzone(
          string $table,
          int $editUid,
          array $dataArr,
          string &$content
    ): bool {
        // TODO: Implement wrapWithDropzone() method.
        // return true if no need for futher processing, otherwise false
    }
}