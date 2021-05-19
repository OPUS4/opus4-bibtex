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

use function array_key_exists;
use function count;
use function explode;
use function intval;
use function str_replace;
use function trim;

/**
 * Erlaubt das Erzeugen von OPUS-Metadatenfeldern für die Speicherung von Seitenangaben: PageFirst, PageLast und \
 * PageNumber.
 *
 * TODO etwas kompliziert, oder? Mit redundantem Code zwischen getPageFirst and getPageLast
 */
class Pages extends AbstractComplexRule
{
    /**
     * Konstruktor
     */
    public function __construct()
    {
        parent::__construct(['pages']);
    }

    /**
     * Setzt die OPUS-Metadatenfelder für Seitenangaben.
     *
     * @param array $fieldValues Werte der BibTeX-Felder
     * @param array $documentMetadata OPUS-Metadatensatz (Array von Metadatenfeldern)
     * @return bool liefert true, gdw. die Anwendung der Regeln zur Bestimmung der Seitenangaben erfolgreich war
     */
    protected function setFields($fieldValues, &$documentMetadata)
    {
        if (array_key_exists('pages', $fieldValues)) {
            $pagesFieldValue = $fieldValues['pages'];

            $value                         = str_replace(['--', '––', '–'], '-', $pagesFieldValue);
            $parts                         = explode('-', $value, 2);
            $documentMetadata['PageFirst'] = trim($parts[0]);
            $documentMetadata['PageLast']  = trim($parts[count($parts) === 2 ? 1 : 0]);

            $pageFirst =
                array_key_exists('PageFirst', $documentMetadata) ? intval($documentMetadata['PageFirst']) : 0;
            $pageLast  =
                array_key_exists('PageLast', $documentMetadata) ? intval($documentMetadata['PageLast']) : 0;
            if ($pageFirst > 0 && $pageLast > 0 && $pageLast >= $pageFirst) {
                $documentMetadata['PageNumber'] = 1 + $pageLast - $pageFirst;
            }
            return true;
        }
        return false;
    }
}
