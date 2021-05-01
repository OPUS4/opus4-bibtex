<?php


namespace Opus\Bibtex\Import\Rules;


class Issn extends ArrayRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'issn';
        $this->opusFieldName = 'Identifier';
        $this->fn = function ($value) {
            return [
                'Value' => $value,
                'Type' => 'issn'
            ];
        };
    }

}