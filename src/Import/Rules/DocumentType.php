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

use Opus\Bibtex\Import\Config\BibtexService;
use Opus\Bibtex\Import\Config\DocumentTypeMapping;

/**
 * Regel zum Setzen des OPUS-Dokumenttyps.
 *
 * Standardmäßig wird der BibTeX-Typ aus der Zeichenkette nach @ im BibTeX-Record abgeleitet. Die Klasse bietet
 * zusätzlich die Möglichkeit ein benutzerspezifisches (nicht im Standard enthaltenes) BibTeX-Feld zu definieren, das
 * stattdessen zur Bestimmung des OPUS-Dokumenttyps verwendet wird. Diese Option wird in der ausgelieferten
 * Mapping-Konfiguration verwendet, um das Nicht-Standard-Feld pytpe beim Dokumenttyp-Mapping auszuwerten.
 */
class DocumentType extends SimpleRule
{
    /**
     * Name des BibTeX-Felds, das standardmäßig für die Bestimmung des Dokumenttyps verwendet wird
     */
    const DEFAULT_BIBTEX_FIELD_NAME = 'type';

    /** @var array auf Namen basierendes Mapping von BibTeX-Typen auf OPUS-Dokumenttypen */
    private $documentTypeMapping;

    /** @var array Liste der bei der Regelanwendung ausgewerteten BibTeX-Felder */
    private $fieldsEvaluated = [];

    /**
     * Konstruktor, der Standardeinstellungen setzt
     */
    public function __construct()
    {
        $this->setBibtexField('type');
        $this->setOpusField('Type');
    }

    /**
     * Erlaubt das Registrieren eines Mappings von BibTeX-Typen auf OPUS-Dokumenttypen.
     *
     * @param DocumentTypeMapping $documentTypeMapping
     * @return $this
     */
    public function setDocumentTypeMapping($documentTypeMapping)
    {
        $this->documentTypeMapping = $documentTypeMapping;
        return $this;
    }

    /**
     * Ermittelt den Namen des OPUS-Dokumenttyp für den übergebenen BibTeX-Typen.
     *
     * @param string $value Name des BibTeX-Typs
     * @return string Name des OPUS-Dokumenttyps
     */
    protected function getValue($value)
    {
        if ($this->documentTypeMapping === null) {
            $this->documentTypeMapping = BibtexService::getInstance()->getTypeMapping();
        }

        $useDefaultAsFallback = $this->getBibtexField() === self::DEFAULT_BIBTEX_FIELD_NAME;
        return $this->documentTypeMapping->getOpusType($value, $useDefaultAsFallback);
    }

    /**
     * Anwendung der Regel zur Abbildung des BibTeX-Typs auf den OPUS-Dokumenttyp für den übergebenen BibTeX-Record.
     *
     * @param array $bibtexRecord BibTeX-Record (Array von BibTeX-Feldern)
     * @param array $documentMetadata OPUS-Metadatensatz (Array von Metadatenfeldern)
     * @return bool liefert true, gdw. die Regelanwendung erfolgreich war
     */
    public function apply($bibtexRecord, &$documentMetadata)
    {
        $this->fieldsEvaluated = [];

        $result    = parent::apply($bibtexRecord, $documentMetadata);
        $typeField = $this->getBibtexField();
        if ($result) {
            // Regelanwendung war erfolgreich
            $this->fieldsEvaluated[] = $typeField;
            return true;
        }

        // bei nicht erfolgreicher Regelanwendung: prüfe, ob möglicherweise ein benutzerspezifisches BibTeX-Feld
        // ausgewertet wurde und falle auf das Standard-BibTeX-Feld zurück
        if ($typeField !== self::DEFAULT_BIBTEX_FIELD_NAME) {
            // Auswertung des Standard-BibTeX-Felds für den Dokumenttyp
            $this->setBibtexField(self::DEFAULT_BIBTEX_FIELD_NAME);
            $result                  = parent::apply($bibtexRecord, $documentMetadata);
            $this->fieldsEvaluated[] = self::DEFAULT_BIBTEX_FIELD_NAME;
            $this->setBibtexField($typeField);
        }

        return $result;
    }

    /**
     * Liefert die Namen der bei der Regelausführung augewerteten BibTeX-Felder.
     *
     * @return array
     */
    public function getEvaluatedBibTexField()
    {
        return $this->fieldsEvaluated;
    }
}
