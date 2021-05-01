<?php


namespace Opus\Bibtex\Import\Rules;

class Type extends SimpleRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'type';
        $this->opusFieldName = 'Type';
        $this->fn = function ($value) {
            if (array_key_exists($value, DocumentTypeMapping::$MAPPING)) {
                return DocumentTypeMapping::$MAPPING[$value];
            } else {
                return DocumentTypeMapping::$DEFAULT_OPUS_TYPE;
            }
        };
    }
}