.. include:: /Includes.rst.txt


.. _tca:

=========================
Additions to standard TCA
=========================

Frontend Editing adds some configuration options to the TCA that can be used to
make fields behave in a specific manner when editing them in the frontend.


.. _tca-fielddefinitions:

Field definitions
=================

Found in :php:`$GLOBALS['TCA'][<table>]['columns'][<field>]`


.. _tca-enablefrontendrichtext:

[config][enableFrontendRichtext]
--------------------------------

:aspect:`DataType`
   boolean

Enable rich-text editing in FrontendEditing only.

Inherits and overrides the value from `[config][enableRichtext]`.


.. _tca-frontendrichtextconfiguration:

[config][frontendRichtextConfiguration]
---------------------------------------

:aspect:`DataType`
   string

Set or change the rich-text editing preset in FrontendEditing only.

Inherits and overrides the value from `[config][richtextConfiguration]`.
