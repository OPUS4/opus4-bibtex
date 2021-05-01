<?php


namespace Opus\Bibtex\Import\Rules;


class Arxiv extends ArrayRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'arxiv';
        $this->opusFieldName = 'Identifier';
        $this->fn = function ($value) {
            $type = 'url';

            $baseUrl1 = 'http://arxiv.org/abs/';
            $baseUrl2 = 'https://arxiv.org/abs/';
            if (substr($value, 0, strlen($baseUrl1)) == $baseUrl1 ||
                substr($value, 0, strlen($baseUrl2)) == $baseUrl2) {
                $type = 'arxiv';
                // URL-Präfix abschneiden, so dass nur die ArXiv-ID übrigbleibt
                $value = preg_replace('#https?://arxiv.org/abs/#i', '', $value);
            } elseif (strtolower(substr($value, 0, 6)) === 'arxiv:') {
                $type = 'arxiv';
                $value = substr($value, 6); // Präfix 'arxiv:' abschneiden
            }

            return [
                'Value' => $value,
                'Type' => $type
            ];
        };
    }
}