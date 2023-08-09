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

use function explode;

/**
 * Erlaubt das Setzen von Schlagworten vom Typ 'uncontrolled' und der Sprachen 'eng'. Typ und Sprache können optional
 * überschrieben werden.
 */
class Subject extends AbstractArrayRule
{
    /** @var string Typ des Schlagworts */
    private $type = 'uncontrolled';

    /** @var string Sprache des Schlagworts */
    private $language = 'eng';

    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->setBibtexField('keywords');
        $this->setOpusField('Subject');
    }

    /**
     * Gibt den Typ des Schlagworts zurück.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Erlaubt das Setzen des Typs des Schlagworts.
     *
     * @param string $type Schlagworttyp
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Gibt die Sprache des Schlagworts zurück.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Erlaubt das Setzen der Sprache des Schlagworts.
     *
     * @param string $language Sprachkürzel
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Erlaubt das Setzen von Schlagworten des Typs 'uncontrolled' auf Basis des übergebenen Wertes. Die einzelnen
     * Schlagworte sind hierbei durch Komma voneinander getrennt.
     *
     * @param string $value kommaseparierte Liste von Schlagworten
     * @return array
     */
    public function getValue($value)
    {
        $keywords = explode(', ', $value);
        $result   = [];
        foreach ($keywords as $keyword) {
            $result[] = [
                'Language' => $this->language,
                'Type'     => $this->type,
                'Value'    => $this->deleteBrace($keyword),
            ];
        }
        return $result;
    }
}
