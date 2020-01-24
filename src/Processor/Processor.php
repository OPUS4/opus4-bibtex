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
 * @package     Opus\Processor
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Processor;

class Processor
{
    public function convertBibtexToOpus($bibtexBlock)
    {
        $opusArray = [];
        $bibtexBlock = array_change_key_case($bibtexBlock);
        foreach ($bibtexBlock as $field => $value) {
            foreach (glob(__DIR__.'/ConvertingRules/*.php') as $file) {
                require_once $file;
                $class = "Opus\Processor\ConvertingRules\\" . basename($file, '.php');
                if (class_exists($class)) {
                    $rule = new $class;
                    $return = $rule->process($field, $value, $bibtexBlock);
                    // TODO: Die Zuordnung ist noch nicht ideal.
                    // TODO: Die Regeln sind viel zu speziell. Ein Konzept um diese allgemeiner zu halten und konfigurierbar zu machen, wäre die bessere Lösung
                    // TODO: Nicht bearbeitete Zeilen sollten geloggt werden. Da könnte man gut mit einer Art Registry arbeiten.
                    if ($return[0] === true and ! array_key_exists($return[1], $opusArray)) {
                        $opusArray[$return[1]] = $return[2];
                    }
                }
            }
        }
        $opusArray = $this->addDefaultEntries($opusArray);

        return $opusArray;
    }

    public function addDefaultEntries($opusArray)
    {
        // TODO: Das MUSS konfigurierbar sein!
        $opusArray['Language'] = 'eng';
        $opusArray['BelongsToBibliography'] = '0';

        return $opusArray;
    }
}
