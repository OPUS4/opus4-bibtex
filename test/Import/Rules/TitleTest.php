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

use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\Title;
use PHPUnit\Framework\TestCase;

class TitleTest extends TestCase
{
    public static function dataProvider(): array
    {
        return [
            [['title' => 'My Article'], 'My Article'],
            [['title' => '{My Article}'], 'My Article'],
        ];
    }

    /**
     * Test title-rule.
     *
     * @param mixed  $arg Value to check given by the data provider
     * @param string $res expected mapping-result
     * @dataProvider dataProvider
     */
    public function testProcess($arg, $res)
    {
        $proc     = new Processor();
        $metadata = [];
        $proc->handleRecord($arg, $metadata);

        $this->assertEquals(
            [
                [
                    'Language' => 'eng',
                    'Value'    => $res,
                    'Type'     => 'main',
                ],
            ],
            $metadata['TitleMain']
        );
    }

    public function testConstruct()
    {
        $title = new Title();

        $this->assertEquals('main', $title->getTitleType());
        $this->assertEquals('TitleMain', $title->getOpusField());
        $this->assertEquals('title', $title->getBibtexField());
        $this->assertEquals('eng', $title->getLanguage());
    }

    public function testGetValue()
    {
        $title = new Title();
        $title->setBibtexField('journal');
        $title->setTitleType('parent');
        $title->setLanguage('deu');

        $value = $title->getValue('Der Titel');

        $this->assertEquals([
            'Language' => 'deu',
            'Value'    => 'Der Titel',
            'Type'     => 'parent',
        ], $value);
    }
}
