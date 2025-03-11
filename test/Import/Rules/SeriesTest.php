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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Rules;

use Opus\Bibtex\Import\Rules\Series;
use Opus\Common\Series as OpusSeries;
use PHPUnit\Framework\TestCase;

use function var_dump;

class SeriesTest extends TestCase
{
    /** @var OpusSeries */
    private $series;

    public function setUp(): void
    {
        parent::setUp();

        /* TODO testing with database not setup
        $series = OpusSeries::new();
        $series->setName('Test Series');
        $series->store();

        $this->series = $series;
        */
    }

    public function tearDown(): void
    {
        if ($this->series !== null) {
            $this->series->delete();
        }

        parent::tearDown();
    }

    public function testApply()
    {
        $this->markTestIncomplete('todo');

        $rule = new Series();

        $metadata     = [];
        $bibtexRecord = [
            'series' => '',
        ];
        $rule->apply($bibtexRecord, $metadata);

        $expected = [];

        $this->assertEquals($expected, $metadata['Series']);
    }

    public function testApplyValuesWithWhitespace()
    {
        $this->markTestIncomplete('todo');
    }

    public function testApplyWithMatchingName()
    {
        $this->markTestIncomplete('todo');
    }

    public function testApplyException()
    {
        $this->markTestIncomplete('todo');
    }

    public function testDuplicateSeriesNumber()
    {
        $this->markTestIncomplete('check');
    }
}
