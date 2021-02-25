.. include:: ../Includes.txt



.. _ts-config:

TS Config
---------

It's possible to configure TSconfig for BE user or BE user group.

Disable access to BE
^^^^^^^^^^^^^^^^^^^^

There might be cases when editors should only be able to use frontend editing to work with a content.
BE access can be disabled by setting TSconfig for user or entire BE group. This setting is ignored for admin users.

.. code-block:: typoscript

    # Disallow BE access
    frontend_editing.disallow_backend_access = 1

Disallow editing for content elements
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Possibility to disallow a user or a user group editing to a specific set of content elements.
A comma separated list of content id´s are provided, this setting is ignored for admin users.

.. code-block:: typoscript

    # Disallow editing for content elements
    frontend_editing.disallow_content_editing = 29,30