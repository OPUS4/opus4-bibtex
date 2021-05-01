<?php


namespace Opus\Bibtex\Import\Rules;

class PageNumber extends ConstantValueRule
{
    public function __construct()
    {
        $this->setOpusFieldName('PageNumber');
        return $this;
    }

    public function apply($bibtexRecord, &$documentMetadata)
    {
        $pageFirst =
            array_key_exists('PageFirst', $documentMetadata) ? intval($documentMetadata['PageFirst']) : 0;
        $pageLast =
            array_key_exists('PageLast', $documentMetadata) ? intval($documentMetadata['PageLast']) : 0;
        if ($pageFirst > 0 && $pageLast > 0 && $pageLast >= $pageFirst) {
            $this->setValue(1 + $pageLast - $pageFirst);
            return parent::apply($bibtexRecord, $documentMetadata);
        }
        return false;
    }
}