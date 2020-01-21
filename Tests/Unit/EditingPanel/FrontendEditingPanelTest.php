<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\EditingPanel;

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
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Tests\Unit\ContentObject\Fixtures\PageRepositoryFixture;
use TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingPanel;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingPanel.
 *
 * @extensionScannerIgnoreFile
 */
class FrontendEditingPanelTest extends UnitTestCase
{

    /**
     * @var TemplateService
     */
    protected $templateServiceMock;

    /**
     * @var TypoScriptFrontendController
     */
    protected $frontendControllerMock;

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
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);

        $router = GeneralUtility::makeInstance(Router::class);
        $router->addRoute('record_edit', new Route('record_edit', []));

        $contentEditableWrapperService = new ContentEditableWrapperService();

        $content = $this->getUniqueId('content');
        return [
            'standard case call edit icons for tt_content:bodytext' => [
                $contentEditableWrapperService->wrapContentWithDropzone(
                    'tt_content',
                    1,
                    $contentEditableWrapperService->wrapContent(
                        'tt_content',
                        1,
                        [],
                        $content
                    )
                ),
                $contentEditableWrapperService->wrapContentWithDropzone(
                    'tt_content',
                    1,
                    $contentEditableWrapperService->wrapContent(
                        'tt_content',
                        1,
                        [],
                        $content
                    )
                ),
                'tt_content:bodytext',
                ['beforeLastTag' => '1', 'allow' => 'edit'],
                'tt_content:1',
                ['uid' => 1, 'pid' => 1, 'CType' => 'text', 'bodytext' => $content],
                '',
                'tt_content',
                '1',
                'bodytext',
                1,
                1
            ],
            'another case with fe_users:email' => [
                $contentEditableWrapperService->wrapContentWithDropzone(
                    'fe_users',
                    12,
                    $contentEditableWrapperService->wrapContent(
                        'fe_users',
                        12,
                        [],
                        '<div contenteditable="true" data-table="fe_users" data-field="email" data-uid="12" class="">'
                            . $content . '</div>'
                    )
                ),
                $contentEditableWrapperService->wrapContentWithDropzone(
                    'fe_users',
                    12,
                    $contentEditableWrapperService->wrapContent(
                        'fe_users',
                        12,
                        [],
                        '<div contenteditable="true" data-table="fe_users" data-field="email" data-uid="12" class="">'
                        . $content . '</div>'
                    )
                ),
                'fe_users:email',
                ['beforeLastTag' => '1', 'allow' => 'edit'],
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
                $contentEditableWrapperService->wrapContentWithDropzone(
                    'tt_content',
                    12,
                    $contentEditableWrapperService->wrapContent(
                        'tt_content',
                        12,
                        [],
                        $content
                    )
                ),
                $contentEditableWrapperService->wrapContentWithDropzone(
                    'tt_content',
                    12,
                    $contentEditableWrapperService->wrapContent(
                        'tt_content',
                        12,
                        [],
                        $content
                    )
                ),
                'tt_content:header',
                ['beforeLastTag' => '1', 'allow' => 'edit'],
                'tt_content:12',
                ['uid' => 12, 'pid' => 1, 'CType' => 'text', 'header' => $content],
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
                ['beforeLastTag' => '1', 'allow' => 'edit'],
                'tt_content:1',
                ['uid' => 1, 'pid' => 1, 'CType' => 'text', 'bodytext' => $content],
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
                ['beforeLastTag' => '1', 'allow' => 'edit'],
                'tt_content:1',
                ['uid' => 1, 'pid' => 1, 'CType' => 'text', 'bodytext' => $content],
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
