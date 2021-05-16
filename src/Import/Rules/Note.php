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

/**
 * Erlaubt das Erzeugen von Notes, wobei neben dem Wert auch die Sichtbarkeit (Standard: public) sowie ein optional
 * Präfix gesetzt werden kann, das dem Feldwert vorangestellt wird.
 */
class Note extends ArrayRule
{
    /** @var string Präfix, der dem Wert der Note vorangestellt wird (Default: leer) */
    private $messagePrefix = '';

    /** @var string Sichtbarkeitseinstellung der Note (Default: public) */
    private $visibility = 'public';

    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->setOpusField('Note');
    }

    /**
     * Gibt das gesetzte Präfix zurück.
     *
     * @return string
     */
    public function getMessagePrefix()
    {
        return $this->messagePrefix;
    }

    /**
     * Erlaubt das Setzen des Präfix für den Wert der Note.
     *
     * @param string $messagePrefix Präfix
     * @return $this
     */
    public function setMessagePrefix($messagePrefix)
    {
        $this->messagePrefix = $messagePrefix;
        return $this;
    }

    /**
     * Liefert die Sichtbarkeitseinstellung zurück.
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Erlaubt das Setzen der Sichtbarkeitseinstellung auf den übergebenen Wert.
     *
     * @param string $visibility Sichtbarkeitseinstellung
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * Ermittelt die Werte für das OPUS-Metadatenfeld.
     *
     * @param string $value auszuwertender Wert aus BibTeX-Feld
     * @return array
     */
    protected function getValue($value)
    {
        return [
            'Visibility' => $this->visibility,
            'Message'    => $this->messagePrefix . $value,
        ];
    }
}
