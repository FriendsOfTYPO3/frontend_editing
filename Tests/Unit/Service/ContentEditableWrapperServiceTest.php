<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Service\ContentEditable;

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

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3\CMS\FrontendEditing\Tests\Unit\Fixtures\ContentEditableFixtures;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService.
 *
 * @extensionScannerIgnoreFile
 */
class ContentEditableWrapperServiceTest extends UnitTestCase
{
    /**
     * @var ContentEditableWrapperService
     */
    protected $subject = null;

    /**
     * @var ContentEditableFixtures
     */
    protected $fixtures = null;

    /**
     * Set up for the
     */
    protected function setUp()
    {
        Bootstrap::getInstance()->initializeBackendRouter();
        $this->subject = new ContentEditableWrapperService();
        $this->fixtures = new ContentEditableFixtures();
    }

    /**
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
    public function tryWrapContentWithMissingUidAndExceptGetUnwrappedContent()
    {
        $wrappedContent = $this->subject->wrapContent(
            $this->fixtures->getTable(),
            0,
            $this->fixtures->getDataArr(),
            $this->fixtures->getContent()
        );

        $this->assertEquals($this->fixtures->getContent(), $wrappedContent);
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
                -1,
                $this->fixtures->getContent()
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "uid" is not valid!');
            return;
        }
        $this->fail('Expected Property "uid" missing Exception has not been raised.');
    }
}
