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
 * @package     OpusTest\Bibtex\Import
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import;

use Opus\Bibtex\Import\Config\BibtexService;
use Opus\Bibtex\Import\Parser;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\ParserException;
use Opus\Bibtex\Import\Rules\SourceData;
use Opus\Bibtex\Import\Rules\SourceDataHash;

/**
 * Class ParserTest
 * @package OpusTest\Bibtex\Import
 *
 * TODO Tests sortieren - wo gehören sie wirklich hin?
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessFileSpecialchars()
    {
        $testfile = $this->getPath('specialchars.bib');

        $parser = new Parser($testfile);
        $result = $parser->parse();
        $this->assertCount(1, $result);
        $bibTexRecord = $result[0];
        $this->assertEquals('Müller, Jürgen Jörg', $bibTexRecord['author']);
        $this->assertEquals('Müller, Jürgen Jörg and Müller Ä.', $bibTexRecord['editor']);
        $this->assertEquals('2006', $bibTexRecord['year']);
        $this->assertEquals('misc', $bibTexRecord['type']);
    }

    public function testProcessFileSpecialcharsInvalid()
    {
        $testfile = $this->getPath('specialchars-invalid.bib');

        $parser = new Parser($testfile);
        $result = $parser->parse();
        $this->assertCount(1, $result);
        $bibTexRecord = $result[0];
        $this->assertEquals('M"ullerß, J.', $bibTexRecord['author']);
        $this->assertEquals('Möllerß, J.', $bibTexRecord['editor']);
        $this->assertEquals('Ää Öö Üü ß - "A"a "O"o "U"u', $bibTexRecord['title']);
        $this->assertEquals('2006', $bibTexRecord['year']);
        $this->assertEquals('misc', $bibTexRecord['type']);

        $metadata = [];
        $proc = new Processor();
        $proc->handleRecord($bibTexRecord, $metadata);
        $this->assertPerson('author', 'J.', 'Müllerß', $metadata['Person'][0]);
        $this->assertPerson('editor', 'J.', 'Möllerß', $metadata['Person'][1]);
        $this->assertTitle('main', 'Ää Öö Üü ß - Ää Öö Üü', $metadata['TitleMain'][0]);
    }

    public function testProcessInvalidFile()
    {
        $testfile = $this->getPath('invalid.bib');

        $parser = new Parser($testfile);
        $this->setExpectedException(ParserException::class);
        $parser->parse();
    }

    public function testProcessInvalidUrlFile()
    {
        $testfile = $this->getPath('invalid-url.bib');

        $parser = new Parser($testfile);
        $this->setExpectedException(ParserException::class);
        $parser->parse();
    }

    public function testProcessUnknownFile()
    {
        $testfile = $this->getPath('missing.bib');

        $parser = new Parser($testfile);
        $result = $parser->parse();
        $this->assertEmpty($result);
    }

    public function testProcessPlainBibtex()
    {
        $bibtex =
            '@article{CitekeyArticle,
                author   = "Cohen, P. J.",
                editor   = "Doe and Done",
                title    = "The independence of the continuum hypothesis",
                journal  = "The Foo Journal",
                number   = 42,
                volume   = 13,
                year     = 2021,
                issn     = "1234-5678",
                isbn     = "978-1-23456-789-0",
                doi      = "doi:10.1002/0470841559.ch1",
                arxiv    = "arXiv:1501.00001",
                keywords = "foo,  bar , baz",
                pdfurl   = "http://example.org/pdf/1",
                slides   = "http://example.org/slide/2",
                annote   = "Example Annotation",
                summary  = "http://example.org/abstract/3",
                code     = "http://example.org/code/4",
                poster   = "http://example.org/poster/5"
            }';

        $parser = new Parser($bibtex);
        $result = $parser->parse();
        $this->assertCount(1, $result);

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($result[0], $metadata);

        $this->assertEquals(42, $metadata['Issue']);
        $this->assertEquals(13, $metadata['Volume']);
        $this->assertEquals(2021, $metadata['PublishedYear']);
        $this->assertCount(4, $metadata['Identifier']);
        $this->assertEquals("1234-5678", $metadata['Identifier'][0]['Value']);
        $this->assertEquals("issn", $metadata['Identifier'][0]['Type']);

        $this->assertEquals("978-1-23456-789-0", $metadata['Identifier'][1]['Value']);
        $this->assertEquals("isbn", $metadata['Identifier'][1]['Type']);

        $this->assertEquals("10.1002/0470841559.ch1", $metadata['Identifier'][2]['Value']);
        $this->assertEquals("doi", $metadata['Identifier'][2]['Type']);

        $this->assertEquals("1501.00001", $metadata['Identifier'][3]['Value']);
        $this->assertEquals("arxiv", $metadata['Identifier'][3]['Type']);

        $titleMain = $metadata['TitleMain'];
        $this->assertCount(1, $titleMain);
        $this->assertEquals('main', $titleMain[0]['Type']);
        $this->assertEquals('eng', $titleMain[0]['Language']);
        $this->assertEquals('The independence of the continuum hypothesis', $titleMain[0]['Value']);

        $titleParent = $metadata['TitleParent'];
        $this->assertCount(1, $titleParent);
        $this->assertEquals('parent', $titleParent[0]['Type']);
        $this->assertEquals('eng', $titleParent[0]['Language']);
        $this->assertEquals('The Foo Journal', $titleParent[0]['Value']);

        $subjects = $metadata['Subject'];
        $this->assertCount(3, $subjects);
        $this->assertSubject('foo', $subjects[0]);
        $this->assertSubject('bar', $subjects[1]);
        $this->assertSubject('baz', $subjects[2]);

        $notes = $metadata['Note'];
        $this->assertCount(6, $notes);
        $this->assertNote('URL of the PDF: http://example.org/pdf/1', $notes[0]);
        $this->assertNote('URL of the Slides: http://example.org/slide/2', $notes[1]);
        $this->assertNote('Additional Note: Example Annotation', $notes[2]);
        $this->assertNote('URL of the Abstract: http://example.org/abstract/3', $notes[3]);
        $this->assertNote('URL of the Code: http://example.org/code/4', $notes[4]);
        $this->assertNote('URL of the Poster: http://example.org/poster/5', $notes[5]);

        $persons = $metadata['Person'];
        $this->assertCount(3, $persons);
        $this->assertPerson('author', 'P. J.', 'Cohen', $persons[0]);
        $this->assertPerson('editor', null, 'Doe', $persons[1]);
        $this->assertPerson('editor', null, 'Done', $persons[2]);

        $this->assertEquals('eng', $metadata['Language']);
        $this->assertFalse($metadata['BelongsToBibliography']);

        $enrichments = $metadata['Enrichment'];
        $this->assertCount(2, $enrichments);
        $bibtexRecord = $result[0]['_original'];
        $this->assertEnrichment(
            SourceData::SOURCE_DATA_KEY,
            $bibtexRecord,
            $enrichments[0]
        );
        $this->assertEnrichment(
            SourceDataHash::SOURCE_DATA_HASH_KEY,
            SourceDataHash::HASH_FUNCTION . ':' . (SourceDataHash::HASH_FUNCTION)($bibtexRecord),
            $enrichments[1]
        );
    }

    private function assertEnrichment($keyName, $value, $enrichment)
    {
        $this->assertEquals($keyName, $enrichment['KeyName']);
        $this->assertEquals($value, $enrichment['Value']);
    }

    private function assertPerson($role, $firstName, $lastName, $person)
    {
        $this->assertEquals($role, $person['Role']);
        $this->assertEquals($lastName, $person['LastName']);
        if (is_null($firstName)) {
            $this->assertArrayNotHasKey('FirstName', $person);
        } else {
            $this->assertEquals($firstName, $person['FirstName']);
        }
    }

    private function assertTitle($titleType, $titleValue, $title)
    {
        $this->assertEquals($titleType, $title['Type']);
        $this->assertEquals($titleValue, $title['Value']);
        $this->assertEquals('eng', $title['Language']);
    }

    private function assertSubject($value, $subject)
    {
        $this->assertEquals('uncontrolled', $subject['Type']);
        $this->assertEquals('eng', $subject['Language']);
        $this->assertEquals($value, $subject['Value']);
    }

    private function assertNote($message, $note)
    {
        $this->assertEquals('public', $note['Visibility']);
        $this->assertEquals($message, $note['Message']);
    }

    public function testProcessInvalidPlainBibtex()
    {
        $bibtex =
            "@article{CitekeyArticle,
                author   = \"P. J. Cohen\",
                title    = \"The independence of the continuum hypothesis\",
                year     = 1963";

        $parser = new Parser($bibtex);
        $this->setExpectedException(ParserException::class);
        $parser->parse();
    }

    public function testProcessPlainBibtexTwoRecords()
    {
        $bibtex =
            "@article{CitekeyArticle1,
                author   = \"P. J. Cohen\",
                title    = \"The independence of the continuum hypothesis\",
                year     = 1963
            },
            @article{CitekeyArticle2,
                author   = \"John Doe\",
                title    = \"The continuum hypothesis\",
                year     = 1973
            }";

        $parser = new Parser($bibtex);
        $result = $parser->parse();
        $this->assertCount(2, $result);
    }

    public function testFile()
    {
        $testfile = $this->getPath('testbib.bib');
        $parser = new Parser($testfile);
        $bibTexRecords = $parser->parse();
        $this->assertCount(2, $bibTexRecords);

        $entries = $this->splitBibtex(file_get_contents($testfile));
        $hashFn = SourceDataHash::HASH_FUNCTION;

        $expectedDoc = [
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
                'KeyName' => SourceData::SOURCE_DATA_KEY,
                'Value' => $entries[0]
            ], [
                'KeyName' => SourceDataHash::SOURCE_DATA_HASH_KEY,
                'Value' => $hashFn . ':' . $hashFn($entries[0])
            ]]
        ];
        $this->checkBibTexRecord($bibTexRecords[0], $expectedDoc);

        $expectedDoc = [
            'BelongsToBibliography' => '0',
            'Language' => 'eng',
            'TitleMain' => [[
                'Language' => 'eng',
                'Value' => 'Cool Stuff: With Apples',
                'Type' => 'main'
            ]],
            'Type' => 'article',
            'Enrichment' => [[
                'KeyName' => SourceData::SOURCE_DATA_KEY,
                'Value' => $entries[1]
            ], [
                'KeyName' => SourceDataHash::SOURCE_DATA_HASH_KEY,
                'Value' => $hashFn . ':' . $hashFn($entries[1])
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
                'Type' => 'url'
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
        $this->checkBibTexRecord($bibTexRecords[1], $expectedDoc);
    }

    private function checkBibTexRecord($bibTexRecord, $expectedDoc)
    {
        $proc = new Processor();
        $metadata = [];
        $fieldsEvaluated = $proc->handleRecord($bibTexRecord, $metadata);
        foreach (array_keys($bibTexRecord) as $fieldName) {
            $fieldName = strtolower($fieldName); // BibTex-Parser liefert Feldnamen z.T. mit beginnendem Großbuchstaben
            if (strpos($fieldName, '_') === 0 || $fieldName === 'citation-key' || $fieldName === 'type') {
                // interne Felder des BibTex-Parsers beim Vergleich ignorieren
                continue;
            }
            if ($fieldName === 'unknown') {
                // dieses Feld wurde bei der Ausführung des Mappings nicht ausgewertet
                $this->assertArrayNotHasKey($fieldName, $fieldsEvaluated);
            } else {
                $this->assertContains($fieldName, $fieldsEvaluated);
            }
        }
        $this->assertEquals(ksort($expectedDoc), ksort($metadata));
    }

    private function splitBibtex($bibtex)
    {
        $entries = preg_split('/^@/m', $bibtex, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $entries = array_map(function ($value) {
            return '@' . trim($value);
        }, $entries);

        return $entries;
    }

    public function testDocumentTypeHandling()
    {
        $bibtex =
            '@bar{citekey,
                ptype = "foo"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);

        $documentTypeMapping = BibtexService::getInstance()->getTypeMapping();
        $this->assertEquals($documentTypeMapping->getDefaultType(), $metadata['Type']);
    }

    public function testDocumentTypeHandling1()
    {
        $bibtex =
            '@mastersthesis{citekey,
                ptype = "foo"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);

        $documentTypeMapping = BibtexService::getInstance()->getTypeMapping();
        $this->assertEquals($documentTypeMapping->getOpusType('mastersthesis'), $metadata['Type']);
    }

    public function testDocumentTypeHandling2()
    {
        $bibtex =
            '@article{citekey,
                ptype = "mastersthesis"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);

        $documentTypeMapping = BibtexService::getInstance()->getTypeMapping();
        $this->assertEquals($documentTypeMapping->getOpusType('mastersthesis'), $metadata['Type']);
    }

    public function testDocumentTypeHandling3()
    {
        $bibtex =
            '@book{citekey,
                ptype = "journal"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);

        $documentTypeMapping = BibtexService::getInstance()->getTypeMapping();
        $this->assertEquals($documentTypeMapping->getOpusType('journal'), $metadata['Type']);
    }

    public function testPagesHandling()
    {
        $bibtex =
            '@book{citekey,
                pages = "42"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertEquals(42, $metadata['PageFirst']);
        $this->assertEquals(42, $metadata['PageLast']);
        $this->assertEquals(1, $metadata['PageNumber']);
    }

    public function testPagesHandling1()
    {
        $bibtex =
            '@book{citekey,
                pages = "42--43"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertEquals(42, $metadata['PageFirst']);
        $this->assertEquals(43, $metadata['PageLast']);
        $this->assertEquals(2, $metadata['PageNumber']);
    }

    public function testPagesHandling2()
    {
        $bibtex =
            '@book{citekey,
                pages = "42 - 43"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertEquals(42, $metadata['PageFirst']);
        $this->assertEquals(43, $metadata['PageLast']);
        $this->assertEquals(2, $metadata['PageNumber']);
    }
    public function testPagesHandling3()
    {
        $bibtex =
            '@book{citekey,
                pages = "42--41"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertEquals(42, $metadata['PageFirst']);
        $this->assertEquals(41, $metadata['PageLast']);
        $this->assertArrayNotHasKey('PageNumber', $metadata);
    }

    public function testYearHandling1()
    {
        $bibtex =
            '@article{citekey,
                year = 2021
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertEquals(2021, $metadata['PublishedYear']);
    }

    public function testYearHandling2()
    {
        $bibtex =
            '@article{citekey,
                year = MMXXI
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertArrayNotHasKey('PublishedYear', $metadata);
    }

    public function testYearHandling3()
    {
        $bibtex =
            '@article{citekey,
                year = "2020/1"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertArrayNotHasKey('PublishedYear', $metadata);
    }

    public function testIdentifierDoiHandling()
    {
        $bibtex =
            '@article{citekey,
                doi = "10.1002/0470841559.ch1"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $identifiers = $metadata['Identifier'];
        $this->assertCount(1, $identifiers);
        $this->assertEquals('10.1002/0470841559.ch1', $identifiers[0]['Value']);
        $this->assertEquals('doi', $identifiers[0]['Type']);
    }

    public function testIdentifierArxivHandling()
    {
        $bibtex =
            '@article{citekey,
                arxiv = "https://arxiv.org/abs/2006.00108"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $identifiers = $metadata['Identifier'];
        $this->assertCount(1, $identifiers);
        $this->assertEquals('2006.00108', $identifiers[0]['Value']);
        $this->assertEquals('arxiv', $identifiers[0]['Type']);
    }

    public function testIdentifierArxivHandling1()
    {
        $bibtex =
            '@article{citekey,
                arxiv = "http://arxiv.org/abs/2006.00108"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $identifiers = $metadata['Identifier'];
        $this->assertCount(1, $identifiers);
        $this->assertEquals('2006.00108', $identifiers[0]['Value']);
        $this->assertEquals('arxiv', $identifiers[0]['Type']);
    }

    public function testIdentifierArxivHandling2()
    {
        $bibtex =
            '@article{citekey,
                arxiv = "arxiv:2006.00108"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $identifiers = $metadata['Identifier'];
        $this->assertCount(1, $identifiers);
        $this->assertEquals('2006.00108', $identifiers[0]['Value']);
        $this->assertEquals('arxiv', $identifiers[0]['Type']);
    }

    public function testIdentifierArxivHandling3()
    {
        $bibtex =
            '@article{citekey,
                arxiv = "\url{http://example.org/2006.00108}"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $identifiers = $metadata['Identifier'];
        $this->assertCount(1, $identifiers);
        $this->assertEquals('http://example.org/2006.00108', $identifiers[0]['Value']);
        $this->assertEquals('url', $identifiers[0]['Type']);
    }

    public function testIdentifierArxivHandling4()
    {
        $bibtex =
            '@article{citekey,
                arxiv = "2006.00108"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);
        $identifiers = $metadata['Identifier'];
        $this->assertCount(1, $identifiers);
        $this->assertEquals('2006.00108', $identifiers[0]['Value']);
        $this->assertEquals('url', $identifiers[0]['Type']); // FIXME ist dieser Typ sinnvoll?
    }

    public function testTitleHandling()
    {
        $bibtex =
            '@article{citekey,
                title   = {Foo{\"u} Bar},
                journal = "{Bar \{ Baz \}}"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);

        $titles = $metadata['TitleMain'];
        $this->assertCount(1, $titles);
        $this->assertEquals("Fooü Bar", $titles[0]['Value']);

        $titles = $metadata['TitleParent'];
        $this->assertCount(1, $titles);
        $this->assertEquals("Bar { Baz }", $titles[0]['Value']);
    }

    public function testBookTitleHandling()
    {
        $bibtex =
            '@book{citekey,
                booktitle = {Foo{\"u} Bar}
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);

        $titles = $metadata['TitleParent'];
        $this->assertCount(1, $titles);
        $this->assertEquals("Fooü Bar", $titles[0]['Value']);
        $this->assertEquals("parent", $titles[0]['Type']);
        $this->assertEquals("eng", $titles[0]['Language']);
    }

    public function testPersonNameHandling()
    {
        $bibtex =
            '@article{citekey,
                author = "John Doe and Jane van Doe and Sally M. Doe and M. R. van Doe",
                editor = "Doe, John and van Doe, Jane and Doe, Sally M. and van Doe, M. R."
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[0], $metadata);

        $persons = $metadata['Person'];
        $this->assertCount(8, $persons);
        $this->assertPerson('author', 'John', 'Doe', $persons[0]);
        $this->assertPerson('author', 'Jane', 'van Doe', $persons[1]);
        $this->assertPerson('author', 'Sally M.', 'Doe', $persons[2]);
        $this->assertPerson('author', 'M. R.', 'van Doe', $persons[3]);
        $this->assertPerson('editor', 'John', 'Doe', $persons[4]);
        $this->assertPerson('editor', 'Jane', 'van Doe', $persons[5]);
        $this->assertPerson('editor', 'Sally M.', 'Doe', $persons[6]);
        $this->assertPerson('editor', 'M. R.', 'van Doe', $persons[7]);
    }

    public function testFieldsEvaluated()
    {
        $bibtex =
            '@article{citekey,
                author   = "John Doe",
                year     = 2020,
                doi      = "10.1002/0470841559.ch1",
                keywords = "foo, bar, baz",
                unused   = "unused field"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $fieldsEvaluated = $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertCount(5, $fieldsEvaluated);
        $this->assertContains('type', $fieldsEvaluated);
        $this->assertContains('author', $fieldsEvaluated);
        $this->assertContains('year', $fieldsEvaluated);
        $this->assertContains('doi', $fieldsEvaluated);
        $this->assertContains('keywords', $fieldsEvaluated);

        $bibTexFields = $parser->getBibTexFieldNames($bibTexRecords[0]);
        $unusedFields = array_diff($bibTexFields, $fieldsEvaluated);
        $this->assertCount(2, $unusedFields);
        $this->assertContains('unused', $unusedFields);
        $this->assertContains('citation-key', $unusedFields);
    }

    public function testFieldsEvaluated1()
    {
        $bibtex =
            '@article{citekey,
                ptype     = "book",
                author    = "John Doe",
                unused    = "unused field",
                unusedAlt = "another unused field"
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $fieldsEvaluated = $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertCount(2, $fieldsEvaluated);
        $this->assertTrue(in_array('ptype', $fieldsEvaluated));
        $this->assertTrue(in_array('author', $fieldsEvaluated));

        $bibTexFields = $parser->getBibTexFieldNames($bibTexRecords[0]);
        $unusedFields = array_diff($bibTexFields, $fieldsEvaluated);
        $this->assertCount(4, $unusedFields);
        $this->assertContains('type', $unusedFields);
        $this->assertContains('unused', $unusedFields);
        $this->assertContains('unusedAlt', $unusedFields);
        $this->assertContains('citation-key', $unusedFields);
    }

    /*
    public function testComplexRule()
    {
        $bibtex =
            '@article{citekey,
                author    = "John Doe",
                firstpage = 1,
                lastpage  = 2
            }';

        $parser = new Parser($bibtex);
        $bibTexRecords = $parser->parse();

        $complexRule = new ComplexRule(
            function ($fieldValues, &$documentMetadata) {
                $documentMetadata['PageFirst'] = $fieldValues['firstpage'];
                $documentMetadata['PageLast'] = $fieldValues['lastpage'];
                $documentMetadata['PageNumber'] = intval($documentMetadata['PageLast']) - intval($documentMetadata['PageFirst']);
            },
            ['firstpage', 'lastpage']
        );
        $mappingConfiguration = BibtexService::getFieldMapping();
        $mappingConfiguration->resetRules();
        $mappingConfiguration->addRule('newRule', $complexRule);
        $proc = new Processor($mappingConfiguration);
        $metadata = [];
        $fieldsEvaluated = $proc->handleRecord($bibTexRecords[0], $metadata);

        $this->assertCount(2, $fieldsEvaluated);
        $this->assertContains('firstpage', $fieldsEvaluated);
        $this->assertContains('lastpage', $fieldsEvaluated);

        $bibTexFields = $parser->getBibTexFieldNames($bibTexRecords[0]);
        $unusedFields = array_diff($bibTexFields, $fieldsEvaluated);
        $this->assertCount(3, $unusedFields); // 3 Felder des BibTex-Records werden nicht ausgewertet
        $this->assertContains('type', $unusedFields);
        $this->assertContains('author', $unusedFields);
        $this->assertContains('citation-key', $unusedFields);

        $this->assertCount(3, $metadata);
        $this->assertArrayHasKey('PageFirst', $metadata);
        $this->assertArrayHasKey('PageLast', $metadata);
        $this->assertArrayHasKey('PageNumber', $metadata);
    }*/

    /**
     * Dieser Testcase wurde aus der ursprünglichen Implementierung übernommen.
     */
    public function testProcessor()
    {
        $bibtex = "@misc{Nobody06,\n       author = \"Nobody, Jr\",\n       title = \"My Article\",\n       year = \"2006\"}";
        $hashFunction = SourceDataHash::HASH_FUNCTION;
        $bibtexHash = $hashFunction($bibtex);

        $processor = new Processor();
        $bibtexArray = [
            'type' => 'misc',
            'citation-key' => 'Nobody06',
            'author' => 'Nobody, Jr',
            'title' => 'My Article',
            'year' => '2006',
            '_original' => $bibtex
        ];

        $opus = [
            'BelongsToBibliography' => false,
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
            'Enrichment' => [
                [
                    'KeyName' => SourceData::SOURCE_DATA_KEY,
                    'Value' => $bibtex
                ], [
                    'KeyName' => SourceDataHash::SOURCE_DATA_HASH_KEY,
                    'Value' => $hashFunction . ':' . $bibtexHash
                ]
            ]
        ];

        $metadata = [];
        $processor->handleRecord($bibtexArray, $metadata);
        $this->assertEquals($opus, $metadata);
    }

    private function getPath($fileName)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $fileName;
    }
}
