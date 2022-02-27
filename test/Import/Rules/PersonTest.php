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
 * @copyright   Copyright (c) 2021-2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Rules;

use Opus\Bibtex\Import\Parser;
use Opus\Bibtex\Import\Processor;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    public function testProcessAuthors()
    {
        $proc        = new Processor();
        $metadata    = [];
        $bibtexBlock = [
            'Author' => 'Wang, Y. and Xie and Steffen, S.',
            'Editor' => 'Ming, J.',
        ];
        $proc->handleRecord($bibtexBlock, $metadata);

        $this->assertEquals(
            [
                [
                    'FirstName' => 'Y.',
                    'LastName'  => 'Wang',
                    'Role'      => 'author',
                ],
                [
                    'LastName' => 'Xie',
                    'Role'     => 'author',
                ],
                [
                    'FirstName' => 'S.',
                    'LastName'  => 'Steffen',
                    'Role'      => 'author',
                ],
                [
                    'FirstName' => 'J.',
                    'LastName'  => 'Ming',
                    'Role'      => 'editor',
                ],
            ],
            $metadata['Person']
        );
    }

    public function testProcessSpecialCharacters()
    {
        $parser       = new Parser('@misc{test, author = {M{\"u}ller, Michael}}');
        $bibtexRecord = $parser->parse();

        $proc     = new Processor();
        $metadata = [];
        $proc->handleRecord(
            $bibtexRecord[0],
            $metadata
        );

        $this->assertEquals(
            [
                [
                    'FirstName' => 'Michael',
                    'LastName'  => 'Müller',
                    'Role'      => 'author',
                ],
            ],
            $metadata['Person']
        );
    }

    public function testApply()
    {
        $this->markTestIncomplete();

        $record = [
            'Author' => 'Wang, Y. and Xie and Steffen, S.',
        ];

        $rule = new Person();

        $result = [];

        $rule->apply($record, $result);

        // var_dump($result);
    }
}
