<?php


namespace Opus\Bibtex\Import\Rules;

class PageFirst extends SimpleRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'pages';
        $this->opusFieldName = 'PageFirst';
        $this->fn = function ($value) {
            $value = str_replace(['--', '––', '–'], '-', $value);
            $parts = explode('-', $value, 2);
            return trim($parts[0]);
        };
    }
}