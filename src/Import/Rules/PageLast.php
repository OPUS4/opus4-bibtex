<?php


namespace Opus\Bibtex\Import\Rules;

class PageLast extends SimpleRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'pages';
        $this->opusFieldName = 'PageLast';
        $this->fn = function ($value) {
            $value = str_replace(['--', '––', '–'], '-', $value);
            $parts = explode('-', $value, 2);
            if (count($parts) == 2) {
                return trim($parts[1]);
            }
            return trim($parts[0]);
        };
    }
}