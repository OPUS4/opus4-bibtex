<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @category    BibTeX
 * @package     Opus\Bibtex\Import\Rules
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import\Rules;

use function array_key_exists;
use function array_keys;
use function in_array;
use function is_array;
use function preg_match;
use function str_replace;

/**
 * Behandlung von Umlauten, die im BibTeX-File nicht korrekt angegeben wurden (siehe OPUSVIER-4216).
 * Ein Beispiel für solche Umlaute findet sich in der Testdatei specialchars-invalid.bib
 */
class Umlauts extends AbstractComplexRule
{
    /** @var array Namen von Feldern, die bei der Umlautbehandlung ignoriert werden sollen */
    protected $fieldBlacklist;

    /**
     * Konstruktor
     */
    public function __construct()
    {
        parent::__construct();
        $this->fieldBlacklist = [
            'Enrichment' => [
                'KeyName' => [
                    SourceDataHash::SOURCE_DATA_HASH_KEY,
                    SourceData::SOURCE_DATA_KEY,
                ],
            ],
        ];
    }

    /**
     * Wandelt jeden speziell angegebenen Umlaut im übergebenen Feldwert in das zugehörige Unicode-Zeichen um.
     *
     * @param string $value Feldwert, der ggf. umzuwandelnde Umlaute enthält
     * @return string|boolean liefert den umgewandelten Wert oder false, falls keine Umwandlung von Zeichen erfolgt ist
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

    /**
     * Erlaubt das Setzen der Feldnamen, die bei der Prüfung ignoriert werden sollen.
     *
     * @param array $fieldBlacklist Array von Feldnamen
     */
    public function setFieldBlacklist(array $fieldBlacklist)
    {
        $this->fieldBlacklist = $fieldBlacklist;
    }

    /**
     * Iteriert über die im OPUS-Metadatensatz enthaltenen Felder und ersetzt speziell angegebene Umlaute durch ihr
     * zugehöriges Unicode-Zeichen. Hierbei werden Enrichments von EnrichmentKeys, die sich auf der Blacklist befinden,
     * ignoriert.
     *
     * @param array $fieldValues Wert aus BibTex-Feldern: wird an dieser Stelle nicht ausgewertet
     * @param array $documentMetadata OPUS-Metadatensatz (Array von Feldwerten)
     */
    protected function setFields($fieldValues, &$documentMetadata)
    {
        foreach ($documentMetadata as $fieldName => $fieldValue) {
            if (is_array($fieldValue)) {
                $this->setArrayField($fieldName, $fieldValue, $documentMetadata);
            } else {
                $this->setSimpleField($fieldName, $fieldValue, $documentMetadata);
            }
        }
    }

    /**
     * Setzt im Feld mit dem übergebenen Namen den übergebenen Feldwert, sofern das Feld nicht ignoriert werden soll.
     *
     * @param string $fieldName Feldname
     * @param string $fieldValue Feldwert
     * @param array $documentMetadata OPUS-Metadatensatz
     */
    private function setSimpleField($fieldName, $fieldValue, &$documentMetadata)
    {
        if ($this->skipField($fieldName, $fieldValue)) {
            return;
        }
        $convertedFieldValue = $this->convertUmlauts($fieldValue);
        if ($convertedFieldValue !== false) {
            $documentMetadata[$fieldName] = $convertedFieldValue;
        }
    }

    /**
     * Setzt im Array-basierten Feld mit dem übergebenen Namen den übergebenen Feldwert, sofern das Feld nicht ignoriert
     * werden soll.
     *
     * @param string $fieldName Feldname
     * @param array $fieldValue Feldwert
     * @param array $documentMetadata OPUS-Metadatensatz
     */
    private function setArrayField($fieldName, $fieldValue, &$documentMetadata)
    {
        foreach ($fieldValue as $subFieldIndex => $subFieldValue) {
            if ($this->skipField($fieldName, $subFieldValue)) {
                continue;
            }
            foreach ($subFieldValue as $name => $value) {
                $convertedFieldValue = $this->convertUmlauts($value);
                if ($convertedFieldValue !== false) {
                    $documentMetadata[$fieldName][$subFieldIndex][$name] = $convertedFieldValue;
                }
            }
        }
    }

    /**
     * Prüft, ob das Metadatenfeld bei der Umlautbehandlung berücksichtigt werden soll.
     *
     * @param string $fieldName Feldbezeichnung
     * @param string|array $fieldValue Feldwert
     * @return bool Liefert true, gdw. das Feld bei der Umlautbehandlung nicht berücksichtigt werden soll.
     */
    private function skipField($fieldName, $fieldValue)
    {
        if (is_array($fieldValue)) {
            if (array_key_exists($fieldName, $this->fieldBlacklist)) {
                $blacklistEntry = $this->fieldBlacklist[$fieldName];
                foreach (array_keys($blacklistEntry) as $key) {
                    if (array_key_exists($key, $fieldValue)) {
                        return in_array($fieldValue[$key], $blacklistEntry[$key]);
                    }
                }
            }
            return false;
        }

        return in_array($fieldName, $this->fieldBlacklist);
    }
}
