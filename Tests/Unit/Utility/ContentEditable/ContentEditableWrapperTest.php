<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Utility\ContentEditable;

use TYPO3\CMS\FrontendEditing\Utility\ContentEditable\ContentEditableWrapper;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\Utility\ContentEditableWrapper.
 */
class ContentEditableWrapperTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \TYPO3\CMS\FrontendEditing\Utility\ContentEditable\ContentEditableWrapper
     */
    protected $subject = null;

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
     * Set up for the
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = new ContentEditableWrapper();
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getWrappedContent()
    {
        $wrappedContent = $this->subject->wrapContentToBeEditable(
            $this->table,
            $this->field,
            $this->uid,
            $this->content
        );

        $expectedOutput = sprintf(
            '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
            $this->table,
            $this->field,
            $this->uid,
            $this->content
        );

        $this->assertSame(
            $wrappedContent,
            $expectedOutput
        );
    }

    /**
     * @test
     */
    public function tryWrapContentAndExpectAnException()
    {
        try {
            $wrappedContent = $this->subject->wrapContentToBeEditable(
                '',
                $this->field,
                $this->uid,
                $this->content
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "table" can not to be empty!');
            return;
        }
        $this->fail('Expected Property "table" missing Exception has not been raised.');
    }
}
