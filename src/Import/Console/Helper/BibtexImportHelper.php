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
 * @package     Opus\Bibtex\Import\Console\Helper
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import\Console\Helper;

use Exception;
use Opus\Bibtex\Import\Config\BibtexService;
use Opus\Bibtex\Import\Parser;
use Opus\Bibtex\Import\ParserException;
use Opus\Bibtex\Import\Processor;
use Opus\Bibtex\Import\Rules\SourceData;
use Opus\Bibtex\Import\Rules\SourceDataHash;
use Opus\Collection;
use Opus\Document;
use Opus\DocumentFinder;
use Opus\Enrichment;
use Opus\EnrichmentKey;
use Opus\Model\ModelException;
use Opus\Model\NotFoundException;

use function count;
use function explode;
use function gmdate;
use function is_array;
use function is_null;
use function strlen;
use function substr;
use function trim;
use function uniqid;

class BibtexImportHelper
{
    /** @var string Name der BibTeX-Datei */
    private $fileName;

    /** @var string Name der Mapping-Konfiguration */
    private $mappingConfiguration;

    /** @var string Name der INI-Konfigurationsdatei */
    private $iniFileName;

    /** @var bool Dry-Mode */
    private $dryMode;

    /** @var bool ausführliche Logausgabe */
    private $verbose;

    /** @var array IDs von Collections */
    private $collectionIds = [];

    /**
     * Konstruktor
     *
     * @param string $fileName Name der zu importierenden BibTeX-Datei
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @param BibtexImportResult $bibtexImportResult
     */
    public function doImport($bibtexImportResult)
    {
        if (! $this->dryMode) {
            // das Hinzufügen von Collections zu importierten OPUS-Dokumenten muss nur im Nicht-Dry-Mode betrachtet werden
            foreach ($this->collectionIds as $collectionId) {
                $collectionId = trim($collectionId);
                try {
                    $collections[] = Collection::get($collectionId);
                } catch (NotFoundException $nfe) {
                    $bibtexImportResult->addMessage("Collection with ID $collectionId does not exist");
                }
            }

            // Anlegen der erforderlichen Enrichment Keys ist nur erforderlich, wenn tatsächlich Dokumente gespeichert werden
            $requiredEnrichmentKeys = [
                SourceData::SOURCE_DATA_KEY,
                SourceDataHash::SOURCE_DATA_HASH_KEY,
                'opus.import.date',
                'opus.import.file',
                'opus.import.format',
                'opus.import.id',
            ];

            $this->createEnrichmentKeysIfMissing($requiredEnrichmentKeys, $bibtexImportResult);

            $parser = new Parser($this->fileName);
            try {
                $bibtexRecords = $parser->parse();
                $bibtexImportResult->addMessage("Parsing of BibTeX file '$this->fileName' returned " . count($bibtexRecords) . " records");
            } catch (ParserException $e) {
                $bibtexImportResult->addMessage("Parsing of BibTeX file '$this->fileName' exited unsuccessfully with error message: " . $e->getMessage());
                return;
            }

            if ($bibtexRecords === null || count($bibtexRecords) === 0) {
                $bibtexImportResult->addMessage("No records found in BibTex file '$this->fileName'");
                return;
            }

            $fieldMapping  = null;
            $bibtexService = BibtexService::getInstance($this->iniFileName);
            if ($this->mappingConfiguration !== null) {
                $fieldMapping = $bibtexService->getFieldMapping($this->mappingConfiguration);
            }
            $processor = new Processor($fieldMapping);

            // BibTeX-Records werden einzeln importiert: im Fehlerfall -> springe zum nächsten Record
            foreach ($bibtexRecords as $bibtexRecord) {
                $bibtexImportResult->increaseNumDocsProcessed();
                $metadata = [];
                try {
                    $fieldsEvaluated = $processor->handleRecord($bibtexRecord, $metadata);
                } catch (Exception $e) {
                    $bibtexImportResult->addMessage('E');
                    continue; // springe zum nächsten Record
                }

                try {
                    $doc = Document::fromArray($metadata);
                    if ($this->checkIfDocumentAlreadyExists($doc, $bibtexImportResult)) {
                        $bibtexImportResult->addMessage('S');
                        continue; // Dokument wurde bereits importiert: springe zum nächsten Record
                    }

                    if (! $this->dryMode) {
                        foreach ($collections as $collection) {
                            $doc->addCollection($collection);
                        }
                        $this->addImportEnrichments($doc);
                        try {
                            $doc = Document::get($doc->store());
                            if ($this->verbose) {
                                $bibtexImportResult->addMessage("Successful import of OPUS document " . $doc->getId());
                            }
                            $bibtexImportResult->increaseNumDocsImported();
                        } catch (ModelException $ome) {
                            if ($this->verbose) {
                                $bibtexImportResult->addMessage('Unexpected error: ' . $ome->getMessage());
                            }
                            $bibtexImportResult->addMessage('E');
                            continue; // zum nächsten Record springen
                        }
                    }
                } catch (Exception $e) {
                    $bibtexImportResult->addMessage('E');
                    continue;
                }
                $bibtexImportResult->addMessage('.');
            }
        }
    }

    /**
     * @param array|string $collectionIds IDs von Collections, zu denen die importierten Dokumente hinzugefügt werden
     *                                    sollen
     */
    public function setCollectionIds($collectionIds)
    {
        if (! is_array($collectionIds)) {
            if (trim($collectionIds) === '') {
                $collectionIds = [];
            } else {
                $collectionIds = explode(',', $collectionIds);
            }
        }
        $this->collectionIds = $collectionIds;
    }

    /**
     * @param string $mappingConfiguration Name der Feld-Mapping-Konfiguration
     */
    public function setMappingConfiguration($mappingConfiguration)
    {
        if (trim($mappingConfiguration) !== '') {
            $this->mappingConfiguration = $mappingConfiguration;
        }
    }

    /**
     * @param string $iniFileName Name der INI-Datei
     */
    public function setIniFileName($iniFileName)
    {
        if (trim($iniFileName) !== '') {
            $this->iniFileName = $iniFileName;
        }
    }

    public function enableDryMode()
    {
        $this->dryMode = true;
    }

    public function enableVerbose()
    {
        $this->verbose = true;
    }

    /**
     * Erzeugt die für den BibTeX-Import erforderlichen Enrichment Keys, sofern sie nicht bereits existieren.
     *
     * @param string             $enrichmentKeyNames
     * @param BibtexImportResult $bibtexImportResult
     */
    private function createEnrichmentKeysIfMissing($enrichmentKeyNames, $bibtexImportResult)
    {
        foreach ($enrichmentKeyNames as $keyName) {
            try {
                $sourceEnrichmentKey = EnrichmentKey::fetchByName($keyName);
                if ($sourceEnrichmentKey === null) {
                    $sourceEnrichmentKey = new EnrichmentKey();
                    $sourceEnrichmentKey->setName($keyName);
                    $sourceEnrichmentKey->store();
                }
            } catch (Exception $e) {
                $bibtexImportResult->addMessage("Could not create enrichment key $keyName: " . $e->getMessage());
            }
        }
    }

    /**
     * Fügt zum übergebenen Dokument eine Menge von Enrichments hinzu. Die Enrichment Keys müssen in der Datenbank
     * existieren.
     *
     * @param Document $document das importierte OPUS-Dokument
     */
    private function addImportEnrichments($document)
    {
        $this->createEnrichment($document, 'opus.import.date', gmdate('c'));
        $this->createEnrichment($document, 'opus.import.file', $this->fileName);
        $this->createEnrichment($document, 'opus.import.format', 'bibtex');
        $this->createEnrichment($document, 'opus.import.id', uniqid('', true));
    }

    /**
     * Erzeugt ein Enrichment mit den übergebenen Daten und fügt es dem übergebenen OPUS-Dokument hinzu.
     *
     * @param Document $document
     * @param string   $keyName
     * @param string   $value
     */
    private function createEnrichment($document, $keyName, $value)
    {
        $enrichment = new Enrichment();
        $enrichment->setKeyName($keyName);
        $enrichment->setValue($value);
        $document->addEnrichment($enrichment);
    }

    /**
     * Prüft, ob bereits ein OPUS-Dokument importiert wurde, das den gleichen Hashwert besitzt, wie das übergebene
     * OPUS-Dokument. In diesem Fall sollte das übergebene Dokument nicht erneut importiert werden.
     *
     * @param Document           $document das zu importierende Dokument
     * @param BibtexImportResult $bibtexImportResult
     * @return bool true, gdw. bereits ein Dokument mit identische Hashwert in der Datenbank existiert
     */
    private function checkIfDocumentAlreadyExists($document, $bibtexImportResult)
    {
        $hashValue = null;
        try {
            foreach ($document->getEnrichment() as $enrichment) {
                if ($enrichment->getKeyName() === SourceDataHash::SOURCE_DATA_HASH_KEY) {
                    $hashValue = $enrichment->getValue();
                    break;
                }
            }
        } catch (ModelException $modelException) {
            $bibtexImportResult->addMessage('unexpected exception (could not find enrichment '
                . SourceDataHash::SOURCE_DATA_HASH_KEY
                . '): ' . $modelException->getMessage());
            return true; // Dokument wird nicht importiert, da der Hashvergleich nicht möglich war
        }

        if ($hashValue === null) {
            $bibtexImportResult->addMessage('could not find enrichment ' . SourceDataHash::SOURCE_DATA_HASH_KEY);
            return true; // Dokument wird nicht importiert, da der Hashvergleich nicht möglich war
        }

        if ($this->checkForDocWithIdenticalHashValue($hashValue, $bibtexImportResult)) {
            return true; // Import des Dokuments wird verhindert
        }

        // in der ersten Version des BibTeX-Imports wurde die Hashfunktion nicht als Präfix im Enrichment-Wert (Hashwert)
        // gespeichert - daher erfolgt eine Suche nach Hashwert ohne mit Doppelpunkt vorangestellter Name der
        // verwendeten Hashfunktion
        $hashValueWithoutPrefix = substr(
            $hashValue,
            strlen(SourceDataHash::HASH_FUNCTION) + 1
        );

        return $this->checkForDocWithIdenticalHashValue($hashValueWithoutPrefix, $bibtexImportResult);
    }

    /**
     * Gibt true zurück, wenn in der Datenbank bereits ein Dokument mit dem übergebenen Hashwert im Enrichment
     * BibtexMapping::SOURCE_DATA_HASH_KEY existiert.
     *
     * @param string             $hashValue
     * @param BibtexImportResult $bibtexImportResult
     * @return bool
     */
    private function checkForDocWithIdenticalHashValue($hashValue, $bibtexImportResult)
    {
        $finder = new DocumentFinder();
        $finder->setEnrichmentKeyValue(SourceDataHash::SOURCE_DATA_HASH_KEY, $hashValue);
        if ($finder->count() > 0) {
            if ($this->verbose) {
                $bibtexImportResult->addMessage('Found existing OPUS document ' . $finder->ids()[0] . " with same hash value ($hashValue)");
            }
            return true; // Dokument mit identischem Hashwert existiert bereits in der Datenbank
        }
        return false;
    }
}
