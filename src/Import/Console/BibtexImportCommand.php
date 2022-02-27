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
 * @copyright   Copyright (c) 2021-2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Console;

use Opus\Bibtex\Import\Console\Helper\BibtexImportHelper;
use Opus\Bibtex\Import\Console\Helper\BibtexImportResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Befehl zum Import einer BibTeX-Datei (in der beliebig viele BibTeX-Records enthalten sein können).
 */
class BibtexImportCommand extends Command
{
    /**
     * Argument für den Namen der zu importierenden BibTeX-Datei
     */
    const ARGUMENT_IMPORT_FILE = 'fileName';

    /**
     * Option, um den Modus zu aktivieren, bei dem keine aus der BibTeX-Datei erzeugten OPUS-Dokumente in der Datenbank
     * gespeichert werden
     */
    const OPTION_DRYMODE = 'dry';

    /**
     * Option, um die Menge der Ausgaben während der Ausführung des BibTeX-Imports zu vergrößern
     */
    const OPTION_VERBOSE = 'verbose';

    /**
     * Mehrfach-Option, mit der IDs von Collections übergeben werden kann: jedes erfolgreich
     * importierte Dokument wird den über die ID referenzierten Collections zugewiesen; IDs von nicht existierenden
     * Collections werden bei der Zuweisung stillschweigend ignoriert
     */
    const OPTION_COLLECTION = 'collection';

    /**
     * Option, mit der das zu verwendende Feld-Mapping ausgewählt werden kann. Wird diese Option nicht gesetzt,
     * so wird das Default-Mapping aus default-mapping.json verwendet. Das Feld-Mapping muss in der INI-Datei
     * unter fieldMappings registriert sein.
     */
    const OPTION_MAPPING_CONFIGURATION = 'mapping';

    protected function configure()
    {
        $help = <<<EOT
The <fg=green>bibtex:import</> command can be used to import a BibTeX file 
that contains an arbitrary number of BibTeX records (document metadata only).
The name of the BibTeX file (*.bib) must be provided as an argument. 

Each BibTeX record is converted into an OPUS 4 document by applying a mapping 
configuration. A mapping configuration consists of rules defined in a JSON
file. 

In case of failures, the current BibTeX record is skipped and processing
continues with the next BibTeX record.

The parsing of the BibTeX file can be checked without changing the database 
state by using the dry (<fg=green>--dry</> or <fg=green>-d</>).

The name of the field mapping that is applied in the import process (defaults
to 'default') can be overwritten by the option <fg=green>--mapping</> or 
<fg=green>-m</>. The field mapping is defined by a JSON file that has to be 
registered in the configuration.

The (repeatable) option <fg=green>--collection</> or <fg=green>-c</> allows to 
specify IDs of collections each successfully imported document is assigned to.
IDs that do not match with IDs of existing collection are ignored silently.
EOT;

        $this->setName('bibtex:import')
            ->setDescription('Import records in given BibTeX file')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_IMPORT_FILE,
                InputArgument::REQUIRED,
                'Name of BibTeX file to be imported'
            )
            ->addOption(
                self::OPTION_DRYMODE,
                'd',
                InputOption::VALUE_NONE,
                'Dry mode (processing of the given BibTeX file without changing OPUS database)'
            )
            ->addOption(
                self::OPTION_MAPPING_CONFIGURATION,
                'm',
                InputOption::VALUE_REQUIRED,
                'Name of mapping configuration to be used'
            )
            ->addOption(
                self::OPTION_COLLECTION,
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Collection IDs (comma-separated) that documents will be assigned to'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument(self::ARGUMENT_IMPORT_FILE);

        $bibtexImportHelper = new BibtexImportHelper($fileName);

        $collectionIds = $input->getOption(self::OPTION_COLLECTION);
        if ($collectionIds !== null) {
            $bibtexImportHelper->setCollectionIds($collectionIds);
        }

        $mappingConfiguration = $input->getOption(self::OPTION_MAPPING_CONFIGURATION);
        if ($mappingConfiguration !== null) {
            $bibtexImportHelper->setMappingConfiguration($mappingConfiguration);
        }

        if ($input->getOption(self::OPTION_DRYMODE)) {
            $bibtexImportHelper->enableDryMode();
        }

        if ($input->getOption(self::OPTION_VERBOSE)) {
            $bibtexImportHelper->enableVerbose();
        }

        $bibtexImportResult = new BibtexImportResult($output);
        $bibtexImportHelper->doImport($bibtexImportResult);
        $bibtexImportResult->outputCompletionMessage();
    }
}
