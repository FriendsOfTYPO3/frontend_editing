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
     * A public getter for getting the correct expected wrapping
     *
     * @return string
     */
    public function getWrappedExpectedContent()
    {
        $expectedOutput = sprintf(
            '<span class="t3-frontend-editing__inline-actions">%s</span>' .
                '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
            ContentEditableWrapper::renderInlineActionIcons(),
            $this->table,
            $this->field,
            $this->uid,
            $this->content
        );

        return $expectedOutput;
    }
}
