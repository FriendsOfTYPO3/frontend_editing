<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Controller;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\FrontendEditing\Controller\CrudController;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\Controller\CrudController.
 */
class CrudControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \TYPO3\CMS\FrontendEditing\Controller\CrudController
     */
    protected $subject = null;

    /**
     * Set up for the
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = $this->getMockBuilder(CrudController::class)
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
    public function getPropertiesInitializedInConstructor()
    {
        $this->assertSame(
            get_class($this->subject->getDataHandler()),
            DataHandler::class
        );
    }

    /**
     * @test
     */
    public function getTableName()
    {
        $this->assertSame(
            $this->subject->getTable(),
            null
        );
    }

    /**
     * @test
     */
    public function tryWrapContentAndExpectAnException()
    {
        try {
            $this->subject->updateAction();
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'throwStatus() only supports web requests.');
            return;
        }
        $this->fail('Expected Exception has not been raised.');
    }
}
