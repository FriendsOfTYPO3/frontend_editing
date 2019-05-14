<?php
declare(strict_types = 1);

namespace TYPO3\CMS\FrontendEditing\Xclass;

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

use TYPO3\CMS\Backend\Template\Components\Buttons\InputButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\SplitButton;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Xclass TcaButtons to minimize the number of save options
 * when using the frontend editing.
 */
class TcaButtons extends SplitButton
{
    /**
     * Renders the configured SplitButton
     *
     * @return string
     */
    public function render()
    {
        // Only display the minimize the number of save options when feEdit is present
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('feEdit')) {
            $defaultSettings = [
                'buttons' => '_saveandclosedok,_savedok',
            ];
            $settings = [];
            $settings = array_replace($defaultSettings, $settings);

            foreach ($defaultSettings as $name => $_) {
                if (isset($this->getBeUser()->uc[$name])) {
                    $value = $this->getBeUser()->uc[$name];
                    if ($value !== 'default') {
                        $settings[$name] = $value === 'enable';
                    }
                }
            }

            // Get flat array of buttons. Button name is the key of the array.
            $buttonsByName = $this->getButtonsByName();
            // Get flat array of buttons in new order
            $newOrderedButtons = [];
            foreach (explode(',', $settings['buttons']) as $buttonName) {
                $buttonName = trim($buttonName);
                if (isset($buttonsByName[$buttonName])) {
                    $newOrderedButtons[] = $buttonsByName[$buttonName];
                    unset($buttonsByName[$buttonName]);
                }
            }
            // Append remaining buttons
            /** @var InputButton[] $buttons */
            $buttons = $newOrderedButtons;

            // Change button order in configuration
            $this->items = ['primary' => reset($buttons), 'options' => array_slice($buttons, 1, null, true)];
        }

        return parent::render();
    }

    /**
     * Get buttons by name
     *
     * @return InputButton[] name attribute is key of array
     */
    protected function getButtonsByName()
    {
        $items = $this->getButton();
        /** @var InputButton[] $buttons */
        $buttons = array_merge([$items['primary']], $items['options']);
        $buttonsByName = [];
        foreach ($buttons as $button) {
            $key = $button->getName();
            if ($button->getValue() != '1') {
                $key = $button->getValue();
            }
            $buttonsByName[$key] = $button;
        }

        return $buttonsByName;
    }

    /**
     * Get backend user session
     *
     * @return BackendUserAuthentication
     */
    private function getBeUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
