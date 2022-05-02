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
 * @copyright   Copyright (c) 2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Config;

use Opus\Bibtex\Import\Config\BibtexMapping;
use Opus\Bibtex\Import\Config\BibtexService;
use Opus\Bibtex\Import\Config\DocumentTypeMapping;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\DocumentType;
use Opus\Common\Config;
use PHPUnit\Framework\TestCase;
use Zend_Config;

class DocumentTypeMappingTest extends TestCase
{
    public function testDefaultMapping()
    {
        $typeMapping = new DocumentTypeMapping();
        $typeMapping->setDefaultType('defaultType');

        $mappingConf = new BibtexMapping();
        $mappingConf->addRule('type', (new DocumentType())->setDocumentTypeMapping($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata  = [];
        $processor->handleRecord(['type' => 'unknownType'], $metadata);

        $this->assertEquals($typeMapping->getDefaultType(), $metadata['Type']);
        $this->assertEquals('defaultType', $metadata['Type']);
    }

    public function testUnsetDefaultMapping()
    {
        $typeMapping = new DocumentTypeMapping();
        $typeMapping->setDefaultType(null);

        $mappingConf = new BibtexMapping();
        $mappingConf
            ->resetRules()
            ->addRule('type', (new DocumentType())->setDocumentTypeMapping($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata  = [];
        $processor->handleRecord(['type' => 'unknownType'], $metadata);

        $this->assertArrayNotHasKey('Type', $metadata);
        $this->assertNull($typeMapping->getDefaultType());
    }

    public function testMapping()
    {
        $typeMapping = new DocumentTypeMapping();
        $typeMapping
            ->setDefaultType('defaultType')
            ->setMapping('foo', 'bar');

        $mappingConf = new BibtexMapping();
        $mappingConf
            ->resetRules()
            ->addRule('type', (new DocumentType())->setDocumentTypeMapping($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata  = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getOpusType('foo'), $metadata['Type']);
        $this->assertEquals('bar', $metadata['Type']);
    }

    public function testMappingTwice()
    {
        $typeMapping = new DocumentTypeMapping();
        $typeMapping
            ->setDefaultType('defaultType')
            ->setMapping('foo', 'bar')
            ->setMapping('foo', 'baz'); // Mapping wird überschrieben

        $mappingConf = new BibtexMapping();
        $mappingConf
            ->resetRules()
            ->addRule('type', (new DocumentType())->setDocumentTypeMapping($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata  = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getOpusType('foo'), $metadata['Type']);
        $this->assertEquals('baz', $metadata['Type']);
    }

    public function testRemoveUnkownMapping()
    {
        $typeMapping = new DocumentTypeMapping();
        $typeMapping
            ->setDefaultType('defaultType')
            ->setMapping('foo', 'bar')
            ->removeMapping('baz');

        $mappingConf = new BibtexMapping();
        $mappingConf
            ->resetRules()
            ->addRule('type', (new DocumentType())->setDocumentTypeMapping($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata  = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getOpusType('foo'), $metadata['Type']);
        $this->assertEquals('bar', $metadata['Type']);
    }

    public function testRemoveMapping()
    {
        $typeMapping = new DocumentTypeMapping();
        $typeMapping
            ->setDefaultType('defaultType')
            ->setMapping('foo', 'bar')
            ->removeMapping('foo');

        $mappingConf = new BibtexMapping();
        $mappingConf
            ->resetRules()
            ->addRule('type', (new DocumentType())->setDocumentTypeMapping($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata  = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getOpusType('foo'), $metadata['Type']);
        $this->assertEquals($typeMapping->getDefaultType(), $metadata['Type']);
        $this->assertEquals('defaultType', $metadata['Type']);
    }

    public function testClearMapping()
    {
        $typeMapping = new DocumentTypeMapping();
        $typeMapping
            ->setDefaultType('defaultType')
            ->setMapping('foo', 'bar')
            ->clearMapping();

        $mappingConf = new BibtexMapping();
        $mappingConf
            ->resetRules()
            ->addRule('type', (new DocumentType())->setDocumentTypeMapping($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata  = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getOpusType('foo'), $metadata['Type']);
        $this->assertEquals($typeMapping->getDefaultType(), $metadata['Type']);
        $this->assertEquals('defaultType', $metadata['Type']);
    }

    public function testGetTypeMapping()
    {
        $documentTypeMapping = new DocumentTypeMapping();
        $documentTypeMapping
            // dieser OPUS-Dokumenttyp wird immer dann verwendet, wenn kein Mapping für den aus dem BibTeX-Record
            // abgeleiteten Record-Typ vorliegt.
            ->setDefaultType('misc')

            ->setMapping('article', 'article')
            ->setMapping('book', 'book')
            ->setMapping('booklet', 'bookpart')
            ->setMapping('conference', 'conferenceobject')
            ->setMapping('inbook', 'bookpart')
            ->setMapping('incollection', 'bookpart')
            ->setMapping('inproceedings', 'article')
            ->setMapping('manual', 'article')
            ->setMapping('mastersthesis', 'masterthesis')
            ->setMapping('misc', 'misc')
            ->setMapping('phdthesis', 'doctoralthesis')
            ->setMapping('proceedings', 'conferenceobject')
            ->setMapping('techreport', 'report')
            ->setMapping('unpublished', 'workingpaper')
            // Mapping von nicht Standard BibTeX-Typen
            ->setMapping('journal', 'article');

        $documentTypeMappingFromConfig = BibtexService::getInstance()->getTypeMapping();
        $this->assertEquals($documentTypeMapping->getDefaultType(), $documentTypeMappingFromConfig->getDefaultType());
        $this->assertEquals($documentTypeMapping->getMappings(), $documentTypeMappingFromConfig->getMappings());
    }

    public function testCustomMapping()
    {
        $customType = 'report';

        $mapping = new DocumentTypeMapping();
        $mapping->setMapping('manual', 'article');

        $this->assertEquals('article', $mapping->getOpusType('manual'));

        Config::set(new Zend_Config([
            'bibtex' => ['entryTypes' => ['manual' => $customType]],
        ]));

        $this->assertEquals($customType, $mapping->getOpusType('manual'));
    }

    public function testCustomDefaultMapping()
    {
        $defaultType = 'defaultType';

        $mapping = new DocumentTypeMapping();
        $mapping->setDefaultType($defaultType);

        $this->assertEquals($defaultType, $mapping->getDefaultType());
        $this->assertEquals($defaultType, $mapping->getOpusType('article'));

        Config::set(new Zend_Config([
            'bibtex' => ['defaultDocumentType' => 'article'],
        ]));

        $this->assertEquals('article', $mapping->getDefaultType());
        $this->assertEquals('article', $mapping->getOpusType('book'));
    }
}
