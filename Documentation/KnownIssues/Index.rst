.. include:: /Includes.rst.txt



.. _known_issues:

Known issues
------------

We are aware of a few issues that can affect the user experience. By explaining the issues here, we hope you will be able to circumvent them until they are fixed.

Blank page when disableNoCacheParameter is set
""""""""""""""""""""""""""""""""""""""""""""""

Pages may not render properly (blank page) if the setting `[FE][disableNoCacheParameter]` is enabled when using the Frontend Editor.

Route Enhancers
"""""""""""""""

Nice urls may not render properly when using a Route Enhancer for a record. The following error may occur `#1537633463 OutOfRangeException Hash not resolvable`.
To solve this add the following setting `[FE][pageNotFoundOnCHashError] = false` in LocalConfiguration.php.

Other issues
""""""""""""

A full list of bugs can be found in our `issue tracker <https://github.com/FriendsOfTYPO3/frontend_editing/issues>`__.
