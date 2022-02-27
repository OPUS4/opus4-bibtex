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
 * @package     Opus\Bibtex\Import
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import;

use Exception;
use Opus\Bibtex\Import\Config\BibtexMapping;
use Opus\Bibtex\Import\Config\BibtexService;

use function array_change_key_case;
use function in_array;
use function substr;

/**
 * Bildet die Werte von BibTeX-Feldern auf konfigurierte OPUS-Metadatenfelder ab. Das Feld-Mapping wird hierbei in einer
 * Konfigurationsdatei vorgebeben. Es existiert mit default-mapping.json eine Standardkonfiguration für die Abbildung
 * der Felder. In der README.md sind die Konfigurationsmöglichkeiten detailliert beschrieben.
 *
 * TODO this needs an interface - this is a Rules-based processor, there could be other implementations
 */
class Processor
{
    /** @var BibtexMapping Feld-Mapping (aus externer Konfigurationsdatei) */
    private $fieldMapping;

    /**
     * Konstruktor
     *
     * @param BibtexMapping|null $fieldMapping Feld-Mapping als Instanz von BibtexMapping
     * @throws Exception Wird geworfen, wenn bei der Auswertung der Feld-Mapping-Konfiguration Fehler aufgetreten sind.
     */
    public function __construct($fieldMapping = null)
    {
        if ($fieldMapping !== null || $fieldMapping instanceof BibtexMapping) {
            $this->fieldMapping = $fieldMapping;
        } else {
            $configService      = BibtexService::getInstance();
            $this->fieldMapping = $configService->getFieldMapping();
        }
    }

    /**
     * Erzeugt aus den Feldern (und ihren Werten) des übergebenen BibTeX-Records ein Array von OPUS-Metadatenfeldern.
     * Dieses Array kann im weiteren Verlauf mit der Funktion fromArray in ein Objekt vom Typ Opus_Document umgewandelt
     * werden.
     *
     * @param array $bibtexRecord BibTeX-Record als Array von BibTeX-Feldern auf die zugehörigen Feldwerte
     * @param array $opusMetadata OPUS-Metadatensatz als Array von Metadatenfelden auf die zugehörigen Werte
     * @return array Array mit Namen der Felder des BibTeX-Records, die bei der Befüllung des OPUS-Metadatensatzes
     *               ausgewertet wurden
     */
    public function handleRecord($bibtexRecord, &$opusMetadata)
    {
        $bibtexRecord          = array_change_key_case($bibtexRecord);
        $bibtexFieldsEvaluated = [];

        foreach ($this->fieldMapping->getRules() as $name => $rule) {
            $ruleResult = $rule->apply($bibtexRecord, $opusMetadata);
            if ($ruleResult) {
                // Regel wurde erfolgreich angewendet und das Ziel-Metadatenfeld wurde mit einem Inhalt befüllt
                $fieldsEvaluated = $rule->getEvaluatedBibTexField();
                foreach ($fieldsEvaluated as $fieldEvaluated) {
                    if (substr($fieldEvaluated, 0, 1) !== '_') {
                        // interne Feldnamen des BibTeX-Parsers beginnen mit einem Unterstich und werden hier ignoriert
                        // da sie im Original-BibTeX-Record nicht existieren
                        if (! in_array($fieldEvaluated, $bibtexFieldsEvaluated)) {
                            // TODO wäre es interessant, die Anzahl der Zugriffe auf ein BibTeX-Felds zu protokollieren
                            $bibtexFieldsEvaluated[] = $fieldEvaluated;
                        }
                    }
                }
            }
        }
        return $bibtexFieldsEvaluated;
    }
}
