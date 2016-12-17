<?php
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\EditingPanel;

use TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingPanel;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Tests\Unit\ContentObject\Fixtures\PageRepositoryFixture;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingPanel.
 */
class FrontendEditingPanelTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->templateServiceMock =
            $this->getMockBuilder(TemplateService::class)
            ->setMethods(['getFileName', 'linkData'])->getMock();
        $pageRepositoryMock =
            $this->getMockBuilder(PageRepositoryFixture::class)
            ->setMethods(['getRawRecord', 'getMountPointInfo'])->getMock();

        $this->frontendControllerMock = $this->getAccessibleMock(
            TypoScriptFrontendController::class,
            ['dummy'],
            [],
            '',
            false
        );
        $this->frontendControllerMock->tmpl = $this->templateServiceMock;
        $this->frontendControllerMock->config = [];
        $this->frontendControllerMock->page =  [];
        $this->frontendControllerMock->sys_page = $pageRepositoryMock;
        $GLOBALS['TSFE'] = $this->frontendControllerMock;
    }

     /**
     * Data provider for editIcons.
     *
     * @return array []
     */
    public function editIconsDataProvider()
    {
        $content = $this->getUniqueId('content');
        return [
            'standard case call edit icons for tt_content:bodytext' => [
                '<div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">' .
                $content . '</div>',
                $content,
                'tt_content:bodytext',
                ['beforeLastTag' => '1','allow' => 'edit'],
                'tt_content:1',
                ['uid' => 1, 'pid' => 1,'CType' => 'text', 'bodytext' => $content],
                '',
                'tt_content',
                '1',
                'bodytext',
                1,
                1
            ],
            'another case with fe_users:email' => [
                '<div contenteditable="true" data-table="fe_users" data-field="email" data-uid="12">' .
                $content . '</div>',
                $content,
                'fe_users:email',
                ['beforeLastTag' => '1','allow' => 'edit'],
                'fe_users:12',
                ['uid' => 12, 'pid' => 1, 'email' => $content],
                '',
                'fe_users',
                '12',
                'email',
                1,
                1
            ],
            'another case with tt_content:header' => [
                '<div contenteditable="true" data-table="tt_content" data-field="header" data-uid="12">' .
                $content . '</div>',
                $content,
                'tt_content:header',
                ['beforeLastTag' => '1','allow' => 'edit'],
                'tt_content:12',
                ['uid' => 12, 'pid' => 1,'CType' => 'text', 'header' => $content],
                '',
                'tt_content',
                '12',
                'header',
                1,
                1
            ],
            'frontend editing not enabled' => [
                $content,
                $content,
                'tt_content:bodytext',
                ['beforeLastTag' => '1','allow' => 'edit'],
                'tt_content:1',
                ['uid' => 1, 'pid' => 1,'CType' => 'text', 'bodytext' => $content],
                '',
                'tt_content',
                '1',
                'bodytext',
                0,
                1
            ],
            'user does not have frontend editing enabled' => [
                $content,
                $content,
                'tt_content:bodytext',
                ['beforeLastTag' => '1','allow' => 'edit'],
                'tt_content:1',
                ['uid' => 1, 'pid' => 1,'CType' => 'text', 'bodytext' => $content],
                '',
                'tt_content',
                '1',
                'bodytext',
                1,
                0
            ],
        ];
    }

    /**
     * Check if editIcons works properly.
     *
     * Show:
     *
     * - Returns $content as is if:
     *   - beUserLogin is not set
     *   - (bool)$conf['editIcons'] is false
     * - Otherwise:
     *   - Delegates to method editIcons.
     *   - Parameter 1 is $content.
     *   - Parameter 2 is $conf['editIcons'].
     *   - Parameter 3 is $conf['editIcons.'].
     *   - If $conf['editIcons.'] is no array at all, the empty array is used.
     *   - Returns the return value.
     *
     * @test
     * @dataProvider editIconsDataProvider
     * @param string $expect The expected output.
     * @param string $content The given content.
     * @param array $conf The given configuration.
     * @param bool $login Simulate backend user login.
     * @param int $times Times editIcons is called (0 or 1).
     * @param array $param3 The expected third parameter.
     * @param string $will Return value of editIcons.
     * @return void
     */
    public function editIcons(
        $expect,
        $content,
        $params,
        $conf,
        $currentRecord,
        $dataArr,
        $addUrlParamStr,
        $table,
        $editUid,
        $fieldList,
        $enableEditing,
        $allowEditing
    ) {
        // TODO: this seems dirty to me. Is there some better example in TYPO3 core?
        $GLOBALS['BE_USER'] = new FrontendBackendUserAuthentication();
        $GLOBALS['TSFE']->config['config']['tx_frontend_editing'] = $enableEditing;
        $GLOBALS['BE_USER']->uc['tx_frontend_editing_enable'] = $allowEditing;
        $subject = new FrontendEditingPanel();

        $this->assertSame(
            $expect,
            $subject->editIcons(
                $content,
                $params,
                $conf,
                $currentRecord,
                $dataArr,
                $addUrlParamStr,
                $table,
                $editUid,
                $fieldList
            )
        );
    }
}
