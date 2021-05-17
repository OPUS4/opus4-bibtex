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

use Opus\Bibtex\Import\Config\BibtexService;
use PHPUnit_Framework_TestCase;

use function count;

class BibtexServiceTest extends PHPUnit_Framework_TestCase
{
    public function testListAvailableMappingsDefault()
    {
        $bibtexService     = BibtexService::getInstance();
        $availableMappings = $bibtexService->listAvailableMappings();
        $this->assertEquals(1, count($availableMappings));
        $this->assertEquals('default', $availableMappings[0]);
    }

    public function testListAvailableMappingsInvalidIniFile()
    {
        $this->setExpectedException('Exception');
        BibtexService::getInstance(__DIR__ . '/../_files/import-invalid.ini');
    }

    public function testListAvailableMappingsInvalidIniFileAlternative()
    {
        $this->setExpectedException('Exception');
        BibtexService::getInstance(__DIR__ . '/../_files/import-invalid-alt.ini');
    }

    public function testListAvailableMappingsCustom()
    {
        $bibtexService     = BibtexService::getInstance(__DIR__ . '/../_files/import.ini');
        $availableMappings = $bibtexService->listAvailableMappings();
        $this->assertEquals(2, count($availableMappings));
        $this->assertEquals('test', $availableMappings[0]);
        $this->assertEquals('other-mapping', $availableMappings[1]);
    }

    public function testRegisterMapping()
    {
        $bibtexService = BibtexService::getInstance(__DIR__ . '/../_files/import-single.ini');
        $bibtexService->registerMapping('mapping.json');
        $availableMappings = $bibtexService->listAvailableMappings();
        $this->assertEquals(2, count($availableMappings));
        $this->assertEquals('other-mapping', $availableMappings[0]);
        $this->assertEquals('test', $availableMappings[1]);
    }

    public function testGetTypeMapping()
    {
        $bibtexService = BibtexService::getInstance(__DIR__ . '/../_files/import-single.ini');
        $typeMapping   = $bibtexService->getTypeMapping();
        $this->assertEquals('article', $typeMapping->getDefaultType());
        $mappings = $typeMapping->getMappings();
        $this->assertEquals(2, count($mappings));
        $this->assertEquals('opusArticle', $mappings['article']);
        $this->assertEquals('opusBook', $mappings['book']);
        $this->assertEquals('opusArticle', $typeMapping->getOpusType('article'));
        $this->assertEquals('opusBook', $typeMapping->getOpusType('book'));
        $this->assertEquals($typeMapping->getDefaultType(), $typeMapping->getOpusType('unknown'));
    }

    public function testGetMissingDefaultFieldMapping()
    {
        $bibtexService = BibtexService::getInstance(__DIR__ . '/../_files/import-single.ini');
        $this->setExpectedException('Exception');
        $bibtexService->getFieldMapping();
    }

    public function testGetDefaultFieldMapping()
    {
        $bibtexService = BibtexService::getInstance();
        $fieldMapping  = $bibtexService->getFieldMapping();
        $this->assertEquals('default', $fieldMapping->getName());
        $this->assertEquals('Default BibTeX Mapping Configuration.', $fieldMapping->getDescription());
        $this->assertFalse(empty($fieldMapping->getRules()));
    }

    public function testGetFieldMappingSingle()
    {
        $bibtexService = BibtexService::getInstance(__DIR__ . '/../_files/import-single.ini');
        $fieldMapping  = $bibtexService->getFieldMapping('other-mapping');
        $this->assertEquals('other-mapping', $fieldMapping->getName());
        $this->assertEquals('Another Test BibTeX Mapping Configuration.', $fieldMapping->getDescription());
        $this->assertFalse(empty($fieldMapping->getRules()));
    }

    public function testGetFieldMapping()
    {
        $bibtexService = BibtexService::getInstance(__DIR__ . '/../_files/import.ini');
        $fieldMapping  = $bibtexService->getFieldMapping('other-mapping');
        $this->assertEquals('other-mapping', $fieldMapping->getName());
        $this->assertEquals('Another Test BibTeX Mapping Configuration.', $fieldMapping->getDescription());
        $this->assertFalse(empty($fieldMapping->getRules()));

        $fieldMapping = $bibtexService->getFieldMapping('test');
        $this->assertEquals('test', $fieldMapping->getName());
        $this->assertEquals('Test BibTeX Mapping Configuration.', $fieldMapping->getDescription());
        $this->assertFalse(empty($fieldMapping->getRules()));
    }
}
