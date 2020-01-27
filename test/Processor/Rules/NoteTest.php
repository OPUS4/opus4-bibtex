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
 * @package     OpusTest\Processor\Rules
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Processor\Rules;

use Opus\Bibtex\Import\Processor\Rules\Note;

class NoteTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $rule = new Note();
        $bibtexBlock = [
            'pdfurl' => 'http://dx.doi.org/10.2222/j.jbankfin.2222.32.001',
            'slides' => 'https://app.box.com/s/1231451532132slide',
            'summary' => 'http://www.Forschung.com/blog/research/2020/01/04/Ein-abstract.html',
            'code' => 'https://colab.research.google.com/drive/123456a456',
            'poster' => 'https://app.box.com/s/1231451532132post',
            'annote' => 'http://www.sciencedirect.com/science/article/pii/123456789
	doi:10.1234/TIT.2020.1234567
	arXiv:1234.1233v4'
        ];

        $return = $rule->process(
            'pdfurl',
            'http://dx.doi.org/10.2222/j.jbankfin.2222.32.001',
            $bibtexBlock
        );

        $expected = [
            true,
            'Note',
            [
                [
                    'Visibility' => 'public',
                    'Message' => 'URL of the PDF: http://dx.doi.org/10.2222/j.jbankfin.2222.32.001'
                ],
                [
                    'Visibility' => 'public',
                    'Message' => 'URL of the Slides: https://app.box.com/s/1231451532132slide'
                ],
                [
                    'Visibility' => 'public',
                    'Message' => 'URL of the Abstract: http://www.Forschung.com/blog/research/2020/01/04/Ein-abstract.html'
                ],
                [
                    'Visibility' => 'public',
                    'Message' => 'URL of the Code: https://colab.research.google.com/drive/123456a456'
                ],
                [
                    'Visibility' => 'public',
                    'Message' => 'URL of the Poster: https://app.box.com/s/1231451532132post'
                ],

                [
                    'Visibility' => 'public',
                    'Message' => "Additional Note: http://www.sciencedirect.com/science/article/pii/123456789\n\tdoi:10.1234/TIT.2020.1234567\n\tarXiv:1234.1233v4"
                ],
            ],
        ];

        $this->assertEquals($expected, $return);
    }
}
