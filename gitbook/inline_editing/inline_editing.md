# Inline editing

After the frontend editing have been activated from within TYPO3´s backend
there are one scenario that needs to been taking into account. It is what 
kind of templating engine are used for the frontend template for the websites
that you are using. 

## CSS Styled Content (css_styled_content)

If the installation are using the well known (and old) extension which is called
css_styled_content are being used. The functionality comes straight out of the
box and the editing can start directly.

## Fluid Styled Contet (fluid_styled_content)

When it comes to fluid_styled_content there are some things that needs to be
adjusted to your template to get the editing to work. First of all there is
a view helper that needs to be included and configured. 

First import the namespace:

    {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}

Next step is to find the content that you want editable and wrap it
with the view helper: 

    <fe:editable table="tt_content" field="bodytext" uid="{item.uid}">
        {item.bodytext}
    </fe:editable>

The available options are:

* **table**: The database table name to where the data should be saved
* **field**: The database field to where the data should be saved
* **uid**: The database field to where the data should be saved

A full example looks like this: 

    {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}
    
    <fe:editable table="{item.table}" field="{item.field}" uid="{item.uid}">
        {item.bodytext}
    </fe:editable>

The output would then look like the following in frontend edit mode: 

    <div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
        This is the content text to edit
    </div>

While not in frontend edit mode the output are the following: 

    This is the content text to edit