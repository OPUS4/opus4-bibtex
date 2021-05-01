<?php


namespace Opus\Bibtex\Import\Rules;


class Subject extends ArrayRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'keywords';
        $this->opusFieldName = 'Subject';
        $this->fn = function ($value) {
            $keywords = explode(', ', $value);
            $result = [];
            foreach ($keywords as $keyword) {
                $result[] = [
                    'Language' => 'eng',
                    'Type' => 'uncontrolled',
                    'Value' => $this->deleteBrace($keyword)
                ];
            }
            return $result;
        };
    }
}