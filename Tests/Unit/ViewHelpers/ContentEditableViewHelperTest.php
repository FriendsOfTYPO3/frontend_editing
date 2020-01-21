<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\ViewHelpers;

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

use Nimut\TestingFramework\Rendering\RenderingContextFixture;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\FrontendEditing\Tests\Unit\Fixtures\ContentEditableFixtures;

use TYPO3\CMS\FrontendEditing\ViewHelpers\ContentEditableViewHelper;

/**
 * Test case for TYPO3\CMS\FrontendEditing\ViewHelpers\EditableViewHelper
 *
 * @extensionScannerIgnoreFile
 */
class ContentEditableViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments()
    {
        $instance = $this->getMock(ContentEditableViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with(
            'table',
            'string',
            $this->anything(),
            true,
            false
        );
        $instance->expects($this->at(1))->method('registerArgument')->with(
            'field',
            'string',
            $this->anything(),
            false,
            false
        );
        $instance->expects($this->at(2))->method('registerArgument')->with(
            'uid',
            'string',
            $this->anything(),
            true,
            false
        );
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @dataProvider getRenderTestValuesWithoutFrontendEditionEnabled
     * @param mixed $value
     * @param array $arguments
     * @param string $expected
     */
    public function testRenderWithoutFrontendEditingEnabled($value, array $arguments, $expected)
    {
        $instance = $this->getMock(ContentEditableViewHelper::class, ['renderChildren']);
        $instance->expects($this->once())->method('renderChildren')->willReturn($value);
        $instance->setArguments($arguments);
        $instance->setRenderingContext(new RenderingContextFixture());
        $result = $instance->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getRenderTestValuesWithFrontendEditionEnabled
     * @param mixed $value
     * @param array $arguments
     * @param string $expected
     * @throws \Exception
     */
    public function testRenderWithFrontendEditingEnabled($value, array $arguments, $expected)
    {
        // Simulate BackendUserAuthentication object
        $GLOBALS['BE_USER'] = $this->getMock(
            BackendUserAuthentication::class,
            [],
            [],
            '',
            false
        );
        ContentEditableFixtures::setAccessServiceEnabled(true);
        $instance = $this->getMock(ContentEditableViewHelper::class, ['renderChildren']);
        $instance->expects($this->once())->method('renderChildren')->willReturn($value);
        $instance->setArguments($arguments);
        $instance->setRenderingContext(new RenderingContextFixture());
        $result = $instance->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderTestValuesWithoutFrontendEditionEnabled()
    {
        $fixtures = new ContentEditableFixtures();

        return [
            [
                $fixtures->getWrappedExpectedContent(),
                [
                    'table' => $fixtures->getTable(),
                    'field' => $fixtures->getField(),
                    'uid' => $fixtures->getUid()
                ],
                $fixtures->getWrappedExpectedContent()
            ]
        ];
    }

    /**
     * @return array
     */
    public function getRenderTestValuesWithFrontendEditionEnabled()
    {
        $fixtures = new ContentEditableFixtures();

        return [
            [
                'This is my content',
                [
                    'table' => $fixtures->getTable(),
                    'field' => $fixtures->getField(),
                    'uid' => $fixtures->getUid()
                ],
                $fixtures->getWrappedExpectedContent()
            ]
        ];
    }
}
