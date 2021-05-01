<?php


namespace Opus\Bibtex\Import\Rules;


use Opus\Bibtex\Import\AbstractMappingConfiguration;

class SourceData extends ArrayRule
{
    public function __construct()
    {
        return parent::__construct(
            '_original',
            'Enrichment',
            function ($value) {
                return [
                    'KeyName' => AbstractMappingConfiguration::SOURCE_DATA_KEY,
                    'Value' => $value
                ];
            }
        );
    }
}