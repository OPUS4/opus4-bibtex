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
 * @package     OpusTest\Bibtex\Import\Rules
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace OpusTest\Bibtex\Import\Rules;

use Opus\Bibtex\Import\Config\DocumentTypeMapping;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\DocumentType;
use PHPUnit\Framework\TestCase;

use function count;

class DocumentTypeTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [['ptype' => 'conference'], 'conferenceobject'],
            [['ptype' => 'journal'], 'article'],
            [['type' => 'article'], 'article'],
        ];
    }

    /**
     * Test Mapping of document types.
     *
     * @param string $arg Value to check given by the data provider
     * @param string $res expected mapping-result
     * @return void
     * @dataProvider dataProvider
     */
    public function testProcessMapping($arg, $res)
    {
        $proc     = new Processor();
        $metadata = [];
        $proc->handleRecord($arg, $metadata);
        $this->assertEquals($res, $metadata['Type']);
    }

    public function testProcessTwoInfos()
    {
        $bibtexBlock = [
            'ptype' => 'conference',
            'type'  => 'article',
        ];

        $proc     = new Processor();
        $metadata = [];
        $proc->handleRecord($bibtexBlock, $metadata);
        $this->assertEquals('conferenceobject', $metadata['Type']);
    }

    public function testDefaultValueInTypeField()
    {
        $docType = new DocumentType();
        $docType->setBibtexField('customType');
        $docType->setOpusField('type');
        $typeMapping = new DocumentTypeMapping();
        $typeMapping->setDefaultType('defaultOpusType');
        $typeMapping->setMapping('bibtexType', 'opusType');
        $docType->setDocumentTypeMapping($typeMapping);

        $bibtexBlock = [
            'type' => 'article',
        ];
        $this->assertTypeField($docType, $bibtexBlock, $typeMapping->getDefaultType());
    }

    public function testDefaultValueInCustomTypeField()
    {
        $docType = new DocumentType();
        $docType->setBibtexField('customType');
        $docType->setOpusField('type');
        $typeMapping = new DocumentTypeMapping();
        $typeMapping->setDefaultType('defaultOpusType');
        $typeMapping->setMapping('bibtexType', 'opusType');
        $docType->setDocumentTypeMapping($typeMapping);

        $bibtexBlock = [
            'customType' => 'conference',
            'type'       => 'article',
        ];
        $this->assertTypeField($docType, $bibtexBlock, $typeMapping->getDefaultType());
    }

    public function testMappedValueInCustomTypeField()
    {
        $docType = new DocumentType();
        $docType->setBibtexField('customType');
        $docType->setOpusField('type');
        $typeMapping = new DocumentTypeMapping();
        $typeMapping->setDefaultType('defaultOpusType');
        $typeMapping->setMapping('bibtexType', 'opusType');
        $docType->setDocumentTypeMapping($typeMapping);

        $bibtexBlock = [
            'customType' => 'bibtexType',
            'type'       => 'article',
        ];
        $this->assertTypeField($docType, $bibtexBlock, $typeMapping->getOpusType('bibtexType'));
    }

    public function testMappedValueInTypeField()
    {
        $docType = new DocumentType();
        $docType->setBibtexField('customType');
        $docType->setOpusField('type');
        $typeMapping = new DocumentTypeMapping();
        $typeMapping->setDefaultType('defaultOpusType');
        $typeMapping->setMapping('bibtexType', 'opusType');
        $docType->setDocumentTypeMapping($typeMapping);

        $bibtexBlock = [
            'type' => 'bibtexType',
        ];
        $this->assertTypeField($docType, $bibtexBlock, $typeMapping->getOpusType('bibtexType'));
    }

    public function testMissingTypeField()
    {
        $docType = new DocumentType();
        $docType->setBibtexField('customType');
        $docType->setOpusField('type');
        $typeMapping = new DocumentTypeMapping();
        $typeMapping->setDefaultType('defaultOpusType');
        $typeMapping->setMapping('bibtexType', 'opusType');
        $docType->setDocumentTypeMapping($typeMapping);

        $docType->apply([], $metadata);
        $this->assertEmpty($metadata);
    }

    /**
     * @param string $docType Document type
     * @param array $bibtexBlock BibTeX data
     * @param string $expectedType Expected document type
     */
    private function assertTypeField($docType, $bibtexBlock, $expectedType)
    {
        $metadata = [];
        $docType->apply($bibtexBlock, $metadata);
        $this->assertEquals(1, count($metadata));
        $this->assertArrayHasKey('Type', $metadata);
        $this->assertEquals($expectedType, $metadata['Type']);
    }
}
