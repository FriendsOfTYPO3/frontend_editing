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

use TYPO3\CMS\FrontendEditing\ViewHelpers\SvgViewHelper;

/**
 * Test case for TYPO3\CMS\FrontendEditing\ViewHelpers\SvgViewHelper
 *
 * @extensionScannerIgnoreFile
 */
class SvgViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments()
    {
        $instance = $this->getMock(SvgViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with(
            'source',
            'string',
            $this->anything(),
            true,
            false
        );
        $instance->expects($this->at(1))->method('registerArgument')->with(
            'class',
            'string',
            $this->anything(),
            false,
            false
        );
        $instance->expects($this->at(2))->method('registerArgument')->with(
            'width',
            'int',
            $this->anything(),
            false,
            false
        );
        $instance->expects($this->at(3))->method('registerArgument')->with(
            'height',
            'int',
            $this->anything(),
            false,
            false
        );
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @dataProvider getRenderTestValuesForWrongPathToIcon
     * @param mixed $value
     * @param array $arguments
     * @param string $expected
     */
    public function testRenderWithWrongPathToIcon($value, array $arguments, $expected)
    {
        $instance = $this->getMock(SvgViewHelper::class, ['renderChildren']);
        $instance->setArguments($arguments);
        $instance->setRenderingContext(new RenderingContextFixture());
        $result = $instance->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderTestValuesForWrongPathToIcon()
    {
        return [
            [
                'no SVG file on /Icons/visible_icon.svg',
                [
                    'source' => 'Icons/visible_icon.svg',
                    'class' => 'hidden-elements-toggle-icon',
                    'width' => 40,
                    'height' => 40
                ],
                'no SVG file on /Icons/visible_icon.svg'
            ]
        ];
    }
}
