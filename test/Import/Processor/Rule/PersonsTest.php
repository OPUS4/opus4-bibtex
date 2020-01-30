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

use Opus\Bibtex\Import\Processor\Rule\Persons;

class PersonsTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessAuthors()
    {
        $rule = new Persons();
        $bibtexBlock = [
            'Author' => 'Wang, Y. and Xie and Steffen, S.',
            'Editor' => 'Ming, J.'
        ];

        $return = $rule->process(
            'Author',
            $bibtexBlock['Author'],
            $bibtexBlock
        );

        $expected = [
            true,
            'Person',
            [
                [
                    'FirstName' => 'Y.',
                    'LastName' => 'Wang',
                    'Role' => 'author'
                ],
                [
                    'LastName' => 'Xie',
                    'Role' => 'author'
                ],
                [
                    'FirstName' => 'S.',
                    'LastName' => 'Steffen',
                    'Role' => 'author'
                ],
                [
                    'FirstName' => 'J.',
                    'LastName' => 'Ming',
                    'Role' => 'editor'
                ]
            ]
        ];

        $this->assertEquals($expected, $return);
    }

    public function testProcessSpecialCharacters()
    {
        $bibtex = [
            'Author' => 'M{\"u}ller, Michael'
        ];

        $rule = new Persons();

        $result = $rule->process('Author', $bibtex['Author'], $bibtex);

        var_dump($result);
    }
}
