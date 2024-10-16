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
 */

namespace OpusTest\Bibtex\Import\Rules;

use Opus\Bibtex\Import\Parser;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\Person;
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

    public function testGetValueSingleAuthor()
    {
        $rule = new Person();
        $rule->setBibtexField('author');

        $value = $rule->getValue('Meier, Thomas');

        $this->assertEquals([
            [
                'Role'      => 'author',
                'LastName'  => 'Meier',
                'FirstName' => 'Thomas',
            ],
        ], $value);
    }

    public function testGetValueMultipleAuthors()
    {
        $rule = new Person();
        $rule->setBibtexField('author');

        $value = $rule->getValue('Meier, Thomas and Muster, Mathilde');

        $this->assertEquals([
            [
                'Role'      => 'author',
                'LastName'  => 'Meier',
                'FirstName' => 'Thomas',
            ],
            [
                'Role'      => 'author',
                'LastName'  => 'Muster',
                'FirstName' => 'Mathilde',
            ],
        ], $value);
    }

    /**
     * @return array
     */
    public static function valuesWithOrcidAndGndProvider()
    {
        return [
            ['ORCID:0001-0002-0003-0004+GND:123456789+Doe, John'],
            ['GND:123456789+ORCID:0001-0002-0003-0004+Doe, John'],
            ['ORCID:0001-0002-0003-0004+Doe, John+GND:123456789'],
            ['GND:123456789+Doe, John+ORCID:0001-0002-0003-0004'],
            ['Doe, John+ORCID:0001-0002-0003-0004+GND:123456789'],
            ['Doe, John+GND:123456789+ORCID:0001-0002-0003-0004'],
            [' ORCID:0001-0002-0003-0004 + GND:123456789 + Doe , John '],
            [' ORCID: 0001-0002-0003-0004 + GND: 123456789 + Doe , John '],
            ['Doe, John (ORCID:0001-0002-0003-0004,GND:123456789)'],
            ['Doe, John ( ORCID: 0001-0002-0003-0004 , GND: 123456789 ) '],
            ['Doe, John(ORCID:0001-0002-0003-0004, GND:123456789)'],
        ];
    }

    /**
     * @param string $fieldValue
     * @dataProvider valuesWithOrcidAndGndProvider
     */
    public function testGetValueWithOrcidAndGnd($fieldValue)
    {
        $rule = new Person();
        $rule->setBibtexField('author');

        $value = $rule->getValue($fieldValue);

        $this->assertEquals([
            [
                'FirstName'       => 'John',
                'LastName'        => 'Doe',
                'Role'            => 'author',
                'IdentifierOrcid' => '0001-0002-0003-0004',
                'IdentifierGnd'   => '123456789',
            ],
        ], $value);
    }

    public function testGetValueWithNameAnd()
    {
        $rule = new Person();
        $rule->setBibtexField('author');

        $value = $rule->getValue('And, And and And, And');

        $this->assertEquals([
            [
                'FirstName' => 'And',
                'LastName'  => 'And',
                'Role'      => 'author',
            ],
            [
                'FirstName' => 'And',
                'LastName'  => 'And',
                'Role'      => 'author',
            ],
        ], $value);
    }

    public function testGetValueWithNameAndInNames()
    {
        $rule = new Person();
        $rule->setBibtexField('author');

        $value = $rule->getValue('Brand, Mandy and Bland, Candy');

        $this->assertEquals([
            [
                'FirstName' => 'Mandy',
                'LastName'  => 'Brand',
                'Role'      => 'author',
            ],
            [
                'FirstName' => 'Candy',
                'LastName'  => 'Bland',
                'Role'      => 'author',
            ],
        ], $value);
    }

    public function testGetValueWithMultipleAuthorsWithOrcid()
    {
        $rule = new Person();
        $rule->setBibtexField('author');

        $value = $rule->getValue('ORCID:0001-0002-0003-0004+Doe, John and Muster, Jane+ORCID:0000-0002-0002-0002');

        $this->assertEquals([
            [
                'FirstName'       => 'John',
                'LastName'        => 'Doe',
                'Role'            => 'author',
                'IdentifierOrcid' => '0001-0002-0003-0004',
            ],
            [
                'FirstName'       => 'Jane',
                'LastName'        => 'Muster',
                'Role'            => 'author',
                'IdentifierOrcid' => '0000-0002-0002-0002',
            ],
        ], $value);
    }

    public function testGetValueWithMultipleAuthorsWithOrcidInBrackets()
    {
        $rule = new Person();
        $rule->setBibtexField('author');

        $value = $rule->getValue('Doe, John (ORCID:0001-0002-0003-0004) and Muster, Jane (ORCID:0000-0002-0002-0002)');

        $this->assertEquals([
            [
                'FirstName'       => 'John',
                'LastName'        => 'Doe',
                'Role'            => 'author',
                'IdentifierOrcid' => '0001-0002-0003-0004',
            ],
            [
                'FirstName'       => 'Jane',
                'LastName'        => 'Muster',
                'Role'            => 'author',
                'IdentifierOrcid' => '0000-0002-0002-0002',
            ],
        ], $value);
    }

    public function testGetValueSupportIdentifierMisc()
    {
        $rule = new Person();
        $rule->setBibtexField('author');
        $value = $rule->getValue('MISC:1234+Doe, John');

        $this->assertEquals([
            [
                'FirstName'      => 'John',
                'LastName'       => 'Doe',
                'Role'           => 'author',
                'IdentifierMisc' => '1234',
            ],
        ], $value);
    }

    public function testGetRoleDefault()
    {
        $rule = new Person();

        $rule->setBibtexField('editor');
        $this->assertEquals('editor', $rule->getRole());

        $rule->setBibtexField('Contributor');
        $this->assertEquals('contributor', $rule->getRole());
    }

    public function testSetRole()
    {
        $rule = new Person();

        $rule->setBibtexField('contributors');
        $rule->setRole('contributor');
        $rule->setBibtexField('contributors');

        $this->assertEquals('contributor', $rule->getRole());
    }
}
