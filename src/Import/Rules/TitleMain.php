<?php


namespace Opus\Bibtex\Import\Rules;


class TitleMain extends ArrayRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'title';
        $this->opusFieldName = 'TitleMain';
        $this->fn = function ($value) {
            return [
                'Language' => 'eng',
                'Value' => $this->deleteBrace($value),
                'Type' => 'main'
            ];
        };
    }
}