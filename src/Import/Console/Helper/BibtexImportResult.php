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

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;

class BibtexImportResult
{
    /**
     * @var int Anzahl der während des Imports im Hauptspeicher erzeugten OPUS-Dokumente (ein OPUS-Dokument entspricht
     * einem BibTeX-Record aus der zu importierenden BibTeX-Datei)
     */
    private $numDocsProcessed = 0;

    /** @var int Anzahl der tatsächlich erfolgreich in der Datenbank gespeicherten OPUS-Dokumente */
    private $numDocsImported = 0;

    /** @var int Anzahl der aufgrund von Fehlern nicht importierbaren BibTeX-Records */
    private $numErrors = 0;

    /** @var int Anzahl der aufgrund von Hashwert-Übereinstimmungen nicht importierten BibTeX-Records */
    private $numSkipped = 0;

    /** @var Output Ausgabepuffer für Meldungen während des BibTeX-Imports */
    private $output;

    /** @var array */
    private $messages = [];

    /**
     * Maximale Anzahl der Status-Indikatorzeichen in einer Bildschirmzeile
     */
    const STATUS_LINE_WIDTH = 80;

    /**
     * Konstruktor
     *
     * @param Output|null $output Ausgabepuffer für Meldungen während des BibTeX-Imports
     */
    public function __construct($output = null)
    {
        if ($output === null) {
            $output = new NullOutput();
        }
        $this->output = $output;
    }

    /**
     * @return int
     */
    public function getNumDocsProcessed()
    {
        return $this->numDocsProcessed;
    }

    public function increaseNumDocsProcessed()
    {
        $this->numDocsProcessed++;
    }

    /**
     * @return int
     */
    public function getNumDocsImported()
    {
        return $this->numDocsImported;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->output->writeln($message);
        $this->messages[] = $message;
    }

    /**
     * Gesamtinformationen zum BibTeX-Import ausgeben
     */
    public function outputCompletionMessage()
    {
        $this->addMessage('');
        $this->addMessage('');
        $this->addMessage("Number of documents processed: $this->numDocsProcessed");
        $this->addMessage("Number of documents imported: $this->numDocsImported");

        if ($this->numSkipped > 0) {
            $this->addMessage("Number of skipped BibTeX records: $this->numSkipped");
        }

        if ($this->numErrors > 0) {
            $this->addMessage("Number of discarded BibTeX records due to errors: $this->numErrors");
        }
    }

    /**
     * @param bool $verbose true, wenn ausführliche Logausgabe gewünscht
     * @param bool $dryMode true, wenn keine neuen Dokumente in die Datenbank importiert werden sollen
     */
    public function addSuccessStatus($verbose, $dryMode)
    {
        $this->addStatus('.', $verbose);
        if (! $dryMode) {
            $this->numDocsImported++;
        }
    }

    /**
     * @param bool $verbose true, wenn ausführliche Logausgabe gewünscht
     */
    public function addErrorStatus($verbose)
    {
        $this->addStatus('E', $verbose);
    }

    /**
     * @param bool $verbose true, wenn ausführliche Logausgabe gewünscht
     */
    public function addSkipStatus($verbose)
    {
        $this->addStatus('S', $verbose);
        $this->numSkipped++;
    }

    /**
     * @param string $statusStr auszugebender Status-Indikator (ein Zeichen)
     * @param bool   $verbose   true, wenn ausführliche Logausgabe gewünscht
     */
    private function addStatus($statusStr, $verbose)
    {
        if (! $verbose) {
            $this->output->write($statusStr);
            if ($this->numDocsProcessed % self::STATUS_LINE_WIDTH === 0) {
                $this->output->writeln(''); // neue Zeile beginnen
            }
        }
    }
}
