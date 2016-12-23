<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Fixtures;

use TYPO3\CMS\FrontendEditing\Utility\ContentEditable\ContentEditableWrapper;

/**
 * Fixtures for ContentEditableProperties
 */
class ContentEditableFixtures
{

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
    protected $dataArr = [
        'uid' => 1,
        'pid' => 37,
        'colPos' => 0,
        'title' => 'Test title'
    ];

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
            '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
            $this->table,
            $this->field,
            $this->uid,
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
            '<div class="t3-frontend-editing__ce" title="%s">' .
                '<span class="%s" data-table="%s" data-uid="%s" data-cid="%s" data-edit-url="%s">%s</span>' .
                '%s' .
            '</div>',
            $this->uid,
            $class,
            $this->table,
            $this->uid,
            $this->dataArr['colPos'],
            ContentEditableWrapper::renderEditOnClickReturnUrl(
                ContentEditableWrapper::renderEditUrl(
                    $this->table,
                    $this->uid
                )
            ),
            ContentEditableWrapper::renderInlineActionIcons(),
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
        $jsFuncOnDrop = 'window.parent.F.dropNewCe(event)';
        $jsFuncOnDragover = 'window.parent.F.dragNewCeOver(event)';
        $jsFuncOnDragLeave = 'window.parent.F.dragNewCeLeave(event)';
        $class = 't3-frontend-editing__dropzone';

        $expectedOutput = sprintf(
            '%s' .
            '<div class="%s" ondrop="%s" ondragover="%s" ondragleave="%s" data-new-url="%s"></div>',
            $this->content,
            $class,
            $jsFuncOnDrop,
            $jsFuncOnDragover,
            $jsFuncOnDragLeave,
            ContentEditableWrapper::renderNewUrl($this->table, $this->uid)
        );

        return $expectedOutput;
    }
}
