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

class RawData implements RuleInterface
{

    const SOURCE_DATA_KEY = 'opus.import.data';

    const SOURCE_DATA_HASH_KEY = 'opus.import.dataHash';

    public function process($field, $value, $bibtexBlock)
    {
        $return = [false];

        if ($field == '_original') {
            $enrichment = [[
                'KeyName' => self::SOURCE_DATA_KEY,
                'Value' => $value
            ], [
                'KeyName' => self::SOURCE_DATA_HASH_KEY,
                'Value' => self::hash($value)
            ]];
            $return = [
                true,
                'Enrichment',
                $enrichment
            ];
        }
        return $return;
    }

    public static function hash($data)
    {
        return md5($data);
    }
}
