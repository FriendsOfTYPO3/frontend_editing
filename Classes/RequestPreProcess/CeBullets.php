<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\RequestPreProcess;

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
 * Hook for saving content element "bullets"
 *
 * Thanks for to Georg Ringer (mail@ringer.it) for the initial work.
 */
class CeBullets implements RequestPreProcessInterface
{
    /**
     * Pre process the content
     *
     * @param string $table
     * @param array $record
     * @param string $fieldName
     * @param string $content
     * @param bool $isFinished
     * @return string the modified content
     */
    public function preProcess(
        string $table,
        array $record,
        string &$fieldName,
        string $content,
        bool &$isFinished
    ): string {
        // Only allowed for bullet element
        if ($table === 'tt_content' && $fieldName === 'bodytext' && $record['CType'] === 'bullets') {
            $isFinished = true;
            $domDocument = new \DOMDocument();
            $domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $content);

            $liCollection = $domDocument->getElementsByTagName('li');
            $tempLiElements = [];
            foreach ($liCollection as $class) {
                $value = trim($class->nodeValue);
                if (!empty($value)) {
                    $tempLiElements[] = $value;
                }
            }

            $content = implode(PHP_EOL, $tempLiElements);
        }
        return $content;
    }
}
