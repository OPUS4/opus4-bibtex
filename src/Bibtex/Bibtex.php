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
 * @category    BibTex
 * @package     Opus\Bibtex
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex;

use Opus\Bibtex\BibtexRules;


class Bibtex
{
    private $referencetype;

    private $person;
    private $title;
    private $journal;
    private $year;
    private $volume;
    private $number;
    private $pages;
    private $month;
    private $note;

    private $publisher;
    private $series;
    private $adress;
    private $edition;
    private $isbn;

    private $howpublished;
    private $booktitle;
    private $organization;
    private $chapter;
    private $type;
    private $school;
    private $institution;

    public function setData($literatureData)
    {
        foreach ($literatureData as $field => $value) {
            foreach (glob(__DIR__.'/BibtexRules/*.php') as $file)
            {
                require_once $file;
                $class = "Opus\Bibtex\BibtexRules\\" . basename($file, '.php');
                if (class_exists($class))
                {
                    $rule = new $class;
                    $rule->bibtexRule($this, $field, $value);
                }
            }
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $this->deleteBrace($value);
        }
    }

    public function deleteBrace($string)
    {
        if (substr($string,-1, 1) == '}' and substr($string,0, 1) == '{'){
            $string = substr_replace($string, "", -1, 1);
            $string = substr_replace($string, "", 0, 1);
        }
        return $string;
    }

    public function getAllProperties()
    {
        return get_object_vars($this);
    }

}
