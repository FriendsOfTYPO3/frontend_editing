<?php
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

use TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\FrontendEditing\ViewHelpers\EditableViewHelper;
use TYPO3\CMS\FrontendEditing\Tests\Unit\Fixtures\ContentEditableFixtures;

/**
 * Test case for TYPO3\CMS\FrontendEditing\ViewHelpers\EditableViewHelper
 */
class EditableViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var EditableViewHelper
     */
    protected $viewHelper;

    /**
     * @var ContentEditableFixtures
     */
    protected $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fixtures = new ContentEditableFixtures();
        $this->viewHelper = $this->getAccessibleMock(
            EditableViewHelper::class,
            ['renderChildren']
        );
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function renderTagStructureCorrectly()
    {
        $mockTagBuilder = $this->getMockBuilder(TagBuilder::class)
            ->setMethods(['setTagName'])
            ->getMock();
        $mockTagBuilder->expects($this->once())->method('setTagName')->with('');
        $this->viewHelper->_set('tag', $mockTagBuilder);

        $this->viewHelper->expects($this->once())
            ->method('renderChildren')->will(
                $this->returnValue(
                    $this->fixtures->getWrappedExpectedContent()
                )
            );

        $this->viewHelper->initialize();
        $this->viewHelper->render(
            $this->fixtures->getTable(),
            $this->fixtures->getField(),
            $this->fixtures->getUid()
        );
    }

    /**
     * @test
     */
    public function viewHelperRendersWithTheCorrectEditableWrapping()
    {
        $this->viewHelper = new EditableViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $this->viewHelper->setRenderChildrenClosure(
            function () {
                return $this->fixtures->getContent();
            }
        );

        $this->setArgumentsUnderTest(
            $this->viewHelper,
            [
                'table' => $this->fixtures->getTable(),
                'field' => $this->fixtures->getField(),
                'uid' => $this->fixtures->getUid(),
                'disableAccessCheck' => true
            ]
        );

        $actualResult = $this->viewHelper->initializeArgumentsAndRender();

        $this->assertEquals(
            $this->fixtures->getWrappedExpectedContent(),
            $actualResult
        );
    }
}
