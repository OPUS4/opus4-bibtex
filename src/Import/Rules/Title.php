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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Rules;

/**
 * Erlaubt das Setzen eines Feldwerts fuer einen zu definierenden Titel.
 */
class Title extends AbstractArrayRule
{
    /** @var string Sprache des Titels (Default: eng) */
    private $titleLanguage = 'eng';

    /**
     * Gibt die gesetzte Sprache des Titels zurueck.
     * @return string
     */
    public function getLanguage()
    {
        return $this->titleLanguage;
    }

    /**
     * Erlaubt das Setzen der Sprache fuer den Titel.
     * @param string $titleLanguage Sprache
     * @return $this
     */
    public function setLanguage($titleLanguage)
    {
        $this->titleLanguage = $titleLanguage;
        return $this;
    }

    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->setOpusField('TitleMain');
    }

    /**
     * Setzt den Titel und die gewuenschte Sprache.
     * @param string $value Wert des Titels
     * @return array
     */
    protected function getValue($value)
    {
        return [
            'Language' => $this->titleLanguage,
            'Value' => $this->deleteBrace($value),
            'Type' => 'main',
        ];
    }
}
