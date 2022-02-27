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
 */

namespace Opus\Bibtex\Import\Config;

use Opus\Config;
use Zend_Config;

use function array_key_exists;

/**
 * Hält die Konfigurationseinstellungen zum Mapping von BibTeX-Typen auf OPUS-Dokumenttypen.
 *
 * TODO initialize with default mapping
 */
class DocumentTypeMapping
{
    /**
     * @var array Mapping (assoziatives Array) von BibTeX-Typen auf OPUS-Dokumenttypen, wobei ein BibTeX-Typ nur einem
     * OPUS-Dokumenttyp zugeordnet sein kann.
     */
    private $typeMap = [];

    /**
     * @var string Name des OPUS-Dokumenttyps, der zurückgegeben wird, wenn für den BibTeX-Typ kein Mapping auf einen
     * OPUS-Dokumenttyp existiert.
     */
    private $defaultType;

    /**
     * Erlaubt das Setzen eines Mapping-Eintrags vom übergebenen BibTeX-Typ auf den übergebenen OPUS-Dokumenttyp.
     * Liegt für den übergebenen BibTeX-Typ bereits ein Mapping vor, so wird es überschrieben.
     *
     * @param string $bibtexType Name des BibTeX-Typs
     * @param string $opusType Name des OPUS-Dokumenttyps
     * @return $this
     */
    public function setMapping($bibtexType, $opusType)
    {
        $this->typeMap[$bibtexType] = $opusType;
        return $this;
    }

    /**
     * Erlaubt das Entfernen des Typ-Mappings für den übergebenen BibTeX-Typ. Der Versuch des Entfernens eines nicht
     * vorhandenen Mappings wird ignoriert.
     *
     * @param string $bibtexType Name des BibTeX-Typs
     * @return $this
     */
    public function removeMapping($bibtexType)
    {
        if (array_key_exists($bibtexType, $this->typeMap)) {
            unset($this->typeMap[$bibtexType]);
        }
        return $this;
    }

    /**
     * Setzt das Typ-Mapping zurück.
     *
     * @return $this
     */
    public function clearMapping()
    {
        $this->typeMap = [];
        return $this;
    }

    /**
     * Erlaubt das Setzen eines OPUS-Dokumenttyp, der immer dann zurückgegeben wird, wenn für einen BibTeX-Typ kein
     * Mapping-Eintrag existiert.
     *
     * @param string $defaultType Name des OPUS-Dokumenttyps
     * @return $this
     */
    public function setDefaultType($defaultType)
    {
        $this->defaultType = $defaultType;
        return $this;
    }

    /**
     * Gibt den konfigurierten Default-OPUS-Dokumenttyp zurück.
     *
     * @return string
     */
    public function getDefaultType()
    {
        $config = $this->getConfig();

        if (isset($config->bibtex->defaultDocumentType)) {
            return $config->bibtex->defaultDocumentType;
        }

        return $this->defaultType;
    }

    /**
     * Gibt den OPUS-Dokumenttyp für den übergebenen BibTeX-Typ zurück, der explizit im Mapping eingetragen wurde.
     * Existiert kein Mapping-Eintrag für den übergebenen BibTeX-Typ, so wird der Default-Type als OPUS-Dokumenttyp
     * zurückgegeben. Ist kein Default-Typ definiert, so wird null zurückgegeben.
     *
     * @param string $bibtexType Name des BibTeX-Typs
     * @param bool   $useDefaultAsFallback False deaktiviert Fallback auf Defaulttyp.
     * @return string|null Name des zugehörigen OPUS-Dokumenttyps (oder null, wenn kein Mapping möglich)
     *
     * TODO macht $useDefaultAsFallback Sinn? Wie/wo wird es verwendet?
     * TODO check if mapped document type exists?
     */
    public function getOpusType($bibtexType, $useDefaultAsFallback = true)
    {
        $config = $this->getConfig();

        // Check if a custom mapping has been configured
        if (isset($config->bibtex->entryTypes->$bibtexType)) {
            return $config->bibtex->entryTypes->$bibtexType;
        }

        // Use standard mapping
        if (array_key_exists($bibtexType, $this->typeMap)) {
            return $this->typeMap[$bibtexType];
        }

        // Use default mapping
        if ($useDefaultAsFallback) {
            return $this->getDefaultType();
        }

        return null;
    }

    /**
     * Gibt die aktuelle Typmapping-Konfiguration zurück.
     *
     * @return array
     */
    public function getMappings()
    {
        return $this->typeMap;
    }

    /**
     * @return Zend_Config
     */
    public function getConfig()
    {
        return Config::get();
    }
}
