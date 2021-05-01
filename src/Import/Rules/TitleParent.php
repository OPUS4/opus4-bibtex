<?php


namespace Opus\Bibtex\Import\Rules;


class TitleParent extends ArrayRule
{
    public function __construct()
    {
        return parent::__construct(
            'journal',
            'TitleParent',
            function ($value) {
                return [
                    'Language' => 'eng',
                    'Value' => $this->deleteBrace($value),
                    'Type' => 'parent'
                ];
            }
        );
    }
}