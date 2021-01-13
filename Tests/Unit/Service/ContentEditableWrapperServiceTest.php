<?php

declare(strict_types=1);
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
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3\CMS\FrontendEditing\Service\ExtensionManagerConfigurationService;
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
    protected $subject;

    /**
     * @var ContentEditableFixtures
     */
    protected $fixtures;

    /**
     * Set up for the
     */
    protected function setUp()
    {
        parent::setUp();

        $languageServiceMock = $this->createMock(LanguageService::class);

        $languageServiceMock
            ->method('sL')
            ->willReturnArgument(0);

        $GLOBALS['LANG'] = $languageServiceMock;

        $extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);

        $extensionConfigurationMock
            ->method('get')
            ->willReturn([]);

        GeneralUtility::addInstance(ExtensionConfiguration::class, $extensionConfigurationMock);

        $this->subject = new ContentEditableWrapperService();
    }

    protected function tearDown()
    {
        unset($this->subject);
        unset($GLOBALS['TCA']);
    }

    /**
     * Data provider for getWrappedContent()
     *
     * @return array[]
     */
    public function getWrappedContentDataProvider()
    {
        $content = $this->getUniqueId('content');

        return [
            'normal content' => [
                '',
                'tablename',
                'fieldname',
                123,
                $content,
                [
                    'columns' => [
                        'label' => 'Field label'
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider getWrappedContentDataProvider
     * @test
     */
    public function getWrappedContent(
        string $expected,
        string $table,
        string $field,
        int $uid,
        string $content,
        array $tableTca
    )
    {
        $GLOBALS['TCA'][$table] = $tableTca;

        $wrappedContent = $this->subject->wrapContentToBeEditable(
            $table,
            $field,
            $uid,
            $content
        );

        $this->assertSame(
            $wrappedContent,
            $expected
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
        self::fail('Expected Property "table" missing Exception has not been raised.');
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
        self::fail('Expected Property "table" missing Exception has not been raised.');
    }

    /**
     * @test
     */
    public function tryWrapContentAndExpectAnExceptionForMissingUid()
    {
        try {
            $wrappedContent = $this->subject->wrapContent(
                $this->fixtures->getTable(),
                0,
                $this->fixtures->getDataArr(),
                $this->fixtures->getContent()
            );
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Property "uid" can not to be empty!');
            return;
        }
        self::fail('Expected Property "uid" missing Exception has not been raised.');
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
        self::fail('Expected Property "table" missing Exception has not been raised.');
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
        self::fail('Expected Property "uid" missing Exception has not been raised.');
    }
}
