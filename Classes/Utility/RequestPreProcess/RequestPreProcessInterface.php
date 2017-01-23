<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

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

use TYPO3\CMS\FrontendEditing\Controller\CrudController;

/**
 * Interface RequestPreProcessInterface
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface RequestPreProcessInterface
{
    /**
     * Pre process the request
     *
     * @param array $body save body
     * @param bool $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\CrudController $parentObject
     * @return array
     */
    public function preProcess(
        array &$body,
        &$finished,
        CrudController &$parentObject
    );
}
