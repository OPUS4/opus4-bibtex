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
 * @package     OpusTest\Bibtex\Import\Rules\DocumentType
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Rules\DocumentType;

use Opus\Bibtex\Import\DefaultMappingConfiguration;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\DocumentType\DefaultDocumentTypeMapping;
use Opus\Bibtex\Import\Rules\Type;

class DocumentTypeMappingTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultMapping()
    {
        $typeMapping = new DefaultDocumentTypeMapping();
        $typeMapping->setDefaultType('defaultType');

        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule('type', new Type($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata = [];
        $processor->handleRecord(['type' => 'unknownType'], $metadata);

        $this->assertEquals($typeMapping->getDefaultType(), $metadata['Type']);
        $this->assertEquals('defaultType', $metadata['Type']);
    }

    public function testUnsetDefaultMapping()
    {
        $typeMapping = new DefaultDocumentTypeMapping();
        $typeMapping->setDefaultType(null);

        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule('type', new Type($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata = [];
        $processor->handleRecord(['type' => 'unknownType'], $metadata);

        $this->assertArrayNotHasKey('Type', $metadata);
        $this->assertNull($typeMapping->getDefaultType());
    }

    public function testMapping()
    {
        $typeMapping = new DefaultDocumentTypeMapping();
        $typeMapping->setDefaultType('defaultType');
        $typeMapping->setMapping('foo', 'bar');

        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule('type', new Type($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getMapping('foo'), $metadata['Type']);
        $this->assertEquals('bar', $metadata['Type']);
    }

    public function testMappingTwice()
    {
        $typeMapping = new DefaultDocumentTypeMapping();
        $typeMapping->setDefaultType('defaultType');
        $typeMapping->setMapping('foo', 'bar');
        $typeMapping->setMapping('foo', 'baz'); // Mapping wird überschrieben

        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule('type', new Type($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getMapping('foo'), $metadata['Type']);
        $this->assertEquals('baz', $metadata['Type']);
    }

    public function testRemoveUnkownMapping()
    {
        $typeMapping = new DefaultDocumentTypeMapping();
        $typeMapping->setDefaultType('defaultType');
        $typeMapping->setMapping('foo', 'bar');
        $typeMapping->removeMapping('baz');

        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule('type', new Type($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getMapping('foo'), $metadata['Type']);
        $this->assertEquals('bar', $metadata['Type']);
    }

    public function testRemoveMapping()
    {
        $typeMapping = new DefaultDocumentTypeMapping();
        $typeMapping->setDefaultType('defaultType');
        $typeMapping->setMapping('foo', 'bar');
        $typeMapping->removeMapping('foo');

        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule('type', new Type($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getMapping('foo'), $metadata['Type']);
        $this->assertEquals($typeMapping->getDefaultType(), $metadata['Type']);
        $this->assertEquals('defaultType', $metadata['Type']);
    }

    public function testClearMapping()
    {
        $typeMapping = new DefaultDocumentTypeMapping();
        $typeMapping->setDefaultType('defaultType');
        $typeMapping->setMapping('foo', 'bar');
        $typeMapping->clearMapping();

        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule('type', new Type($typeMapping));

        $processor = new Processor($mappingConf);
        $metadata = [];
        $processor->handleRecord(['type' => 'foo'], $metadata);

        $this->assertEquals($typeMapping->getMapping('foo'), $metadata['Type']);
        $this->assertEquals($typeMapping->getDefaultType(), $metadata['Type']);
        $this->assertEquals('defaultType', $metadata['Type']);
    }
}