<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

use TYPO3\CMS\FrontendEditing\Controller\SaveController;

/**
 * Hook for cleaning content
 */
class Plaintext implements RequestPreProcessInterface
{

    /**
     * Pre process the request
     *
     * @param array $request save request
     * @param bool $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\SaveController $parentObject
     * @return array
     */
    public function preProcess(array &$request, &$finished, SaveController &$parentObject)
    {
        // Only allowed for "special" field "bodytext-plaintext"
        if ($parentObject->getTable() === 'tt_content' &&
            $parentObject->getField() == 'bodytext-plaintext'
        ) {
            $request['content'] = $this->modifyContent($request['content']);
            $parentObject->setField('bodytext');
        }
        return $request;
    }

    /**
     * Cleanup
     *
     * @param string $content
     * @return string
     */
    private function modifyContent($content)
    {
        // @TODO: Maybe give possibility for fields to have html tags
        $fieldAllowedTags = '';

        $content = trim($content);
        $content = strip_tags(
            urldecode(
                html_entity_decode($content)
            ),
            $fieldAllowedTags
        );

        return $content;
    }
}
