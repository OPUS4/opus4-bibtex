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

use Opus\Bibtex\Import\Config\BibtexService;

/**
 * ptype ist kein Standard-BibTeX-Feld: das Feld ptype kann genutzt werden, um das Typ-Mapping auf Basis des
 * BibTeX-Types (die Zeichenkette nach @) zu umgehen
 *
 * Ist im BibTeX-Record kein Feld ptype vorhanden, so wird der Typ aus der Zeichenkette nach @ abgeleitet
 */
class DocumentType extends SimpleRule
{
    protected $documentTypeMapping;

    private $fieldsEvaluated = [];

    public function __construct()
    {
        $this->setBibtexField('type');
        $this->setOpusField('Type');
        return $this;
    }

    public function setDocumentTypeMapping($documentTypeMapping)
    {
        $this->documentTypeMapping = $documentTypeMapping;
        return $this;
    }

    protected function getValue($value)
    {
        if (is_null($this->documentTypeMapping)) {
            $this->documentTypeMapping = BibtexService::getInstance()->getTypeMapping();
        }

        $useDefaultAsFallback = $this->getBibtexField() === 'type';
        $result = $this->documentTypeMapping->getOpusType($value, $useDefaultAsFallback);
        return $result;
    }

    public function apply($bibtexRecord, &$documentMetadata)
    {
        $this->fieldsEvaluated = [];
        $result = parent::apply($bibtexRecord, $documentMetadata);
        $typeField = $this->getBibtexField();
        if ($result) {
            $this->fieldsEvaluated[] = $typeField;
            return true;
        }

        if ($typeField !== 'type') {
            // Auswertung des Standard-BibTeX-Felds für den Dokumenttyp
            $this->setBibtexField('type');
            $result = parent::apply($bibtexRecord, $documentMetadata);
            $this->fieldsEvaluated[] = 'type';
            $this->setBibtexField($typeField);
        }

        return $result;
    }

    public function getEvaluatedBibTexField()
    {
        return $this->fieldsEvaluated;
    }
}
