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
 * @package     Opus\Bibtex\Import\Config
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import\Config;

use function array_key_exists;
use function array_keys;
use function dirname;
use function is_readable;
use function parse_ini_file;
use function strpos;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * Service-Klasse für die Verarbeitung der Konfigurationsdateien, in denen das Mapping der BibTeX-Feldmapping sowie
 * das Mapping der BibTeX-Typen auf das OPUS-Datenmodell definiert ist.
 *
 * Die Klasse ist als Singleton definiert.
 *
 * FIXME use dependency injection capabilities of Laminas ServiceManager
 */
class BibtexService
{
    /**
     * Name der Standard-Konfigurationsdatei, in der Feld-Mapping-Dateien registriert sowie Dokumenttyp-Mappings
     * angegeben werden.
     */
    const INI_FILE = 'import.ini';

    /** @var BibtexService|null interne Instanz der Klasse (für Durchsetzung des Singleton-Patterns) */
    private static $instance;

    /** @var string Name der auszuwertenden INI-Konfigurationsdatei. */
    private $iniFileName;

    /**
     * @var array enthält die registrierten Mappings für BibTeX-Felder, wobei jedes Mapping unter seinem Namen
     * abgelegt wird (ist in der Mapping-Datei als name definiert); aktuell werden ausschließlich Mapping-Dateien im
     * JSON-Format unterstützt.
     */
    private $mappings = [];

    /** @var DocumentTypeMapping Mapping von BibTeX-Typen auf OPUS-Dokumenttypen gemäß Angaben in Konfigurationsdatei */
    private $typeMapping;

    /**
     * Liefert eine Instanz der Klasse zurück; erzeugt eine Instanz, wenn bislang noch keine Instanz erzeugt wurde.
     *
     * @param null $iniFileName Name der INI-Datei, die zur Konfiguration genutzt werden soll (wenn nicht gesetzt, so
     *                          wird die Standardkonfigurationsdate verwendet)
     * @return BibtexService
     * @throws BibtexConfigException
     */
    public static function getInstance($iniFileName = null)
    {
        if (self::$instance === null) {
            self::$instance = new BibtexService();
        }

        self::$instance->iniFileName = self::$instance->getPath(
            $iniFileName ?? self::INI_FILE
        );

        self::$instance->initMappings();
        self::$instance->initTypeMapping();

        return self::$instance;
    }

    /**
     * Liefert das Feld-Mapping auf Basis der Feld-Mapping-Konfiguration (JSON) mit dem übergebenen Namen zurück.
     *
     * @param string|null $mappingConfigName Name der Feld-Mapping-Konfiguration; ist dieser nicht gesetzt, so wird auf
     *                                       das Default-Mapping zurückgegriffen
     * @return BibtexMapping Feld-Mapping von BibTeX-Feldern auf OPUS-Metadatenfelder
     * @throws BibtexConfigException Wird geworfen, wenn das übergebene Feld-Mapping nicht registriert / unbekannt ist.
     */
    public function getFieldMapping($mappingConfigName = null)
    {
        if ($mappingConfigName === null) {
            $mappingConfigName = 'default';
        }

        if (array_key_exists($mappingConfigName, $this->mappings)) {
            return $this->mappings[$mappingConfigName];
        }

        throw new BibtexConfigException("could not find configuration of field mapping with name $mappingConfigName");
    }

    /**
     * Liefert das Dokumenttyp-Mapping zurück, das in der INI-Datei konfiguriert wurde.
     *
     * @return DocumentTypeMapping Dokumenttyp-Mapping
     */
    public function getTypeMapping()
    {
        return $this->typeMapping;
    }

    /**
     * Gibt die Namen der registrierten BibTeX-Feld-Mappings zurück.
     *
     * @return array Liste der Namen der BibTeX-Feld-Mappings
     */
    public function listAvailableMappings()
    {
        return array_keys($this->mappings);
    }

    /**
     * Registriert ein BibTeX-Feld-Mapping aus der Mapping-Konfigurationsdatei (JSON) mit dem übergebenen Namen
     * und liefert die aus der Konfigurationsdatei erzeugte Mappingkonfiguration zurück.
     *
     * @param string $fileName Name der Mapping-Konfigurationsdatei
     * @return BibtexMapping|null Feld-Mapping-Instanz, die aus der Mapping-Konfigurationsdatei erzeugt wurde (liefert
     *                            null, wenn Instanz nicht erfolgreich erzeugt werden konnte)
     * @throws BibtexConfigException
     */
    public function registerMapping($fileName)
    {
        $mapping     = null;
        $mappingFile = dirname($this->iniFileName) . DIRECTORY_SEPARATOR . $fileName;

        if (is_readable($mappingFile)) {
            // TODO zukünftig weitere Konfigurationsformate unterstützen?
            if (substr($fileName, -5) === '.json') {
                $mapping = (new JsonBibtexMappingReader())->getMappingConfigurationFromFile($mappingFile);

                $this->mappings[$mapping->getName()] = $mapping;
            }
        }
        return $mapping;
    }

    /**
     * Initialisiert die in der INI-Datei angegebenen BibTeX-Feld-Mappings und registriert sie unter ihren Namen für
     * die spätere Verwendung.
     *
     * @throws BibtexConfigException Wird geworfen, wenn die Initialisierung nicht erfolgreich durchgeführt werden konnte.
     */
    private function initMappings()
    {
        $this->mappings = [];

        $fieldMappings = $this->getConfiguration('fieldMappings');
        foreach ($fieldMappings as $mappingFileName) {
            $this->registerMapping($mappingFileName);
        }
    }

    /**
     * Initialisiert das Mapping von BibTeX-Typen auf OPUS-Dokumenttypen auf Basis der INI-Konfigurationsdatei.
     *
     * @throws BibtexConfigException Wird geworfen, wenn die Initialisierung nicht erfolgreich durchgeführt werden konnte.
     */
    private function initTypeMapping()
    {
        $this->typeMapping = new DocumentTypeMapping();

        $defaultDocumentType = $this->getConfiguration('defaultDocumentType');
        $this->typeMapping->setDefaultType($defaultDocumentType);

        $documentTypeMappingFromConfig = $this->getConfiguration('documentTypeMapping');
        foreach ($documentTypeMappingFromConfig as $bibtexTypeName => $opusTypeName) {
            $this->typeMapping->setMapping($bibtexTypeName, $opusTypeName);
        }
    }

    /**
     * Gibt die Konfigurationseinstellung aus der INI-Datei zurück, die unter dem übergebenen Schlüsselnamen abgelegt
     * ist.
     *
     * @param string $keyName Name des Konfigurationsschlüssels
     * @return mixed
     * @throws BibtexConfigException Falls INI-Datei nicht existent, nicht lesbar oder der Schlüsselname nicht existiert.
     */
    private function getConfiguration($keyName)
    {
        $fileName = $this->iniFileName;

        if (! is_readable($fileName)) {
            throw new BibtexConfigException("could not find or read ini file '$fileName'");
        }

        $conf = parse_ini_file($fileName);
        if ($conf === false) {
            throw new BibtexConfigException("could not parse ini file '$fileName'");
        }

        if (! array_key_exists($keyName, $conf)) {
            throw new BibtexConfigException("could not find configuration key '$keyName' in ini file '$fileName'");
        }

        return $conf[$keyName];
    }

    /**
     * Fügt die Pfadangabe zum übergebenen Namen einer Konfigurationsdatei hinzu, sofern der übergebene Dateiname
     * keine Pfadangabe enthält.
     *
     * @param string $fileName Name der Konfigurationsdatei
     * @return string mit Pfadangabe erweiterter Dateiname
     */
    private function getPath($fileName)
    {
        if (strpos($fileName, '/') === false) {
            return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $fileName;
        }
        return $fileName;
    }
}
