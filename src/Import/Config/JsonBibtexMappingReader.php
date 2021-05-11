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
 * @package     Opus\Bibtex\Import\Config
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Config;

/**
 * Erlaubt das Auslesen einer JSON-Konfigurationsdatei, in der das Mapping der BibTeX-Felder auf die
 * OPUS-Metadatenfelder definiert ist.
 */
class JsonBibtexMappingReader
{
    /**
     * @param $fileName Name der Mapping-Konfigurationsdatei (JSON)
     * @return BibtexMapping Instanz des BibTeX-Mappings auf Basis der Angaben in der übergebenen Konfigurationsdatei
     * @throws \Exception wird geworfen, wenn das konfigurierte Mapping nicht erfolgreich ausgewertet werden konnte
     */
    public function getMappingConfigurationFromFile($fileName)
    {
        if (! is_readable($fileName)) {
            throw new \Exception("could not read file $fileName");
        }

        $json = file_get_contents($fileName);
        if ($json === false) {
            throw new \Exception("could not get content of file $fileName");
        }

        $jsonArr = json_decode($json, true);
        if (is_null($jsonArr)) {
            throw new \Exception("could not decode JSON file $fileName");
        }

        if (! (array_key_exists('name', $jsonArr) &&
            array_key_exists('description', $jsonArr) &&
            array_key_exists('mapping', $jsonArr))) {
            throw new \Exception("missing key(s) in JSON file $fileName");
        }

        return (new BibtexMapping())
            ->setName($jsonArr['name'])
            ->setDescription($jsonArr['description'])
            ->setRules($jsonArr['mapping']);
    }
}
