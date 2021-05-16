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
 * @category    BibTeX
 * @package     Opus\Bibtex\Import\Rules
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import\Rules;

use function ucfirst;

/**
 * Eine Regel, um ein OPUS-Metadatenfeld mit einer Konstante zu befüllen. Hierbei wird der Inhalt des zu verarbeitenden
 * BibTeX-Record nicht ausgewertet.
 */
class ConstantValue implements IRule
{
    /** @var string Name des OPUS-Metadatenfelds */
    protected $opusField;

    /** @var string der zu setzenden Feldwert (Konstante) */
    protected $value;

    /**
     * Liefert den Namen des zu befüllenden OPUS-Metadatenfelds zurück.
     *
     * @return string Name des OPUS-Metadatenfelds
     */
    public function getOpusField()
    {
        return $this->opusField;
    }

    /**
     * Setzt den Namen des zu befüllenden OPUS-Metadatenfelds.
     *
     * @param string $opusField
     * @return $this
     */
    public function setOpusField($opusField)
    {
        $this->opusField = ucfirst($opusField);
        return $this;
    }

    /**
     * Liefert den zu setzenden Wert (Konstante) zurück.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Setzt den Feldwert (Konstante) für das OPUS-Metadatenfeld.
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Ausführung der konfigurierten Regel zur Befüllung des OPUS-Metadatenfelds mit einem konstanten Wert.
     *
     * @param array $bibtexRecord BibTeX-Record (Array von BibTeX-Feldern)
     * @param array $documentMetadata OPUS-Metadatensatz (Array von Metadatenfeldern)
     * @return bool liefert true, wenn die Regel erfolgreich angewendet werden konnte
     */
    public function apply($bibtexRecord, &$documentMetadata)
    {
        $result = false;
        // der BibTeX-Record wird zur Bestimmung des Metadatenfelds nicht verwendet
        // d.h. Metadatenfeldwert wird hier auf eine Konstante gesetzt oder Bestimmung des Feldinhalts auf Basis
        // von anderen Metadatenfeldern
        if ($this->value !== null) {
            $documentMetadata[ucfirst($this->opusField)] = $this->value;
            $result                                      = true;
        }
        return $result;
    }

    /**
     * Liefert die Liste der ausgewerteten BibTeX-Felder.
     *
     * @return array
     */
    public function getEvaluatedBibTexField()
    {
        return [];
    }
}
