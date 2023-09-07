.. include:: /Includes.rst.txt


.. _typoscript:

====================
Module Configuration
====================

Each section refers to property names within `module.tx_frontendediting`.


.. _typoscript-customrecords:

customRecords
=============

:aspect:`DataType`
   array of table names with default values.

Default values for records created with the
:ref:`customDropZone <viewhelpers-customdropzone>` ViewHelper.

.. code-block:: typoscript

   module.tx_frontendediting.settings {
     customRecords {
       tx_news_domain_model_news {
         pid = 6
       }
     }
   }


.. _typoscript-customrecordediting:

customRecordEditing
===================

Configure :ref:`typoscript-custom-record-editing`.

.. code-block:: typoscript

   config.tx_frontendediting {
     customRecordEditing {
       tx_news_pi1 {
         actionName = detail
         recordName = news
         tableName = tx_news_domain_model_news
         listTypeName = news_pi1
       }
     }
   }

========================
TypoScript Configuration
========================

Each section refers to property names within `config.tx_frontendediting`.


.. _typoscript-contentpersistpreprocessing:

contentPersistPreProcessing
===========================

Modify data for a specific table, field, and record type when saved in Frontend
Editing before the data is persisted to the database. This allows you to
remove any (or all) HTML tags or modify the data to better suit the way it
should be persisted.

This property consists of nested arrays.

.. code-block:: typoscript

   config.tx_frontendediting {
     contentPersistPreProcessing {
       <tableName> {
         <type> {
           <field> {
             # Any stdWrap property
           }
         }
       }
     }
   }

:aspect:`<tableName>`
   The name of the table.

:aspect:`<type>`
   The record type as defined in :php:`$GLOBALS['TCA'][<tableName>]['types']`.
   `0` (zero as string) is the default type. `*` is a wildcard that will apply
   to any type not explicitly defined.

:aspect:`<field>`
   The field name. Add any :ref:`stdwrap <t3tsref:stdwrap>` configurations to
   modify the data. You can also use the :ref:`userFunc <t3tsref:parsefunc-userFunc>`
   property to modify data using PHP.

Example 1
---------

Strip all HTML tags from the `bodytext` field in the `tt_content` table if the
record type (e.g. the `CType` field for content elements) is "bullets".

.. code-block:: typoscript

   config.tx_frontendediting {
     contentPersistPreProcessing {
       tt_content {
         bullets {
           bodytext {
             stripHtml = 1
           }
         }
       }
     }
   }

Example 2
---------

Convert the date of a news item from the frontend display format dd/mm/yyyy
to the ISO 8601 format YYYY-MM-DDT00:00:00Z, so that it can be saved correctly
in the database when the editor changes it.

.. code-block:: typoscript

   config.tx_frontendediting {
     contentPersistPreProcessing {
       tx_news_domain_model_news {
         0 {
           datetime {
             replacement {
               10 {
                 search = /([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/
                 replace = \3-\2-\1T00:00:00Z
                 useRegExp = 1
               }
             }
           }
         }
       }
     }
   }

See also :ref:`typoscript-contentpersistpreprocessingpatterns`.


.. _typoscript-contentpersistpreprocessingpatterns:

contentPersistPreProcessingPatterns
===================================

Modify data for any field with a specific RTE preset before the data is
persisted to the database. This allows you to remove any (or all) HTML tags or
modify the data to better suit the way it should be persisted.

.. code-block:: typoscript

   config.tx_frontendediting {
     contentPersistPreProcessingPatterns {
       <preset> {
         replacement {
           10 {
             search = #<br\s*\/?>#i
             replace.char = 10
             useRegExp = 1
           }
         }
       }
     }
   }

:aspect:`<preset>`
   An RTE preset defined in
   :php:`$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']`. Add any
   :ref:`stdwrap <t3tsref:stdwrap>` configurations to modify the data. The modification is
   applied to any field where `[config][enableRichtext]` or
   `[config][enableFrontendRichtext]` is set and
   `[config][frontendRichtextConfiguration]` or
   `[config][frontendRichtextConfiguration]` is set to the same as `<preset>`.


Example
-------

Strip all HTML tags from any field using the RTE preset "default" when saving
data in Frontend Editing.

.. code-block:: typoscript

   config.tx_frontendediting {
     contentPersistPreProcessing {
       default {
         stripHtml = 1
       }
     }
   }


See also :ref:`typoscript-contentpersistpreprocessing`. This property is only
applied if no match was found there.
