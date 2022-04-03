.. include:: Includes.txt


.. _typoscript:

========================
TypoScript Configuration
========================

Each section refers to property names within `config.tx_frontendediting`.


.. _typoscript-customrecords:

customRecords
=============

:aspect:`DataType`
   array of table names with default values.

Default values for records created with the
:ref:`customDropZone <viewhelpers-customdropzone>` ViewHelper.

   .. code-block:: typoscript

      customRecords {
        tx_news_domain_model_news {
          pid = 6
        }
      }


.. _typoscript-customrecordediting:

customRecordEditing
===================

Configure :ref:`typoscript-custom-record-editing`.

   .. code-block:: typoscript

      customRecordEditing {
        tx_news_pi1 {
          actionName = detail
          recordName = news
          tableName = tx_news_domain_model_news
          listTypeName = news_pi1
        }
      }


.. _typoscript-contentpersistpreprocessing:

contentPersistPreProcessing
===========================

Modify data for a specific table, field, and record type whe saved in Frontend
Editing before the data it is persisted to the database. This allows you to
remove any (or all) HTML tags or modify the data to better suit the way it
should be persisted.

This property consists of nested arrays.

   .. code-block:: typoscript

      contentPersistPreProcessing {
        <tableName> {
          <type> {
            <field> {
              # Any stdWrap property
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
   The field name. Add any :t3tsref:`stdwrap` configurations to modify the data.
   You can also use the :t3tsref:`userfunc` property to modify data using PHP.

Example
-------

Strip all HTML tags from the `bodytext` field in the `tt_content` table if the
record type (e.g. the `CType` field for content elements) is "bullets".

   .. code-block:: typoscript

      contentPersistPreProcessing {
        tt_content {
          bullets {
            bodytext {
              stripHtml = 1
            }
          }
        }
      }

See also :ref:`typoscript-contentpersistpreprocessingpatterns`.


.. _typoscript-contentpersistpreprocessingpatterns:

contentPersistPreProcessingPatterns
===================================

Modify data for any field with a specific RTE preset before the data it is
persisted to the database. This allows you to remove any (or all) HTML tags or
modify the data to better suit the way it should be persisted.

   .. code-block:: typoscript

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

:aspect:`<preset>`
   An RTE preset defined in
   :php:`$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']`. Add any
   :t3tsref:`stdwrap` configurations to modify the data. The modification is
   applied to any field where `[config][enableRichtext]` or
   `[config][enableFrontendRichtext]` is set and
   `[config][frontendRichtextConfiguration]` or
   `[config][frontendRichtextConfiguration]` is set to the same as `<preset>`.


Example
-------

Strip all HTML tags from any field using the RTE preset "default" when saving
data in Frontend Editing.

   .. code-block:: typoscript

      contentPersistPreProcessing {
        default {
          stripHtml = 1
        }
      }


See also :ref:`typoscript-contentpersistpreprocessing`. This property is only
applied if no match was found there.


.. _typoscript-pagecontentpreprocessing:

pageContentPreProcessing
========================

:aspect:`DataType`
   :t3tsref:`stdwrap`

Transformations applied to the page being edited before it is sent to the user.
This is used to ensure features work as expected and inceptions are avoided.

Example
-------

This example from Frontend Editing's default TypoScript configuration modifies
forms so submitting a form produces an editable page. You might have to submit a
form to reach some editabe records through Frontend Editing.

   .. code-block:: typoscript

      pageContentPreProcessing {
        parseFunc {
          tags {
            form = TEXT
            form {
              current = 1
              # Add frontend_editing=true if this is a GET form (rather than POST)
              innerWrap = <input type="hidden" name="frontend_editing" value="true">|
              innerWrap.if {
                value {
                  data = parameters : method
                  case = lower
                }
                equals = get
              }
              dataWrap = <form { parameters : allParams }>|</form>
            }
          }
        }
      }
