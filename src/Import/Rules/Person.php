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

use function array_merge;
use function explode;
use function strlen;
use function strpos;
use function strrpos;
use function substr;
use function trim;

/**
 * Erlaubt das Erzeugen von OPUS-Metadatenfeldern für die Speicherung von Personenangaben.
 */
class Person extends AbstractArrayRule
{
    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->setOpusField('Person');
    }

    /**
     * Erzeugt ein OPUS-Metadatenfelder für die Speicherung von Personendaten.
     * Die Rolle der Person wird aus dem Namen des ausgewerteten BibTeX-Felds abgeleitet.
     *
     * @param string $value Wert des BibTeX-Felds
     * @return array
     */
    protected function getValue($value)
    {
        $persons = explode(' and ', $value);
        $result  = [];
        foreach ($persons as $person) {
            $result[] = array_merge(['Role' => $this->bibtexField], $this->extractNameParts($person));
        }
        return $result;
    }

    /**
     * Zerlegt einen BibTeX Autoren String in seine Teile.
     *
     * @param string $name BibTeX-Autor String.
     * @return array
     */
    private function extractNameParts($name)
    {
        $name          = trim($name);
        $posFirstComma = strpos($name, ',');
        if ($posFirstComma !== false) {
            // Nachname getrennt durch Komma mit Vorname(n)
            // alles nach dem ersten Komma wird hierbei als Vorname interpretiert
            $result = [
                'LastName' => trim(substr($name, 0, $posFirstComma)),
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
                'LastName' => $name,
            ];
        }

        $posLastPeriod = strrpos($name, '.');
        if ($posLastPeriod === false) {
            // letztes Zeichen kann kein Leerzeichen sein, daher kein Vergleich der Länge von $name mit $posFirstSpace
            return [
                'FirstName' => trim(substr($name, 0, $posFirstSpace)),
                'LastName'  => trim(substr($name, $posFirstSpace + 1)),
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
}
