<?php


namespace Opus\Bibtex\Import\Rules;


class Note extends ArrayRule
{
    private $messagePrefix = '';

    private $visibility = 'public';

    public function __construct()
    {
        return parent::__construct(
            'xxx', // FIXME wird durch setBibtexFieldName gesetzt
            'Note',
            function ($value) {
                return [
                    'Visibility' => $this->visibility,
                    'Message' => $this->messagePrefix . $value
                ];
            }
        );
    }

    public function setMessagePrefix($messagePrefix)
    {
        $this->messagePrefix = $messagePrefix;
        return $this;
    }

    public function setVisibility($visibility)
    {
        // FIXME übergebenen Wert validieren
        $this->visibility = $visibility;
        return $this;
    }
}