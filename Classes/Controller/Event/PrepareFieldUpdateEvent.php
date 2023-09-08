<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Controller\Event;

/**
 * Event dispatched before a field is updated with new content.
 */
class PrepareFieldUpdateEvent
{
    /**
     * @var string
     */
    protected string $table;

    /**
     * @var array
     */
    protected array $record;

    /**
     * @var string
     */
    protected string $field;

    /**
     * @var string
     */
    protected string $content;

    /**
     * @param string $table The table being updated
     * @param string $field The name of the field being updated
     * @param string $content The updated content of the field
     * @param array $record The database record before this update is applied
     */
    public function __construct(string $table, string $field, string $content, array $record)
    {
        $this->table = $table;
        $this->field = $field;
        $this->content = $content;
        $this->record = $record;
    }

    /**
     * Returns the table being updated.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Returns the updated content of the field.
     *
     * @return array
     */
    public function getRecord(): array
    {
        return $this->record;
    }

    /**
     * Returns the name of the field being updated.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Returns the updated content of the field.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
