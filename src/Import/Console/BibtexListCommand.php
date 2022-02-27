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
 * @copyright   Copyright (c) 2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Console;

use Opus\Bibtex\Import\Config\BibtexService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_map;
use function max;
use function sprintf;

/**
 * Befehl zum Import einer BibTeX-Datei (in der beliebig viele BibTeX-Records enthalten sein können).
 */
class BibtexListCommand extends Command
{
    protected function configure()
    {
        $help = <<<EOT
List the available BibTeX mapping configurations.
EOT;

        $this->setName('bibtex:list')
            ->setDescription('List available BibTeX mappings')
            ->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bibtex = BibtexService::getInstance();

        $mappings = $bibtex->listAvailableMappings();

        $maxlen = max(array_map('strlen', $mappings));

        foreach ($mappings as $name) {
            $mapping = $bibtex->getFieldMapping($name);

            $line = sprintf("<fg=green>%{$maxlen}s</> - %2s", $name, $mapping->getDescription());

            $output->writeln($line);
        }
    }
}
