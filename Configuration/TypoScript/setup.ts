plugin.tx_frontendediting {
    view {
        layoutRootPath = {$plugin.tx_frontendediting.view.layoutRootPath}
        templateRootPath = {$plugin.tx_frontendediting.view.templateRootPath}
        partialRootPath = {$plugin.tx_frontendediting.view.partialRootPath}
    }
}

[globalVar = TSFE : beUserLogin > 0]
# Additional page plugin for save requests as those need a BE-USER
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

# Disable output of newlines
lib.parseFunc_RTE.nonTypoTagStdWrap.encapsLines >
[global]