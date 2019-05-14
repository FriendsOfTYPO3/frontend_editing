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
 * Hook for saving content element "header"
 *
 * Thanks for to Georg Ringer (mail@ringer.it) for the initial work.
 */
class CeHeader implements RequestPreProcessInterface
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
        // Only allowed for field header
        if ($table === 'tt_content' && ($fieldName === 'header' || $fieldName === 'subheader')
        ) {
            // Check if we need to update header-type field
            if (strpos($content, '<h') === 0) {
                $headerTypeRequest = null;

                switch (substr($content, 0, 3)) {
                    case '<h1':
                        $headerTypeRequest['content'] = 1;
                        break;
                    case '<h2':
                        $headerTypeRequest['content'] = 2;
                        break;
                    case '<h3':
                        $headerTypeRequest['content'] = 3;
                        break;
                    case '<h4':
                        $headerTypeRequest['content'] = 4;
                        break;
                    case '<h5':
                        $headerTypeRequest['content'] = 5;
                        break;
                    case '<h6':
                        $headerTypeRequest['content'] = 6;
                        break;
                    default:
                        $headerTypeRequest['content'] = 0;
                }

                $fieldName = 'header';
            }

            // Remove tags so we only have the plain text.
            $content = urldecode(strip_tags($content));
        }

        return $content;
    }
}
