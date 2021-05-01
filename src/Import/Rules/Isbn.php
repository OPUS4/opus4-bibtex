<?php


namespace Opus\Bibtex\Import\Rules;


class Isbn extends ArrayRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'isbn';
        $this->opusFieldName = 'Identifier';
        $this->fn = function ($value) {
            return [
                'Value' => $value,
                'Type' => 'isbn'
            ];
        };
    }
}