<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Html;

use TYPO3\CMS\Core\Html\DefaultSanitizerBuilder;
use TYPO3\HtmlSanitizer\Behavior\Attr;
use TYPO3\HtmlSanitizer\Builder\BuilderInterface;

/**
 * Sanitizer that adds additional global attributes used in Frontend Editing.
 */
class FrontendEditingSanitizerBuilder extends DefaultSanitizerBuilder implements BuilderInterface
{
    /**
     * @inheritDoc
     */
    protected function createGlobalAttrs(): array
    {
        $attrs = parent::createGlobalAttrs();

        $attrs[] = new Attr('contenteditable');
        $attrs[] = new Attr('placeholder');

        return $attrs;
    }
}
