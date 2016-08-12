<?php

namespace TYPO3\CMS\FrontendEditing\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class SaveController extends ActionController
{
    /**
     * @var \TYPO3\CMS\Extbase\Mvc\View\JsonView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = \TYPO3\CMS\Extbase\Mvc\View\JsonView::class;

    /**
     * Resolves and checks the current action method name
     *
     * @return string Method name of the current action
     */
    protected function resolveActionMethodName()
    {
        var_dump('Resolver111');die;
        switch ($this->request->getMethod()) {
            case 'HEAD':
            case 'GET':
                $actionName = 'save'; //($this->request->hasArgument('tag')) ? 'show' : 'list';
                break;
            case 'POST':
            case 'PUT':
            case 'DELETE':
            default:
                $this->throwStatus(400, null, 'Bad Request.');
        }

        return $actionName . 'Action';
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        var_dump('asdasd');die;
        //$this->view->assign('tags', $this->tagRepository->findAll());
    }
}
