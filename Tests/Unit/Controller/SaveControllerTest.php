<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Controller;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\FrontendEditing\FrontendEditingController;
use TYPO3\CMS\FrontendEditing\Controller\SaveController;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\Controller\SaveController.
 */
class SaveControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \TYPO3\CMS\FrontendEditing\Controller\SaveController
     */
    protected $subject = null;

    /**
     * Set up for the
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = $this->getMock(
            SaveController::class,
            ['save'],
            [],
            '',
            true
        );
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
    public function getPropertiesInitializedInConstructor() {
        $this->assertSame(
            get_class($this->subject->getDataHandler()),
            DataHandler::class
        );
        $this->assertSame(
            get_class($this->subject->getFrontendEditingController()),
            FrontendEditingController::class
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
    public function trySavingContentAndExpectAnException() {
        try {
            $this->subject->saveAction();
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'A table name is missing, no possibility to save the data!');
            return;
        }
        $this->fail('Expected Exception has not been raised.');
    }
}
