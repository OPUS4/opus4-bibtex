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
 *
 * @category    Tests
 * @package     OpusTest\Bibtex\Import\Config
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace OpusTest\Bibtex\Import\Config;

use Opus\Bibtex\Import\Config\BibtexConfigException;
use Opus\Bibtex\Import\Config\BibtexMapping;
use Opus\Bibtex\Import\Config\BibtexService;
use Opus\Bibtex\Import\Config\JsonBibtexMappingReader;
use Opus\Bibtex\Import\Rules\BelongsToBibliography;
use Opus\Bibtex\Import\Rules\ConstantValue;
use Opus\Bibtex\Import\Rules\ConstantValues;
use Opus\Bibtex\Import\Rules\DocumentType;
use Opus\Bibtex\Import\Rules\Language;
use Opus\Bibtex\Import\Rules\Note;
use Opus\Bibtex\Import\Rules\Pages;
use Opus\Bibtex\Import\Rules\Person;
use Opus\Bibtex\Import\Rules\SimpleRule;
use Opus\Bibtex\Import\Rules\TitleParent;
use PHPUnit\Framework\TestCase;

class JsonBibtexMappingReaderTest extends TestCase
{
    public function testGetFieldMappingConfiguration()
    {
        $configService = BibtexService::getInstance();
        $mappingConfig = $configService->getFieldMapping();

        $defaultMapping = $this->createMappingConfiguration();

        $this->assertEquals($defaultMapping->getName(), $mappingConfig->getName());
        $this->assertEquals($defaultMapping->getDescription(), $mappingConfig->getDescription());
        $this->assertEquals($defaultMapping->getRules(), $mappingConfig->getRules());
    }

    /**
     * @return BibtexMapping
     */
    private function createMappingConfiguration()
    {
        return (new BibtexMapping())
            ->setName('default')
            ->setDescription('Default BibTeX Mapping Configuration.')
            ->addRule(
                'documentType',
                (new DocumentType())->setBibtexField('ptype')
            )
            ->addRule(
                'issue',
                new SimpleRule('number', 'Issue')
            )
            ->addRule(
                'volume',
                new SimpleRule('volume', 'Volume')
            )
            ->addRule(
                'pages'
            )
            ->addRule(
                'publishedYear'
            )
            ->addRule(
                'issn'
            )
            ->addRule(
                'isbn'
            )
            ->addRule(
                'doi'
            )
            ->addRule(
                'arxiv'
            )
            ->addRule(
                'titleMain'
            )
            ->addRule(
                'journalTitle',
                (new TitleParent())->setBibtexField('journal')
            )
            ->addRule(
                'bookTitle',
                (new TitleParent())->setBibtexField('booktitle')
            )
            ->addRule(
                'subject'
            )
            ->addRule(
                'pdfUrl',
                (new Note())
                    ->setBibtexField('pdfurl')
                    ->setMessagePrefix('URL of the PDF: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'slides',
                (new Note())
                    ->setBibtexField('slides')
                    ->setMessagePrefix('URL of the Slides: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'annote',
                (new Note())
                    ->setBibtexField('annote')
                    ->setMessagePrefix('Additional Note: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'summary',
                (new Note())
                    ->setBibtexField('summary')
                    ->setMessagePrefix('URL of the Abstract: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'code',
                (new Note())
                    ->setBibtexField('code')
                    ->setMessagePrefix('URL of the Code: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'poster',
                (new Note())
                    ->setBibtexField('poster')
                    ->setMessagePrefix('URL of the Poster: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'author',
                (new Person())->setBibtexField('author')
            )
            ->addRule(
                'editor',
                (new Person())->setBibtexField('editor')
            )
            ->addRule(
                'sourceData'
            )
            ->addRule(
                'sourceDataHash'
            )
            ->addRule(
                'language',
                (new Language())->setValue('eng')
            )
            ->addRule(
                'belongsToBibliography',
                (new BelongsToBibliography())->setValue(false)
            )
            ->addRule(
                'umlauts'
            );
    }

    public function testGetMappingConfigurationFromNull()
    {
        $jsonBibtexMappingReader = new JsonBibtexMappingReader();
        $this->expectException(BibtexConfigException::class);
        $jsonBibtexMappingReader->getMappingConfigurationFromFile(null);
    }

    public function testGetMappingConfigurationFromUnknownFile()
    {
        $jsonBibtexMappingReader = new JsonBibtexMappingReader();
        $this->expectException(BibtexConfigException::class);
        $jsonBibtexMappingReader->getMappingConfigurationFromFile('unknown.json');
    }

    public function testGetMappingConfigurationFromInvalidJsonFile()
    {
        $jsonBibtexMappingReader = new JsonBibtexMappingReader();
        $this->expectException(BibtexConfigException::class);
        $jsonBibtexMappingReader->getMappingConfigurationFromFile(__DIR__ . '/../_files/mapping-invalid.json');
    }

    public function testGetMappingConfigurationFromIncompleteJsonFile()
    {
        $jsonBibtexMappingReader = new JsonBibtexMappingReader();
        $this->expectException(BibtexConfigException::class);
        $jsonBibtexMappingReader->getMappingConfigurationFromFile(__DIR__ . '/../_files/mapping-incomplete.json');
    }

    public function testGetMappingConfigurationFromJsonFile()
    {
        $jsonBibtexMappingReader = new JsonBibtexMappingReader();
        $bibtexMapping           = $jsonBibtexMappingReader->getMappingConfigurationFromFile(__DIR__ . '/../_files/mapping.json');

        $this->assertEquals('test', $bibtexMapping->getName());
        $this->assertEquals('Test BibTeX Mapping Configuration.', $bibtexMapping->getDescription());
        $this->assertCount(6, $bibtexMapping->getRules());

        $ruleInstance = $bibtexMapping->getRules()['testrule'];
        $this->assertInstanceOf(SimpleRule::class, $ruleInstance);
        $this->assertEquals('number', $ruleInstance->getBibtexField());
        $this->assertEquals('Issue', $ruleInstance->getOpusField());

        $ruleInstance = $bibtexMapping->getRules()['pages'];
        $this->assertInstanceOf(Pages::class, $ruleInstance);

        $ruleInstance = $bibtexMapping->getRules()['note'];
        $this->assertInstanceOf(Note::class, $ruleInstance);
        $this->assertEquals('prefix', $ruleInstance->getMessagePrefix());
        $this->assertEquals('private', $ruleInstance->getVisibility());
        $this->assertEquals('summary', $ruleInstance->getBibtexField());
        $this->assertEquals('Note', $ruleInstance->getOpusField());

        $ruleInstance = $bibtexMapping->getRules()['simpleTestRule'];
        $this->assertInstanceOf(SimpleRule::class, $ruleInstance);
        $this->assertEquals('year', $ruleInstance->getBibtexField());
        $this->assertEquals('CompletedYear', $ruleInstance->getOpusField());

        $ruleInstance = $bibtexMapping->getRules()['constantTestRule'];
        $this->assertInstanceOf(ConstantValue::class, $ruleInstance);
        $this->assertEquals('foo', $ruleInstance->getValue());
        $this->assertEquals('ConstantField', $ruleInstance->getOpusField());

        $ruleInstance = $bibtexMapping->getRules()['constantsTestRule'];
        $this->assertInstanceOf(ConstantValues::class, $ruleInstance);
    }
}
