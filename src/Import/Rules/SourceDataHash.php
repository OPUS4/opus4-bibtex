<?php


namespace Opus\Bibtex\Import\Rules;


use Opus\Bibtex\Import\AbstractMappingConfiguration;

class SourceDataHash extends ArrayRule
{
    public function __construct()
    {
        return parent::__construct(
            '_original',
            'Enrichment',
            function ($value) {
                return [
                    'KeyName' => AbstractMappingConfiguration::SOURCE_DATA_HASH_KEY,
                    'Value' => AbstractMappingConfiguration::HASH_FUNCTION . ':' . (AbstractMappingConfiguration::HASH_FUNCTION)($value)
                ];
            }
        );
    }
}