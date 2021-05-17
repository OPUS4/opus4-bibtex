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

use Opus\Bibtex\Import\Processor;
use PHPUnit_Framework_TestCase;

use function ksort;

class IdentifierTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $proc        = new Processor();
        $metadata    = [];
        $bibtexBlock = [
            'arxiv' => 'http://papers.ssrn.com/sol3/papers.cfm?abstract_id=9999999',
            'doi'   => '10.2222/j.jbankfin.2222.32.001',
            'issn'  => '1100-0011',
        ];
        $proc->handleRecord($bibtexBlock, $metadata);

        $expected = [
            [
                'Value' => 'http://papers.ssrn.com/sol3/papers.cfm?abstract_id=9999999',
                'Type'  => 'url',
            ],
            [
                'Value' => '10.2222/j.jbankfin.2222.32.001',
                'Type'  => 'doi',
            ],
            [
                'Value' => '1100-0011',
                'Type'  => 'issn',
            ],
        ];

        $this->assertEquals(ksort($expected), ksort($metadata['Identifier']));
    }
}
