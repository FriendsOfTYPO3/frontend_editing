.. include:: ../Includes.txt



.. _userts:

======
UserTS
======

It's possible to configure TSconfig for BE user or BE user group.

Disallow editing for content elements
=====================================

Possibility to disallow a user or a user group editing to a specific set of content elements.
A comma separated list of content id´s are provided, this setting is ignored for admin users.

.. code-block:: typoscript

    frontend_editing.disallow_content_editing = 29,30
