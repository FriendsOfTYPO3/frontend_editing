<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Fixtures;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;

/**
 * Fixtures for ContentEditableProperties
 *
 * @extensionScannerIgnoreFile
 */
class ContentEditableFixtures
{

    /**
     * @var ContentEditableWrapperService
     */
    protected $contentEditableWrapperService;

    /**
     * @var string
     */
    protected $table = 'tt_content';

    /**
     * @var string
     */
    protected $field = 'bodytext';

    /**
     * @var string
     */
    protected $uid = 1;

    /**
     * @var string
     */
    protected $content = 'This is my content';

    /**
     * @var array
     */
    protected $customTables = [
        'tx_news_domain_model_model1',
        'tx_news_domain_model_model2'
    ];

    /**
     * @var array
     */
    protected $dataArr = [
        'uid' => 1,
        'pid' => 37,
        'colPos' => 0,
        'title' => 'Test title'
    ];

    /**
     * ContentEditableFixtures constructor
     */
    public function __construct()
    {
        $this->contentEditableWrapperService = new ContentEditableWrapperService();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getCustomTables()
    {
        return $this->customTables;
    }

    /**
     * @return array
     */
    public function getDataArr()
    {
        return $this->dataArr;
    }

    /**
     * A public getter for getting the correct expected wrapping
     *
     * @return string
     */
    public function getWrappedExpectedContent()
    {
        $expectedOutput = sprintf(
            '<div '
            . 'contenteditable="true" '
            . 'data-table="%s" '
            . 'data-field="%s" '
            . 'data-uid="%d" '
            . 'class="%s" '
            . 'placeholder="Enter text here"'
            . '>%s</div>',
            $this->table,
            $this->field,
            $this->uid,
            $this->contentEditableWrapperService->checkIfContentElementIsHidden($this->table, $this->uid),
            $this->content
        );

        return $expectedOutput;
    }

    /**
     * A public getter for getting the correct expected wrapping
     *
     * @return string
     */
    public function getWrapExpectedContent()
    {
        $class = 't3-frontend-editing__inline-actions';
        $expectedOutput = sprintf(
            '<div class="t3-frontend-editing__ce %s" title="%s" data-movable="1"' .
                ' ondragstart="window.parent.F.dragCeStart(event)"' .
                ' ondragend="window.parent.F.dragCeEnd(event)">' .
                '<span style="display:none;" class="%s" data-table="%s" data-uid="%d" data-hidden="%s"' .
                ' data-cid="%d" data-edit-url="%s" data-new-url="%s">%s</span>' .
                '%s' .
            '</div>',
            $this->contentEditableWrapperService->checkIfContentElementIsHidden($this->table, $this->uid),
            $this->contentEditableWrapperService->recordTitle($this->table, $this->dataArr),
            $class,
            $this->table,
            $this->uid,
            0,
            $this->dataArr['colPos'],
            $this->contentEditableWrapperService->renderEditOnClickReturnUrl(
                $this->contentEditableWrapperService->renderEditUrl(
                    $this->table,
                    $this->uid
                )
            ),
            $this->contentEditableWrapperService->renderEditOnClickReturnUrl(
                $this->contentEditableWrapperService->renderNewUrl(
                    $this->table,
                    $this->uid
                )
            ),
            $this->contentEditableWrapperService->renderInlineActionIcons($this->table, false),
            $this->content
        );

        return $expectedOutput;
    }

    /**
     * A public getter for getting the correct expected wrapping
     *
     * @return string
     */
    public function getWrapWithDropzoneExpectedContent()
    {
        $jsFuncOnDrop = 'window.parent.F.dropCe(event)';
        $jsFuncOnDragover = 'window.parent.F.dragCeOver(event)';
        $jsFuncOnDragLeave = 'window.parent.F.dragCeLeave(event)';
        $class = 't3-frontend-editing__dropzone';

        $colPos = 0;
        $defaultValues = [];

        $expectedOutput = sprintf(
            '%s' .
            '<div class="%s" ondrop="%s" ondragover="%s" ondragleave="%s" data-new-url="%s" ' .
                'data-moveafter="%d" data-colpos="%d" data-defvals="%s"></div>',
            $this->content,
            $class,
            $jsFuncOnDrop,
            $jsFuncOnDragover,
            $jsFuncOnDragLeave,
            $this->contentEditableWrapperService->renderEditOnClickReturnUrl(
                $this->contentEditableWrapperService->renderNewUrl($this->table, $this->uid)
            ),
            $this->uid,
            $colPos,
            htmlspecialchars(json_encode($defaultValues))
        );

        return $expectedOutput;
    }

    /**
     * A public getter for getting the correct expected wrapping for custom dropzone
     *
     * @param string $content
     *
     * @return string
     */
    public function getWrapWithCustomDropzoneExpectedContent(string $content = '')
    {
        $jsFuncOnDrop = 'window.parent.F.dropCr(event)';
        $jsFuncOnDragover = 'window.parent.F.dragCeOver(event)';
        $jsFuncOnDragLeave = 'window.parent.F.dragCeLeave(event)';
        $class = 't3-frontend-editing__dropzone';

        $tables = implode(',', $this->customTables);
        $defaultValues = [];
        $pageUid = 0;

        $expectedOutput = sprintf(
            '<div class="%s" ondrop="%s" ondragover="%s" ondragleave="%s" ' .
            'data-tables="%s" data-defvals="%s" data-pid="%s"></div>',
            $class,
            $jsFuncOnDrop,
            $jsFuncOnDragover,
            $jsFuncOnDragLeave,
            $tables,
            htmlspecialchars(json_encode($defaultValues)),
            $pageUid
        );

        return $content . $expectedOutput;
    }

    /**
     * @param bool $enabled
     * @throws \Exception
     */
    public static function setAccessServiceEnabled(bool $enabled)
    {
        $access = GeneralUtility::makeInstance(AccessService::class);
        $reflection = new \ReflectionClass($access);
        $reflectionProperty = $reflection->getProperty('isEnabled');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($access, $enabled);
    }
}
