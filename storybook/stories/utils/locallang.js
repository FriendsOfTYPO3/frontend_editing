// TODO: implement function to generate this file automatic
// This file exports the translation as the raw property and default namespace
// mapper as namespace property.
// The translation is a copy of the server side translation file (XLIFF)
// represented as a js object.
// The namespace mapper is a simple copy of the default namespace variable in
// TYPO3/CMS/FrontendEditing/Utils/Translator module.
module.exports = {
    raw: {
        // "settings.field.frontend_editing": "Enable Frontend Editing",
        // "settings.field.frontend_editing_overlay": "Frontend Editing: Enable right overlay bar",
        // "top-bar.to-backend": "To backend",
        // "top-bar.save-all": "Save All",
        // "top-bar.discard-all": "Discard",
        // "top-bar.logout": "Logout",
        // "left-bar.site-tree": "Site Tree",
        // "right-bar.tools-properties": "TOOLS &amp; PROPERTIES",
        // "right-bar.page-seo-score": "Page SEO Score",
        // "right-bar.seo-guide": "SEO Guide",
        // "right-bar.page-edit": "Edit page properties",
        // "right-bar.page-new": "Create new Page",

        'notifications.save-title':
            'Content saved',
        // 'notifications.save-description':
        //     'Saved the content with identifier:',
        'notifications.save-went-wrong':
            'Something went wrong',
        'notifications.no-changes-title':
            'No changes made',
        'notifications.no-changes-description':
            'There are currently no changes made to the content on the page!',
        'notifications.remove-all-changes':
            'Are you sure you want to remove all unsaved changes?',
        'notifications.unsaved-changes':
            'You have some unsaved changes. They will disappear if you navigate away!',
        'notifications.delete-content-element':
            'Are you sure you want to delete the content element?',
        // 'notifications.add-content-element':
        //     'You would have the option to add an element now, if this feature was implemented.',
        // 'notifications.content-locked':
        //     'The content is currently locked, do you still want to save?',
        'notifications.change_site_root':
            'You are going to switch to another site. Are you sure ?',
        'notifications.save-pages-title':
            'Page saved',
        // 'notifications.update.content.success':
        //     'Content updated (%s)',
        // 'notifications.update.pages.success':
        //     'Page title updated (%s)',
        // 'notifications.update.content.fail':
        //     'Content could not be updated (%s)',
        // 'notifications.update.pages.fail':
        //     'Page could not be updated (%s)',
        // 'notifications.new.content.success':
        //     'The content element was created',
        // 'notifications.new.content.fail':
        //     'The content element could not be created',
        'notifications.request.configuration.fail':
            'Could not fetch editor configurations due to a request error. ({0}, "{1}")',
        // "placeholder.label-wrap": "Enter %s here",
        // "placeholder.default-label": "Enter text here",
        // "right-bar.element-title": "Drag and drop on page to create a new '%s' content element.",
        // "right-bar.on-page.changed": "Modified:",
        // "right-bar.on-page.title": "Elements on page",
        // "right-bar.custom-record.title": "Custom records",
        // "left-bar.installation-mounts.change": "Choose site root",
        // "left-bar.installation-mounts.search_tip": "Site root:",
        // "left-bar.search-bar.search_tip": "Search:",
        // "left-bar.search-bar.search_placeholder": "enter search term...",
        'title.navigate':
            'Navigate',
        'button.discard_navigate':
            'Discard and Navigate',
        'button.save':
            'Save All',
        'button.cancel':
            'Cancel',
        'button.okay':
            'OK',
        'error.type.undefined':
            '\'{0}\' is undefined"',
        'error.type.not_function':
            '\'{0}\' is not a function',
        'error.type.not_integer':
            '\'{0}\' is not a integer',
        'error.type.key_invalid':
            'Invalid translation key: \'{0}\'',
        'translator.error.namespace_not_found':
            'Invalid namespace key: \'{0}\'',
    },
    namespaces: {
        translator: {
            translationKeyInvalid: 'error.type.key_invalid',
            namespaceMappingNotFound:
                'translator.error.namespace_not_found',
        },
        modal: {
            titleNavigate: 'title.navigate',
            discardLabel: 'button.discard_navigate',
            saveLabel: 'button.save',
            cancelLabel: 'button.cancel',
            okayLabel: 'button.okay',
            variableNotDefined: 'error.type.undefined',
            variableNotFunction: 'error.type.not_function',
            variableNotInteger: 'error.type.not_integer',
        },
        frontendEditing: {
            confirmNavigateWithChange: 'notifications.unsaved-changes',
        },
        editor: {
            actionUnsavedChanges: 'notifications.unsaved-changes',
            actionEditDelete: 'notifications.delete-content-element',
            initLoadFailed: 'notifications.delete-content-element',
        },
        gui: {
            updatedContentTitle: 'notifications.save-title',
            updatedPageTitle: 'notifications.save-pages-title',
            updateRequestErrorTitle: 'notifications.save-went-wrong',
            saveWithoutChange: 'notifications.no-changes-description',
            saveWithoutChangeTitle: 'notifications.no-changes-title',
            confirmDiscardChanges: 'notifications.remove-all-changes',
            confirmChangeSiteRoot: 'notifications.change_site_root',
            confirmChangeSiteRootWithChange:
                'notifications.unsaved-changes',
        }
    },
};
