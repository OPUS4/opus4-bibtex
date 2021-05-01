<?php


namespace Opus\Bibtex\Import\Rules;

/**
 * ptype ist kein Standard-BibTeX-Feld: das Feld ptype kann genutzt werden, um das Typ-Mapping
 * auf Basis des BibTeX-Types (die Zeichenkette nach @) zu umgehen
 * ist im BibTeX-Record kein Feld ptype vorhanden, so wird der Typ aus der Zeichenkette nach @ abgeleitet
 */
class Ptype extends SimpleRule
{
    public function __construct()
    {
        $this->bibtexFieldName = 'ptype';
        $this->opusFieldName = 'Type';
        $this->fn = function ($value) {
            if (array_key_exists($value, DocumentTypeMapping::$MAPPING)) {
                return DocumentTypeMapping::$MAPPING[$value];
            }
        };
    }
}