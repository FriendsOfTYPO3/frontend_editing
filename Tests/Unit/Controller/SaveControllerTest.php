<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Controller;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\FrontendEditing\FrontendEditingController;

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
            \TYPO3\CMS\FrontendEditing\Controller\SaveController::class,
            ['redirect', 'forward', 'addFlashMessage'],
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
}
