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
 * @package     Test/Processor
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Processor;

use Opus\Bibtex\Import\Processor\Processor;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $processor = new Processor();
        $bibtex = [
            'type' => 'misc',
            'citation-key' => 'Nobody06',
            'author' => 'Nobody, Jr',
            'title' => 'My Article',
            'year' => '2006',
            '_original' => "@misc{Nobody06,
       author = \"Nobody, Jr\",
       title = \"My Article\",
       year = \"2006\"}"
        ];

        $opus = [
            'BelongsToBibliography' => '0',
            'PublishedYear' => '2006',
            'Language' => 'eng',
            'Type' => 'misc',
            'TitleMain' => [[
                'Language' => 'eng',
                'Value' => 'My Article',
                'Type' => 'main'
            ]],
            'Person' => [[
                'FirstName' => 'Jr',
                'LastName' => 'Nobody',
                'Role' => 'author'
            ]],
            'Enrichment' => [[
                'KeyName' => 'opus.rawdata',
                'Value' => "@misc{Nobody06,\n       author = \"Nobody, Jr\",\n       title = \"My Article\",\n       year = \"2006\"}"
            ]]
        ];

        $this->assertEquals($opus, $processor->convertBibtexToOpus($bibtex));
    }
}
