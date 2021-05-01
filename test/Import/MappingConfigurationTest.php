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

use Opus\Bibtex\Import\DefaultMappingConfiguration;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\ConstantValueRule;
use Opus\Bibtex\Import\Rules\SimpleRule;

class MappingConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function testUpdateRule()
    {
        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->updateRule(
            'publishedYear',
            new SimpleRule('year', 'completedYear')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('2019', $metadata['CompletedYear']);
        $this->assertArrayNotHasKey('PublishedYear', $metadata);
    }

    public function testAddRuleOverwrite()
    {
        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->addRule(
            'publishedYear',
            new SimpleRule('year', 'completedYear')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('2019', $metadata['CompletedYear']);
        $this->assertArrayNotHasKey('PublishedYear', $metadata);
    }

    public function testAddRule()
    {
        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->addRule(
            'completedYear',
            new SimpleRule('year', 'completedYear')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('2019', $metadata['CompletedYear']);
        $this->assertEquals('2019', $metadata['PublishedYear']);
    }

    public function testResetRule()
    {
        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->resetRules();
        $mappingConf->addRule(
            'completedYear',
            new SimpleRule('year', 'completedYear')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('2019', $metadata['CompletedYear']);
        $this->assertArrayNotHasKey('PublishedYear', $metadata);
    }

    public function testRemoveRule()
    {
        $mappingConf = new DefaultMappingConfiguration();
        $mappingConf->removeRule('publishedYear');
        $mappingConf->addRule(
            'completedYear',
            new SimpleRule('year', 'completedYear')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('2019', $metadata['CompletedYear']);
        $this->assertArrayNotHasKey('PublishedYear', $metadata);

        $mappingConf->removeRule('completedYear');
        $mappingConf->updateRule(
            'publishedYear',
            new SimpleRule('year', 'publishedYear')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('2019', $metadata['PublishedYear']);
        $this->assertArrayNotHasKey('CompletedYear', $metadata);
    }

    public function testPrependRule()
    {
        $mappingConf = new DefaultMappingConfiguration();

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('2019', $metadata['PublishedYear']);

        $mappingConf->prependRule(
            'secondRule',
            (new ConstantValueRule())->setOpusFieldName('PublishedYear')->setValue('1970')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('1970', $metadata['PublishedYear']);

        $mappingConf->prependRule(
            'firstRule',
            (new ConstantValueRule())->setOpusFieldName('PublishedYear')->setValue('1870')
        );

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('1870', $metadata['PublishedYear']);

        $mappingConf->removeRule('firstRule');

        $proc = new Processor($mappingConf);
        $metadata = [];
        $proc->handleRecord(['Year' => '2019'], $metadata);

        $this->assertEquals('1970', $metadata['PublishedYear']);
    }
}