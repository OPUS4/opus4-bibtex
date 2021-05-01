<?php


namespace Opus\Bibtex\Import\Rules;


class Doi extends ArrayRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'doi';
        $this->opusFieldName = 'Identifier';
        $this->fn = function ($value) {
            if (strtolower(substr($value, 0, 4)) === 'doi:') {
                $value = trim(substr($value, 4)); // Präfix doi: abschneiden
            }
            return [
                'Value' => $value,
                'Type' => 'doi'
            ];
        };
    }
}