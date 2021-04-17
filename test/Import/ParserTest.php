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
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import;

use Opus\Bibtex\Import\AbstractMappingConfiguration;
use Opus\Bibtex\Import\DefaultMappingConfiguration;
use Opus\Bibtex\Import\Parser;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\ParserException;
use Opus\Bibtex\Import\Rules\ComplexRule;
use Opus\Bibtex\Import\Rules\DocumentTypeMapping;

/**
 * Class ParserTest
 * @package OpusTest\Bibtex\Import
 *
 * TODO Tests sortieren - wo gehören sie wirklich hin?
 */
class ImprovedParserTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessFile()
    {
        $testfile = __DIR__ . '/resources/specialchars.bib';

        $parser = new Parser($testfile);
        $result = $parser->parse();
        $this->assertCount(1, $result);
        $bibTexRecord = $result[0];
        $this->assertEquals('Müller, J.', $bibTexRecord['author']);
        $this->assertEquals('My Article', $bibTexRecord['title']);
        $this->assertEquals('2006', $bibTexRecord['year']);
        $this->assertEquals('misc', $bibTexRecord['type']);
    }

    public function testProcesInvalidFile()
    {
        $testfile = __DIR__ . '/resources/invalid.bib';

        $parser = new Parser($testfile);
        $this->setExpectedException(ParserException::class);
        $parser->parse();
    }

    public function testProcesInvalidUrlFile()
    {
        $testfile = __DIR__ . '/resources/invalid-url.bib';

        $parser = new Parser($testfile);
        $this->setExpectedException(ParserException::class);
        $parser->parse();
    }

    public function testProcesUnknownFile()
    {
        $testfile = __DIR__ . '/resources/missing.bib';

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
        $this->assertEquals('0', $metadata['BelongsToBibliography']);

        $enrichments = $metadata['Enrichment'];
        $this->assertCount(2, $enrichments);
        $bibtexRecord = $result[0]['_original'];
        $this->assertEnrichment(
            AbstractMappingConfiguration::SOURCE_DATA_KEY,
            $bibtexRecord,
            $enrichments[0]
        );
        $this->assertEnrichment(
            AbstractMappingConfiguration::SOURCE_DATA_HASH_KEY,
            AbstractMappingConfiguration::HASH_FUNCTION . ':' . (AbstractMappingConfiguration::HASH_FUNCTION)($bibtexRecord),
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
        $parser = new Parser(__DIR__ . '/resources/testbib.bib');
        $bibTexRecords = $parser->parse();

        $proc = new Processor();
        $metadata = [];
        $proc->handleRecord($bibTexRecords[1], $metadata);
        // FIXME
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
        $this->assertEquals(DocumentTypeMapping::$DEFAULT_OPUS_TYPE, $metadata['Type']);
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
        $this->assertEquals(DocumentTypeMapping::$MAPPING['mastersthesis'], $metadata['Type']);
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
        $this->assertEquals(DocumentTypeMapping::$MAPPING['mastersthesis'], $metadata['Type']);
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
        $docMetadata = $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertEquals(DocumentTypeMapping::$MAPPING['journal'], $metadata['Type']);
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
        $docMetadata = $proc->handleRecord($bibTexRecords[0], $metadata);
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
        $this->assertContains('ptype', $fieldsEvaluated);
        $this->assertContains('author', $fieldsEvaluated);

        $bibTexFields = $parser->getBibTexFieldNames($bibTexRecords[0]);
        $unusedFields = array_diff($bibTexFields, $fieldsEvaluated);
        $this->assertCount(4, $unusedFields);
        $this->assertContains('unused', $unusedFields);
        $this->assertContains('unusedAlt', $unusedFields);
        $this->assertContains('citation-key', $unusedFields);
        $this->assertContains('type', $unusedFields);
    }

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
            ['firstpage', 'lastpage'],
            function ($fieldValues, &$documentMetadata) {
                $documentMetadata['PageFirst'] = $fieldValues['firstpage'];
                $documentMetadata['PageLast'] = $fieldValues['lastpage'];
            }
        );
        $mappingConfiguration = new DefaultMappingConfiguration();
        $mappingConfiguration->appendRule($complexRule);
        $proc = new Processor($mappingConfiguration);
        $metadata = [];
        $fieldsEvaluated = $proc->handleRecord($bibTexRecords[0], $metadata);
        $this->assertCount(4, $fieldsEvaluated);
        $this->assertContains('type', $fieldsEvaluated);
        $this->assertContains('firstpage', $fieldsEvaluated);
        $this->assertContains('lastpage', $fieldsEvaluated);
        $this->assertContains('author', $fieldsEvaluated);

        $bibTexFields = $parser->getBibTexFieldNames($bibTexRecords[0]);
        $unusedFields = array_diff($bibTexFields, $fieldsEvaluated);
        $this->assertCount(1, $unusedFields);
        $this->assertContains('citation-key', $unusedFields);
    }
}
