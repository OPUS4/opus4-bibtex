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
 * @category    Tests
 * @package     OpusTest\Processor\Rule
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Processor\Rule;

use Opus\Bibtex\Import\Processor\Rule\DocumentType;

class DocumentTypeTest extends \PHPUnit_Framework_TestCase
{

    public function dataProvider()
    {
        return [
            [['ptype' => 'conference'], 'conferenceobject'],
            [['ptype' => 'journal'], 'article'],
            [['type' => 'article'], 'article']
        ];
    }

    /**
     * Test Mapping of document-types.
     *
     * @param mixed $arg Value to check given by the data provider and $res as expected mapping-result.
     * @return void
     *
     * @dataProvider dataProvider
     */
    public function testProcessMapping($arg, $res)
    {
        $rule = new DocumentType();
        foreach ($arg as $key => $value) {
            $return = $rule->process($key, $value, $arg);
            $this->assertEquals([true, 'Type', $res], $return);
        }
    }

    public function testProcessTwoInfos()
    {
        $rule = new DocumentType();
        $bibtexBlock = [
            'ptype' => 'conference',
            'type' => 'article'
        ];

        $return = $rule->process('type', 'article', $bibtexBlock);

        $this->assertEquals([true, 'Type', 'conferenceobject'], $return);
    }
}
