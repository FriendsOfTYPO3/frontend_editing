<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Backend\Form\FormDataGroup;

use TYPO3\CMS\Backend\Form\FormDataGroup\OrderedProviderList;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendTcaDatabaseRecord implements \TYPO3\CMS\Backend\Form\FormDataGroupInterface
{
    /**
     * Compile form data
     *
     * @param array $result Initialized result array
     * @return array Result filled with data
     * @throws \UnexpectedValueException
     */
    public function compile(array $result)
    {
        $orderedProviderList = GeneralUtility::makeInstance(OrderedProviderList::class);
        $orderedProviderList->setProviderList(
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['frontendTcaDatabaseRecord']
        );

        return $orderedProviderList->compile($result);
    }
}
