<?php


namespace Opus\Bibtex\Import\Rules;


use Opus\Bibtex\Import\AbstractMappingConfiguration;

class Umlauts extends ComplexRule
{

    public function __construct()
    {
        return parent::__construct(
            function ($fieldValues, &$documentMetadata) {
                // behandelt Umlaute, die im BibTeX-File nicht korrekt angegeben wurden (siehe OPUSVIER-4216)
                foreach ($documentMetadata as $fieldName => $fieldValue) {
                    if (is_array($fieldValue)) {
                        foreach ($fieldValue as $subFieldIndex => $subFieldValue) {
                            if ($fieldName === 'Enrichment' &&
                                ($subFieldValue['KeyName'] === AbstractMappingConfiguration::SOURCE_DATA_HASH_KEY ||
                                    $subFieldValue['KeyName'] === AbstractMappingConfiguration::SOURCE_DATA_KEY)) {
                                continue; // der Original-BibTeX-Record soll nicht verändert werden
                            }
                            foreach ($subFieldValue as $name => $value) {
                                $convertedFieldValue = $this->convertUmlauts($value);
                                if ($convertedFieldValue !== false) {
                                    $documentMetadata[$fieldName][$subFieldIndex][$name] = $convertedFieldValue;
                                }
                            }
                        }
                    } else {
                        $convertedFieldValue = $this->convertUmlauts($fieldValue);
                        if ($convertedFieldValue !== false) {
                            $documentMetadata[$fieldName] = $convertedFieldValue;
                        }
                    }
                }
            }
        );
    }

    /**
     * Behandlung von Umlauten (siehe OPUSVIER-4216) bzw. Beispiel in specialchars-invalid.bib.
     *
     * @param $value
     * @return array
     */
    protected function convertUmlauts($value)
    {
        if (! preg_match('#"[a, o, u]#i', $value)) {
            return false;
        }
        return str_replace(
            ['"a', '"A', '"o', '"O', '"u', '"U'],
            ['ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü'],
            $value
        );
    }
}