<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Controller;

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
            false
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
    public function getTableValue()
    {
        $this->assertSame(
            $this->subject->getTable(),
            null
        );
    }
}
