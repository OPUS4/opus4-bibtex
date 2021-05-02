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
    protected $bibtexFieldName;

    /**
     * @var string Name des zu befüllenden OPUS4-Metadatenfelds
     */
    protected $opusFieldName;

    /**
     * @var optionale Funktion, die verwendet wird, um den Feldwert für das OPUS4-Metadatenfelds zu bestimmen
     */
    protected $fn;

    /**
     * @param string $bibtexFieldName Name des auszuwertenden BibTeX-Felds
     * @param string $opusFieldName Name des zu befüllenden OPUS4-Metadatenfelds
     */
    public function __construct($bibtexFieldName = null, string $opusFieldName = null)
    {
        $this->setBibtexFieldName($bibtexFieldName);
        $this->setOpusFieldName($opusFieldName);
        return $this;
    }

    public function apply($bibtexRecord, &$documentMetadata)
    {
        $result = false;
        if (array_key_exists($this->bibtexFieldName, $bibtexRecord)) {
            $value = $bibtexRecord[$this->bibtexFieldName];
            if (! is_null($this->fn)) {
                $value = ($this->fn)($value);
            }
            if (! is_null($value)) {
                $documentMetadata[$this->opusFieldName] = $value;
                $result = true;
            }
        }
        return $result;
    }

    public function getEvaluatedBibTexField()
    {
        return [$this->bibtexFieldName];
    }

    public function setBibtexFieldName($bibtexFieldName)
    {
        $this->bibtexFieldName = $bibtexFieldName;
        return $this;
    }

    public function setOpusFieldName($opusFieldName)
    {
        $this->opusFieldName = ucfirst($opusFieldName);
    }

    public function setFn($fn)
    {
        $this->fn = $fn;
    }

    protected function deleteBrace($value)
    {
        if (strlen($value) >= 2 && substr($value, 0, 1) == '{' && substr($value, -1, 1) == '}') {
            $value = substr($value, 1, -1);
        }
        return trim($value);
    }
}
