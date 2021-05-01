<?php


namespace Opus\Bibtex\Import\Rules;

class PublishedYear extends SimpleRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'year';
        $this->opusFieldName = 'PublishedYear';
        $this->fn = function ($value) {
            $value = preg_replace('/[^0-9]/', '', $value);
            if (strlen($value) == 4) {
                return $value;
            }
        };
    }
}