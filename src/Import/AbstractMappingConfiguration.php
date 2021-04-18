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
 * @category    BibTeX
 * @package     Opus\Bibtex\Import
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import;

abstract class AbstractMappingConfiguration
{
    const SOURCE_DATA_KEY = 'opus.import.data';
    const SOURCE_DATA_HASH_KEY = 'opus.import.dataHash';
    const HASH_FUNCTION = 'md5';

    protected $name;
    protected $description;
    private $ruleList = [];

    /**
     * Liefert den Namen der Mapping-Konfiguration. Diese wird z.B. für die Auswahl der
     * Mapping-Konfiguration in der Administration benötigt.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Liefert eine Beschreibung der Mapping-Konfiguration zurück. In dieser können
     * Hinweise oder Bemerkung zum vorliegenden Mapping hinterlegt werden, so dass
     * die spätere Auswahl der Mapping-Konfiguration erleichtert wird.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Gibt die momentan konfigurierte Liste der Regeln zurück.
     *
     * @return array
     */
    public function getRuleList()
    {
        return $this->ruleList;
    }

    /**
     * Fügt die übergebene Regel am Ende der Regelliste hinzu, d.h. die übergebene Regel
     * wird erst nach der Ausführung aller anderen Regeln ausgeführt.
     *
     * @param $rule die hinzuzufügende Regel
     */
    public function prependRule($rule)
    {
        array_unshift($this->ruleList, $rule);
        return $this;
    }

    /**
     * Fügt die übergebene Regel am Anfang der Regelliste hinzu, d.h. die übergebene Regel
     * wird vor der Ausführung aller anderen Regeln ausgeführt.
     *
     * @param $rule die hinzuzufügende Regel
     */
    public function appendRule($rule)
    {
        $this->ruleList[] = $rule;
        return $this;
    }

    /**
     * Setzt die Liste der Regeln zurück, so dass die Anwendung der Mapping-Konfiguration einer No-Op entspricht.
     */
    public function resetRules()
    {
        $this->ruleList = [];
    }

    protected function deleteBrace($value)
    {
        if (strlen($value) >= 2 && substr($value, 0, 1) == '{' && substr($value, -1, 1) == '}') {
            $value = substr($value, 1, -1);
        }
        return trim($value);
    }

    protected function extractNameParts($name)
    {
        $name = trim($name);
        $posFirstComma = strpos($name, ',');
        if ($posFirstComma !== false) {
            // Nachname getrennt durch Komma mit Vorname(n)
            // alles nach dem ersten Komma wird hierbei als Vorname interpretiert
            $result = [
                'LastName' => trim(substr($name, 0, $posFirstComma))
            ];
            if ($posFirstComma < strlen($name) - 1) {
                $result['FirstName'] = trim(substr($name, $posFirstComma + 1));
            }
            return $result;
        }

        // mehrere Namensbestandteile sind nicht durch Komma getrennt
        // alles nach dem ersten Leerzeichen wird als Nachname aufgefasst
        // kommt kein Leerzeichen wird, so wurde vermutlich nur der Nachname angegeben
        $posFirstSpace = strpos($name, ' ');
        if ($posFirstSpace === false) {
            return [
                'LastName' => $name
            ];
        }

        $posLastPeriod = strrpos($name, '.');
        if ($posLastPeriod === false) {
            // letztes Zeichen kann kein Leerzeichen sein, daher kein Vergleich der Länge von $name mit $posFirstSpace
            return [
                'FirstName' => trim(substr($name, 0, $posFirstSpace)),
                'LastName' => trim(substr($name, $posFirstSpace + 1))
            ];
        }

        // falls Namensbestandteile abgekürzt werden, so betrachte alles nach dem letzten Punkt als Nachnamen
        $result = [
            'FirstName' => trim(substr($name, 0, $posLastPeriod + 1)),
        ];
        if ($posLastPeriod < strlen($name) - 1) {
            $result['LastName'] = trim(substr($name, $posLastPeriod + 1));
        }
        return $result;
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
