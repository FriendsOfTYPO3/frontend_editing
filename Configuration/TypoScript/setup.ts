plugin.tx_frontendediting {
    view {
        layoutRootPaths.10 = {$plugin.tx_frontendediting.view.layoutRootPaths.10}
        templateRootPaths.10 = {$plugin.tx_frontendediting.view.templateRootPaths.10}
        partialRootPaths.10 = {$plugin.tx_frontendediting.view.partialRootPaths.10}
    }
}

[globalVar = TSFE : beUserLogin > 0]
# Additional page plugin for Content CRUD requests as those need a BE-USER
frontendEditing = PAGE
frontendEditing {
    typeNum = 1470741815

    config {
        disableAllHeaderCode = 1
        debug = 0
        xhtml_cleaning = none
        admPanel = 0
        metaCharset = utf-8
        no_cache = 1
        additionalHeaders = Content-type:application/json
    }

    10 < tt_content.list.20.frontendediting_frontend_editing
}

# Additional page plugin for Page tree CRUD requests as those need a BE-USER
frontendEditingPageTree = PAGE
frontendEditingPageTree {
    typeNum = 1477569731

    config {
        disableAllHeaderCode = 1
        debug = 0
        xhtml_cleaning = none
        admPanel = 0
        metaCharset = utf-8
        no_cache = 1
        additionalHeaders = Content-type:application/json
    }

    10 < tt_content.list.20.frontendediting_frontend_editing_page_tree
}

# Disable output of newlines
lib.parseFunc_RTE.nonTypoTagStdWrap.encapsLines >
[global]