<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Controller;

use TYPO3\CMS\FrontendEditing\Controller\PageTreeCrudController;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\Controller\PageTreeCrudController.
 */
class PageTreeCrudControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \TYPO3\CMS\FrontendEditing\Controller\PageTreeCrudController
     */
    protected $subject = null;

    /**
     * Set up for the
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = $this->getMockBuilder(PageTreeCrudController::class)
            ->setMethods(['update'])->getMock();
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
    public function tryUpdatePageTreeNodeLabelWrapContentAndExpectAnException()
    {
        try {
            $this->subject->updateAction();
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'This action is only allowed logged in to the backend!');
            return;
        }
        $this->fail('Expected Exception has not been raised.');
    }
}
