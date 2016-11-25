<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Utility\ContentEditable;

use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\FrontendEditing\Tests\Unit\Fixtures\ContentEditableFixtures;
use TYPO3\CMS\FrontendEditing\Utility\ContentEditable\ContentEditableWrapper;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\Utility\ContentEditableWrapper.
 */
class ContentEditableWrapperTest extends UnitTestCase
{
    /**
     * @var ContentEditableWrapper
     */
    protected $subject = null;

    /**
     * @var ContentEditableFixtures
     */
    protected $fixtures = null;

    /**
     * Set up for the
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = new ContentEditableWrapper();
        $this->fixtures = new ContentEditableFixtures();
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
            $this->fixtures->getTable(),
            $this->fixtures->getField(),
            $this->fixtures->getUid(),
            $this->fixtures->getContent()
        );

        $this->assertSame(
            $wrappedContent,
            $this->fixtures->getWrappedExpectedContent()
        );
    }

    /**
     * @test
     */
    public function getWrapContent()
    {
        $wrapContent = $this->subject->wrapContent(
            $this->fixtures->getTable(),
            $this->fixtures->getUid(),
            $this->fixtures->getDataArr(),
            $this->fixtures->getContent()
        );

        $this->assertSame(
            $wrapContent,
            $this->fixtures->getWrapExpectedContent()
        );
    }

    /**
     * @test
     */
    public function getWrapContentWithDropzone()
    {
        $wrapContent = $this->subject->wrapContentWithDropzone(
            $this->fixtures->getTable(),
            $this->fixtures->getUid(),
            $this->fixtures->getContent()
        );
        $this->assertSame(
            $wrapContent,
            $this->fixtures->getWrapWithDropzoneExpectedContent()
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
                $this->fixtures->getField(),
                $this->fixtures->getUid(),
                $this->fixtures->getContent()
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "table" can not to be empty!');
            return;
        }
        $this->fail('Expected Property "table" missing Exception has not been raised.');
    }

    /**
     * @test
     */
    public function tryWrapContentAndExpectAnExceptionForMissingTable()
    {
        try {
            $wrappedContent = $this->subject->wrapContent(
                '',
                $this->fixtures->getUid(),
                $this->fixtures->getDataArr(),
                $this->fixtures->getContent()
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "table" can not to be empty!');
            return;
        }
        $this->fail('Expected Property "table" missing Exception has not been raised.');
    }

    /**
     * @test
     */
    public function tryWrapContentAndExpectAnExceptionForMissingUid()
    {
        try {
            $wrappedContent = $this->subject->wrapContent(
                $this->fixtures->getTable(),
                '',
                $this->fixtures->getDataArr(),
                $this->fixtures->getContent()
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "uid" can not to be empty!');
            return;
        }
        $this->fail('Expected Property "table" missing Exception has not been raised.');
    }
    /**
     * @test
     */
    public function tryWrapContentWithDropzoneAndExpectAnExceptionForMissingTable()
    {
        try {
            $wrappedContent = $this->subject->wrapContentWithDropzone(
                '',
                $this->fixtures->getUid(),
                $this->fixtures->getContent()
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "table" can not to be empty!');
            return;
        }
        $this->fail('Expected Property "table" missing Exception has not been raised.');
    }

    /**
     * @test
     */
    public function tryWrapContentWithDropzoneAndExpectAnExceptionForMissingUid()
    {
        try {
            $wrappedContent = $this->subject->wrapContentWithDropzone(
                $this->fixtures->getTable(),
                '',
                $this->fixtures->getContent()
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "uid" can not to be empty!');
            return;
        }
        $this->fail('Expected Property "table" missing Exception has not been raised.');
    }
}
