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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Rules;

use function strtolower;
use function ucfirst;

/**
 * Configurable mapping of titles.
 *
 * TODO verify configured title type against centrally maintained list of valid values
 */
class Title extends AbstractArrayRule
{
    /** @var string Language of title */
    private $language = 'eng';

    /** @var string Type of title for OPUS 4 data model (main,parent,sub,additional) */
    private $titleType = 'main';

    /**
     * Konstruktor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setBibtexField('title');
        $this->setTitleType('main');
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitleType()
    {
        return $this->titleType;
    }

    /**
     * @return $this
     */
    public function setTitleType(string $type)
    {
        $this->titleType = strtolower($type);
        $this->setOpusField('Title' . ucfirst($this->titleType));
        return $this;
    }

    /**
     * Returns array containing title properties.
     *
     * @param string $value BibTeX string for title
     * @return array
     */
    public function getValue($value)
    {
        return [
            'Language' => $this->getLanguage(),
            'Value'    => $this->deleteBrace($value),
            'Type'     => $this->getTitleType(),
        ];
    }
}
