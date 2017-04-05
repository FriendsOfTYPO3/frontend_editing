[globalVar = TSFE : beUserLogin > 0]

lib.fluidContent {
    stdWrap {
        editIcons = tt_content:header
    }
}

tt_content.bullets.stdWrap < lib.fluidContent.stdWrap
tt_content.div.stdWrap < lib.fluidContent.stdWrap
tt_content.header.stdWrap < lib.fluidContent.stdWrap
tt_content.html.stdWrap < lib.fluidContent.stdWrap
tt_content.image.stdWrap < lib.fluidContent.stdWrap
tt_content.list.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_abstract.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_categorized_pages.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_pages.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_recently_updated.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_related_pages.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_section.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_section_pages.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_sitemap.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_sitemap_pages.stdWrap < lib.fluidContent.stdWrap
tt_content.menu_subpages.stdWrap < lib.fluidContent.stdWrap
tt_content.shortcut.stdWrap < lib.fluidContent.stdWrap
tt_content.table.stdWrap < lib.fluidContent.stdWrap
tt_content.text.stdWrap < lib.fluidContent.stdWrap
tt_content.textmedia.stdWrap < lib.fluidContent.stdWrap
tt_content.textpic.stdWrap < lib.fluidContent.stdWrap
tt_content.uploads.stdWrap < lib.fluidContent.stdWrap
tt_content.mailform.stdWrap < lib.fluidContent.stdWrap

config.tx_extbase{
    objects {
        TYPO3\CMS\Extbase\Mvc\View\NotFoundView.className = TYPO3\CMS\FrontendEditing\Mvc\View\NotFoundView
    }
}

# prevent links from being parsed to FE url
lib.parseFunc_RTE.tags.a >

[global]
