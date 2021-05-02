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
 * @package     OpusTest\Bibtex\Import\Configuration
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Bibtex\Import\Configuration;

use Opus\Bibtex\Import\Configuration\FieldMapping;
use Opus\Bibtex\Import\Configuration\ConfigurationManager;
use Opus\Bibtex\Import\Rules\Arxiv;
use Opus\Bibtex\Import\Rules\ConstantValueRule;
use Opus\Bibtex\Import\Rules\Doi;
use Opus\Bibtex\Import\Rules\Isbn;
use Opus\Bibtex\Import\Rules\Issn;
use Opus\Bibtex\Import\Rules\Note;
use Opus\Bibtex\Import\Rules\PageFirst;
use Opus\Bibtex\Import\Rules\PageLast;
use Opus\Bibtex\Import\Rules\PageNumber;
use Opus\Bibtex\Import\Rules\Person;
use Opus\Bibtex\Import\Rules\Ptype;
use Opus\Bibtex\Import\Rules\PublishedYear;
use Opus\Bibtex\Import\Rules\SimpleRule;
use Opus\Bibtex\Import\Rules\SourceData;
use Opus\Bibtex\Import\Rules\SourceDataHash;
use Opus\Bibtex\Import\Rules\Subject;
use Opus\Bibtex\Import\Rules\TitleMain;
use Opus\Bibtex\Import\Rules\TitleParent;
use Opus\Bibtex\Import\Rules\Type;
use Opus\Bibtex\Import\Rules\Umlauts;

class JsonFieldMappingReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFieldMappingConfiguration()
    {
        $mappingConfig = ConfigurationManager::getFieldMapping();

        $defaultMapping = $this->createMappingConfiguration();

        $this->assertEquals($defaultMapping->getName(), $mappingConfig->getName());
        $this->assertEquals($defaultMapping->getDescription(), $mappingConfig->getDescription());
        $this->assertEquals($defaultMapping->getRules(), $mappingConfig->getRules());
    }

    private function createMappingConfiguration()
    {
        return (new FieldMapping())
            ->setName('default')
            ->setDescription('Default BibTeX Mapping Configuration.')
            ->addRule(
                'type',
                new Type()
            )
            ->addRule(
                'ptype',
                new Ptype()
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
                'pageFirst',
                new PageFirst()
            )
            ->addRule(
                'pageLast',
                new PageLast()
            )
            ->addRule(
                'pageNumber',
                new PageNumber()
            )
            ->addRule(
                'publishedYear',
                new PublishedYear()
            )
            ->addRule(
                'issn',
                new Issn()
            )
            ->addRule(
                'isbn',
                new Isbn()
            )
            ->addRule(
                'doi',
                new Doi()
            )
            ->addRule(
                'arxiv',
                new Arxiv()
            )
            ->addRule(
                'titleMain',
                new TitleMain()
            )
            ->addRule(
                'journalTitle',
                (new TitleParent())->setBibtexFieldName('journal')
            )
            ->addRule(
                'bookTitle',
                (new TitleParent())->setBibtexFieldName('booktitle')
            )
            ->addRule(
                'subject',
                new Subject()
            )
            ->addRule(
                'pdfUrl',
                (new Note())
                    ->setBibtexFieldName('pdfurl')
                    ->setMessagePrefix('URL of the PDF: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'slides',
                (new Note())
                    ->setBibtexFieldName('slides')
                    ->setMessagePrefix('URL of the Slides: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'annote',
                (new Note())
                    ->setBibtexFieldName('annote')
                    ->setMessagePrefix('Additional Note: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'summary',
                (new Note())
                    ->setBibtexFieldName('summary')
                    ->setMessagePrefix('URL of the Abstract: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'code',
                (new Note())
                    ->setBibtexFieldName('code')
                    ->setMessagePrefix('URL of the Code: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'poster',
                (new Note())
                    ->setBibtexFieldName('poster')
                    ->setMessagePrefix('URL of the Poster: ')
                    ->setVisibility('public')
            )
            ->addRule(
                'author',
                (new Person())->setBibtexFieldName('author')
            )
            ->addRule(
                'editor',
                (new Person())->setBibtexFieldName('editor')
            )
            ->addRule(
                'sourceData',
                new SourceData()
            )
            ->addRule(
                'sourceDataHash',
                new SourceDataHash()
            )
            ->addRule(
                'language',
                (new ConstantValueRule())->setOpusFieldName('Language')->setValue('eng')
            )
            ->addRule(
                'belongsToBibliography',
                (new ConstantValueRule())->setOpusFieldName('BelongsToBibliography')->setValue('0')
            )
            ->addRule(
                'umlauts',
                new Umlauts()
            );
    }
}
