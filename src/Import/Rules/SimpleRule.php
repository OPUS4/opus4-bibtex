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
 * @category    BibTeX
 * @package     Opus\Bibtex\Import\Rules
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Rules;

/**
 * Eine Regel, die ein Metadatenfeld auf Basis des Inhalts eines Felds des BibTeX-Records füllt.
 */
class SimpleRule implements IRule
{
    /**
     * @var string Name des auszuwertenden BibTeX-Felds
     */
    protected $bibtexField;

    /**
     * @var string Name des zu befüllenden OPUS4-Metadatenfelds
     */
    protected $opusField;

    /**
     * @param string $bibtexField Name des auszuwertenden BibTeX-Felds
     * @param string $opusField Name des zu befüllenden OPUS4-Metadatenfelds
     */
    public function __construct($bibtexField = null, string $opusField = null)
    {
        $this->setBibtexField($bibtexField);
        $this->setOpusField($opusField);
        return $this;
    }

    public function apply($bibtexRecord, &$documentMetadata)
    {
        $result = false;
        if (array_key_exists($this->bibtexField, $bibtexRecord)) {
            $value = $this->getValue($bibtexRecord[$this->bibtexField]);
            if (! is_null($value)) {
                $documentMetadata[$this->opusField] = $value;
                $result = true;
            }
        }
        return $result;
    }

    public function getEvaluatedBibTexField()
    {
        return [$this->bibtexField];
    }

    public function getBibtexField()
    {
        return $this->bibtexField;
    }

    public function setBibtexField($bibtexField)
    {
        $this->bibtexField = $bibtexField;
        return $this;
    }

    public function setOpusField($opusField)
    {
        $this->opusField = ucfirst($opusField);
        return $this;
    }

    /**
     * Funktion, die verwendet wird, um den Feldwert für das OPUS4-Metadatenfelds zu bestimmen.
     *
     * @param $value Feldwert aus dem BibTeX-Record
     * @return mixed
     */
    protected function getValue($value)
    {
        return $value;
    }

    protected function deleteBrace($value)
    {
        if (strlen($value) >= 2 && substr($value, 0, 1) == '{' && substr($value, -1, 1) == '}') {
            $value = substr($value, 1, -1);
        }
        return trim($value);
    }
}
