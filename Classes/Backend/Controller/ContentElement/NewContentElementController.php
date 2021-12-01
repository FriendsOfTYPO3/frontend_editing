<?php

declare(strict_types=1);
namespace TYPO3\CMS\FrontendEditing\Backend\Controller\ContentElement;

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

/**
 * Script Class for the New Content element wizard
 * @internal This class is a specific Backend controller implementation
 * and is not considered part of the Public TYPO3 API.
 */
class NewContentElementController extends \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController
{
    /**
     * Returns the array of elements in the wizard display.
     * For the plugin section there is support for adding elements there from a global variable.
     * Done on purpose to expose this method as public
     *
     * @return array
     */
    public function publicGetWizards(): array
    {
        return $this->getWizards();
    }
}
