<?php

namespace TYPO3\CMS\FrontendEditing\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Tree\Pagetree\Commands;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;

/**
 * Class PageTreeCrudController
 * @package TYPO3\CMS\FrontendEditing\Controller
 */
class PageTreeCrudController extends ActionController
{
    /**
     * @var JsonView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    /**
     * Resolves and checks the current action method name
     *
     * @return string Method name of the current action
     */
    protected function resolveActionMethodName()
    {
        $actionName = '';
        switch ($this->request->getMethod()) {
            case 'HEAD':
            case 'GET':
                $actionName = 'update';
                break;
            case 'PUT':
                $actionName = 'update';
                break;
            case 'POST':
                $actionName = 'save';
                break;
            case 'DELETE':
                $actionName = 'delete';
                break;
            default:
                $this->throwStatus(400, null, 'Bad Request.');
        }

        return $actionName . 'Action';
    }

    /**
     * Update the label of a page tree node
     *
     * @return array
     * @throws \Exception
     */
    public function updateAction()
    {
        $body = GeneralUtility::_POST();

        // Request is only allowed for POST request and a BE_USER is available
        if (!isset($GLOBALS['BE_USER'])) {
            throw new \Exception('This action is only allowed logged in to the backend!');
        } elseif (empty($body)) {
            $this->throwStatus(
                400,
                null,
                'A body is missing in the request!'
            );
        }

        // Check body data
        if (empty($body['treeNodeIdentifier'])) {
            $this->throwStatus(
                400,
                null,
                'Property "treeNodeIdentifier" is missing from the body!'
            );
        } elseif (empty($body['treeNodeLabel'])) {
            $this->throwStatus(
                400,
                null,
                'Property "treeNodeLabel" is missing from the body!'
            );
        }

        $message = [];
        $treeNodeIdentifier = $body['treeNodeIdentifier'];
        $treeNodeLabel = $body['treeNodeLabel'];

        $treeNode = Commands::getNode($treeNodeIdentifier);
        if ($treeNode !== null) {
            Commands::updateNodeLabel($treeNode, $treeNodeLabel);
            $message = [
                'success' => true,
                'message' => $treeNodeLabel
            ];
        } else {
            $this->throwStatus(
                400,
                null,
                'The page tree node with identifier "' . $treeNodeIdentifier . '" do not exist!'
            );
        }

        return json_encode($message);
    }
}