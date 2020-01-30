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
 * @package     Test
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import;

use Opus\Bibtex\Import\Parser;
use Opus\Bibtex\Import\Processor\Rule\RawData;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $testfile = __DIR__ . '/resources/testbib.bib';

        $parser = new Parser();
        $parser->fileToArray($testfile);
        $parser->convert();

        $entries = $this->splitBibtex(file_get_contents($testfile));

        $bibtex1 = $entries[0];
        $bibtexHash1 = RawData::hash($bibtex1);

        $bibtex2 = $entries[1];
        $bibtexHash2 = md5($bibtex2);

        $expectedDoc1 = [
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
            ], [
                'FirstName' => 'J.',
                'LastName' => 'Müller',
                'Role' => 'author'
            ]],
            'Enrichment' => [[
                'KeyName' => RawData::SOURCE_DATA_KEY,
                'Value' => $bibtex1
            ], [
                'KeyName' => RawData::SOURCE_DATA_HASH_KEY,
                'Value' => $bibtexHash1
            ]]
        ];

        $expectedDoc2 = [
            'BelongsToBibliography' => '0',
            'Language' => 'eng',
            'TitleMain' => [[
                'Language' => 'eng',
                'Value' => 'Cool Stuff: With Apples',
                'Type' => 'main'
            ]],
            'Type' => 'article',
            'Enrichment' => [[
                'KeyName' => RawData::SOURCE_DATA_KEY,
                'Value' => $bibtex2
            ], [
                'KeyName' => RawData::SOURCE_DATA_HASH_KEY,
                'Value' => $bibtexHash2
            ]],
            'Issue' => '1',
            'PageFirst' => '1',
            'PageLast' => '12',
            'PageNumber' => '12',
            'PublishedYear' => '2020',
            'Volume' => '32',
            'TitleParent' => [[
                'Language' => 'eng',
                'Value' => 'Journal of Cool Stuff',
                'Type' => 'parent'
            ]],
            'Identifier' => [[
                'Value' => 'http://papers.ssrn.com/sol3/papers.cfm?abstract_id=9999999',
                'Type' => 'arxiv'
            ], [
                'Value' => '10.2222/j.jbankfin.2222.32.001',
                'Type' => 'doi'
            ], [
                'Value' => '1100-0011',
                'Type' => 'issn'
            ]],
            'Note' => [[
                'Visibility' => 'public',
                'Message' => 'URL of the PDF: http://dx.doi.org/10.2222/j.jbankfin.2222.32.001'
            ], [
                'Visibility' => 'public',
                'Message' => 'URL of the Slides: https://app.box.com/s/1231451532132slide'
            ], [
                'Visibility' => 'public',
                'Message' => "Additional Note: http://www.sciencedirect.com/science/article/pii/123456789\n\tdoi:10.1234/TIT.2020.1234567\n\tarXiv:1234.1233v4"
            ], [
                'Visibility' => 'public',
                'Message' => 'URL of the Abstract: http://www.Forschung.com/blog/research/2020/01/04/Ein-abstract.html'
            ], [
                'Visibility' => 'public',
                'Message' => 'URL of the Code: https://colab.research.google.com/drive/123456a456'
            ], [
                'Visibility' => 'public',
                'Message' => 'URL of the Poster: https://app.box.com/s/1231451532132post'
            ]],
            'Person' => [[
                'FirstName' => 'S.',
                'LastName' => 'Disterer',
                'Role' => 'author'
            ], [
                'FirstName' => 'C.',
                'LastName' => 'Nobody',
                'Role' => 'author'
            ], [
                'FirstName' => 'Cool',
                'LastName' => 'Women',
                'Role' => 'editor'
            ], [
                'FirstName' => 'Cool',
                'LastName' => 'Men',
                'Role' => 'editor'
            ]],
            'Subject' => [[
                'Language' => 'eng',
                'Type' => 'uncontrolled',
                'Value' => 'Cool'
            ], [
                'Language' => 'eng',
                'Type' => 'uncontrolled',
                'Value' => 'Stuff'
            ]]
        ];

        $opusFormat = $parser->getOpusFormat();

        $this->assertEquals($expectedDoc1, $opusFormat[0]);
        $this->assertEquals($expectedDoc2, $opusFormat[1]);
    }

    public function testConvertSpecialChars()
    {
        $testfile = __DIR__ . '/resources/specialchars.bib';

        $parser = new Parser();
        $parser->fileToArray($testfile);
        $parser->convert();

        $opus = $parser->getOpusFormat();

        var_dump($opus[0]['Person']);

    }

    public function testPersonsSpecialCharacters()
    {
        $testfile = __DIR__ . '/resources/testbib.bib';

        $parser = new Parser();
        $parser->fileToArray($testfile);
        $parser->convert();

        $this->assertEquals([[
                'FirstName' => 'Jr',
                'LastName' => 'Nobody',
                'Role' => 'author'
            ], [
                'FirstName' => 'J.',
                'LastName' => 'Müller',
                'Role' => 'author'
            ]],
            $parser->getOpusFormat()[0]['Person']
        );
    }

    public function splitBibtex($bibtex)
    {
        $entries = preg_split('/^@/m', $bibtex, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $entries = array_map(function($value) {
            return '@' . trim($value);
        }, $entries);

        return $entries;
    }
}
