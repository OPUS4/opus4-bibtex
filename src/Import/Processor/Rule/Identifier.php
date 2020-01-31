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
 * @category    Processor
 * @package     Opus\Processor\Rule
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Processor\Rule;

/**
 * Class Identifier
 * @package Opus\Bibtex\Import\Processor\Rule
 *
 * TODO wird momentan für jeden Artikel mehrfach ausgeführt
 * TODO handle https://arxiv.org/abs/ and http://arxiv.org/abs/
 */
class Identifier implements RuleInterface
{
    private $identifierMap = [
        'arxiv' => 'arxiv',
        'doi' => 'doi',
        'issn' => 'issn',
        'isbn' => 'isbn'
    ];

    /**
     * @param $field
     * @param $value
     * @param $bibtexBlock
     * @return array
     *
     * TODO Mapping-Mechanismus aus dieser Funktion entfernen (kann nicht überschrieben werden)
     * TODO Spezialbehandlung auslagern (kapseln)
     */
    public function process($field, $value, $bibtexBlock)
    {
        $return = [false];
        if (array_key_exists($field, $this->identifierMap)) {
            $identifiers = [];
            foreach ($bibtexBlock as $key => $value) {
                if (array_key_exists($key, $this->identifierMap)) {
                    $type = $this->identifierMap[$key];

                    switch ($type) {
                        case 'arxiv':
                            $baseUrl1 = 'http://arxiv.org/abs/';
                            $baseUrl2 = 'https://arxiv.org/abs/';
                            if (substr($value, 0, strlen($baseUrl1)) !== $baseUrl1 &&
                                substr($value, 0, strlen($baseUrl2)) !== $baseUrl2) {
                                $type = 'url';
                            } else {
                                $value = preg_replace('#http.://arxiv.org/abs/#i', '', $value);
                            }
                            break;
                    }

                    $identifier = [
                        'Value' => $value,
                        'Type' => $type
                    ];

                    array_push($identifiers, $identifier);
                }
            }
            $return = [
                true,
                'Identifier',
                $identifiers
            ];
        }
        return $return;
    }
}
