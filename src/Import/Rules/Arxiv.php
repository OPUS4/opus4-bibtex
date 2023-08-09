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

use function preg_replace;
use function strlen;
use function strtolower;
use function substr;

/**
 * Verarbeitung von ArXiv-Identifiern. Hierbei wird eine ggf. vorhandene Resolver-URL vor dem ArXiv-Identifier
 * entfernt.
 */
class Arxiv extends AbstractArrayRule
{
    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->setBibtexField('arxiv');
        $this->setOpusField('Identifier');
    }

    /**
     * Bestimmt den aus dem BibTeX-Record abgeleiteten Wert des Identifiers.
     *
     * @param string $value Feldwert aus BibTeX-Record
     * @return array
     */
    public function getValue($value)
    {
        $type = 'url';

        $baseUrl1 = 'http://arxiv.org/abs/';
        $baseUrl2 = 'https://arxiv.org/abs/';
        if (
            substr($value, 0, strlen($baseUrl1)) === $baseUrl1 ||
            substr($value, 0, strlen($baseUrl2)) === $baseUrl2
        ) {
            $type = 'arxiv';
            // URL-Präfix abschneiden, so dass nur die ArXiv-ID übrigbleibt
            $value = preg_replace('#https?://arxiv.org/abs/#i', '', $value);
        } elseif (strtolower(substr($value, 0, 6)) === 'arxiv:') {
            $type  = 'arxiv';
            $value = substr($value, 6); // Präfix 'arxiv:' abschneiden
        }

        return [
            'Value' => $value,
            'Type'  => $type,
        ];
    }
}
