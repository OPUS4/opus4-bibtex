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

namespace OpusTest\Bibtex\Import\Rules;

use Opus\Bibtex\Import\Config\BibtexMapping;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\Enrichment;
use PHPUnit\Framework\TestCase;

class EnrichmentTest extends TestCase
{
    public function testApply()
    {
        $rule = new Enrichment();
        $rule->setBibtexField('localId');
        $rule->setEnrichmentKey('local-id');

        $bibtex   = [
            'localId' => '1234',
        ];
        $metadata = [];

        $rule->apply($bibtex, $metadata);

        $this->assertEquals([
            'Enrichment' => [
                [
                    'KeyName' => 'local-id',
                    'Value'   => '1234',
                ],
            ],
        ], $metadata);
    }

    public function testMappingMultipleBibtexFieldsToSameEnrichment()
    {
        $rule1 = new Enrichment();
        $rule1->setBibtexField('localid1');
        $rule1->setEnrichmentKey('local-id');

        $rule2 = new Enrichment();
        $rule2->setBibtexField('localid2');
        $rule2->setEnrichmentKey('local-id');

        $mapping = new BibtexMapping();
        $mapping->addRule('LocalId1', $rule1);
        $mapping->addRule('LocalId2', $rule2);

        $proc = new Processor($mapping);

        $bibtex   = [
            'localId1' => '1234',
            'localId2' => '2345',
        ];
        $metadata = [];

        $processed = $proc->handleRecord($bibtex, $metadata);

        $this->assertEquals(['localid1', 'localid2'], $processed);

        $this->assertEquals([
            'Enrichment' => [
                [
                    'KeyName' => 'local-id',
                    'Value'   => '1234',
                ],
                [
                    'KeyName' => 'local-id',
                    'Value'   => '2345',
                ],
            ],
        ], $metadata);
    }
}
