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

/**
 * Setzt den Hashwert des importierten BibTeX-Record als Wert des Enrichments opus.import.dataHash.
 * Hierbei wird standardmäßig die MD5-Hashfunktion verwendet.
 */
class SourceDataHash extends ArrayRule
{
    /**
     * Name des Enrichments, in dem der Hashwert (auf Basis der Hashfunktion HASH_FUNCTION) des importierten
     * BibTeX-Records gespeichert wird
     */
    const SOURCE_DATA_HASH_KEY = 'opus.import.dataHash';

    /**
     * Name der Hashfunktion, die zur Bestimmung des Hashwerts verwendet werden soll.
     *
     * TODO konfigurierbar machen?
     */
    const HASH_FUNCTION = 'md5';

    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->setBibtexField('_original');
        $this->setOpusField('Enrichment');
    }

    /**
     * Ermittelt den Feldwert für den OPUS-Metadatensatz.
     *
     * @param string $value BibTeX-Record
     * @return array
     */
    protected function getValue($value)
    {
        return [
            'KeyName' => self::SOURCE_DATA_HASH_KEY,
            'Value'   => self::HASH_FUNCTION . ':' . (self::HASH_FUNCTION)($value),
        ];
    }
}
