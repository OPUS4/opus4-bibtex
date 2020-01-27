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

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $parser = new Parser();
        $parser->fileToArray(__DIR__ . '/resources/testbib.bib');
        $parser->convert();

        $expectedOpusFormat = [
            [
                'BelongsToBibliography' => '0',
                'PublishedYear' => '2006',
                'Language' => 'eng',
                'Type' => 'misc',
                'TitleMain' => [
                    [
                        'Language' => 'eng',
                        'Value' => 'My Article',
                        'Type' => 'main'
                    ]
                ],
                'Person' => [
                    [
                        'FirstName' => 'Jr',
                        'LastName' => 'Nobody',
                        'Role' => 'author'
                    ]
                ],
                'Enrichment' => [
                    ['opus.rawdata' => "@misc{Nobody06,\n       author = \"Nobody, Jr\",\n       title = \"My Article\",\n       year = \"2006\"}"]
                ]
            ],
            [
                'BelongsToBibliography' => '0',
                'Language' => 'eng',
                'TitleMain' => [
                    [
                        'Language' => 'eng',
                        'Value' => 'Cool Stuff: With Apples',
                        'Type' => 'main'
                    ]
                ],
                'Type' => 'article',
                'Enrichment' => [
                    ['opus.rawdata' => "@article{BigReference,\n\tArxiv = {http://papers.ssrn.com/sol3/papers.cfm?abstract_id=9999999},\n\tAuthor = {Disterer, S. and Nobody, C.},\n\tEditor = {Women, Cool and Men, Cool},\n\tDoi = {10.2222/j.jbankfin.2222.32.001},\n\tIssn = {1100-0011},\n\tJournal = {Journal of Cool Stuff},\n\tKeywords = {Cool, {Stuff}},\n\tPages = {1--12},\n\tPdfurl = {http://dx.doi.org/10.2222/j.jbankfin.2222.32.001},\n\tPtype = {journal},\n\tTitle = {{Cool Stuff: With Apples}},\n\tVolume = {32},\n\tYear = {2020},\n\tSlides = {https://app.box.com/s/1231451532132slide},\n\tNumber = {1},\n\tAnnote = {http://www.sciencedirect.com/science/article/pii/123456789\n\tdoi:10.1234/TIT.2020.1234567\n\tarXiv:1234.1233v4},\n\tSummary = {http://www.Forschung.com/blog/research/2020/01/04/Ein-abstract.html},\n\tCode = {https://colab.research.google.com/drive/123456a456},\n\tPoster = {https://app.box.com/s/1231451532132post},\n}"]
                ],
                'Issue' => '1',
                'PageFirst' => '1',
                'PageLast' => '12',
                'PageNumber' => '12',
                'PublishedYear' => '2020',
                'Volume' => '32',
                'TitleParent' => [
                    [
                        'Language' => 'eng',
                        'Value' => 'Journal of Cool Stuff',
                        'Type' => 'parent'
                    ]
                ],
                'Identifier' => [
                    [
                        'Value' => 'http://papers.ssrn.com/sol3/papers.cfm?abstract_id=9999999',
                        'Type' => 'arxiv'
                    ],
                    [
                        'Value' => '10.2222/j.jbankfin.2222.32.001',
                        'Type' => 'doi'
                    ],
                    [
                        'Value' => '1100-0011',
                        'Type' => 'issn'
                    ]
                ],
                'Note' => [
                    [
                        'Visibility' => 'public',
                        'Message' => 'URL of the PDF: http://dx.doi.org/10.2222/j.jbankfin.2222.32.001'
                    ],
                    [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Slides: https://app.box.com/s/1231451532132slide'
                    ],
                    [
                        'Visibility' => 'public',
                        'Message' => "Additional Note: http://www.sciencedirect.com/science/article/pii/123456789\n\tdoi:10.1234/TIT.2020.1234567\n\tarXiv:1234.1233v4"
                    ],
                    [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Abstract: http://www.Forschung.com/blog/research/2020/01/04/Ein-abstract.html'
                    ],
                    [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Code: https://colab.research.google.com/drive/123456a456'
                    ],
                    [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Poster: https://app.box.com/s/1231451532132post'
                    ]
                ],
                'Person' => [
                    [
                        'FirstName' => 'S.',
                        'LastName' => 'Disterer',
                        'Role' => 'author'
                    ],
                    [
                        'FirstName' => 'C.',
                        'LastName' => 'Nobody',
                        'Role' => 'author'
                    ],
                    [
                        'FirstName' => 'Cool',
                        'LastName' => 'Women',
                        'Role' => 'editor'
                    ],
                    [
                        'FirstName' => 'Cool',
                        'LastName' => 'Men',
                        'Role' => 'editor'
                    ]
                ],
                'Subject' => [
                    [
                        'Language' => 'eng',
                        'Type' => 'uncontrolled',
                        'Value' => 'Cool'
                    ],
                    [
                        'Language' => 'eng',
                        'Type' => 'uncontrolled',
                        'Value' => 'Stuff'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedOpusFormat[0], $parser->getOpusFormat()[0]);
        $this->assertEquals($expectedOpusFormat[1], $parser->getOpusFormat()[1]);
    }
}
