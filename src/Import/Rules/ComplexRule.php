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
 * Eine Regel, die gleichzeitig auf mehrere BibTeX-Felder zugreift. Im Konstruktor muss die Liste der Feldnamen im
 * BibTeX-Record übergeben werden, auf die in der Regelausführung zugegriffen werden soll. Auf andere Felder kann
 * bei der Regelausführung nicht zugegriffen werden.
 *
 * Wird im Konstruktor keine Feldliste (oder eine leere Feldliste) übergeben, so kann die Regel auch dazu genutzt
 * werden, um auf alle in den Dokumentmetadaten gespeicherten Felder zugreifen und neue Felder in den Dokumentmetadaten
 * hinzufügen.
 */
class ComplexRule implements IRule
{
    protected $fn;

    protected $fieldsEvaluated;

    public function __construct($fn, $fieldsEvaluated = null)
    {
        $this->fn = $fn;
        $this->fieldsEvaluated = $fieldsEvaluated;
    }

    public function apply($bibtexRecord, &$documentMetadata)
    {
        $fieldValues = [];
        if (! is_null($this->fieldsEvaluated)) {
            foreach ($this->fieldsEvaluated as $fieldName) {
                if (array_key_exists($fieldName, $bibtexRecord)) {
                    $fieldValues[$fieldName] = $bibtexRecord[$fieldName];
                } else {
                    // Feld existiert nicht im BibTeX-Record und kann daher nicht ausgewertet werden
                    unset($this->fieldsEvaluated[$fieldName]);
                }
            }
        }
        // FIXME wir können nicht wirklich sicherstellen, dass beim Aufruf von $this->fn tatsächlich auf die in
        //       $this->fieldValues angegebenen Werte des BibTeX-Records zugegriffen wird
        ($this->fn)($fieldValues, $documentMetadata);
        return true;
    }

    public function getEvaluatedBibTexField()
    {
        return is_null($this->fieldsEvaluated) ? [] : $this->fieldsEvaluated;
    }
}
