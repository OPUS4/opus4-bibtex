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
 * @package     Opus\Bibtex\Import
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import;

use Opus\Bibtex\Import\Rules\ArrayRule;
use Opus\Bibtex\Import\Rules\ComplexRule;
use Opus\Bibtex\Import\Rules\ConstantValueRule;
use Opus\Bibtex\Import\Rules\DocumentTypeMapping;
use Opus\Bibtex\Import\Rules\SimpleRule;

class DefaultMappingConfiguration extends AbstractMappingConfiguration
{
    public function __construct()
    {
        $this->name = 'default';
        $this->description = 'Default BibTeX Mapping Configuration';
        $this
            // ptype ist kein Standard-BibTeX-Feld: das Feld ptype kann genutzt werden, um das Typ-Mapping
            // auf Basis des BibTeX-Types (die Zeichenkette nach @) zu umgehen
            // ist im BibTeX-Record kein Feld ptype vorhanden, so wird der Typ aus der Zeichenkette nach @ abgeleitet
            ->appendRule(new SimpleRule(
            'ptype',
            'Type',
            function ($value) {
                if (array_key_exists($value, DocumentTypeMapping::$MAPPING)) {
                    return DocumentTypeMapping::$MAPPING[$value];
                }
            }))
            ->appendRule(new SimpleRule(
                'type',
                'Type',
                function ($value) {
                    if (array_key_exists($value, DocumentTypeMapping::$MAPPING)) {
                        return DocumentTypeMapping::$MAPPING[$value];
                    } else {
                        return DocumentTypeMapping::$DEFAULT_OPUS_TYPE;
                    }
                }
            ))
            ->appendRule(new SimpleRule('number', 'Issue'))
            ->appendRule(new SimpleRule('volume', 'Volume'))
            ->appendRule(new SimpleRule(
                'pages',
                'PageFirst',
                function ($value) {
                    $value = str_replace(['--', '––', '–'], '-', $value);
                    $parts = explode('-', $value, 2);
                    return trim($parts[0]);
                }
            ))
            ->appendRule(new SimpleRule(
                'pages',
                'PageLast',
                function ($value) {
                    $value = str_replace(['--', '––', '–'], '-', $value);
                    $parts = explode('-', $value, 2);
                    if (count($parts) == 2) {
                        return trim($parts[1]);
                    }
                    return trim($parts[0]);
                }
            ))
            ->appendRule(new ConstantValueRule(
                'PageNumber',
                function ($documentMetadata) {
                    $pageFirst =
                        array_key_exists('PageFirst', $documentMetadata) ? intval($documentMetadata['PageFirst']) : 0;
                    $pageLast =
                        array_key_exists('PageLast', $documentMetadata) ? intval($documentMetadata['PageLast']) : 0;
                    if ($pageFirst > 0 && $pageLast > 0 && $pageLast >= $pageFirst) {
                        return 1 + $pageLast - $pageFirst;
                    }
                }
            ))
            ->appendRule(new SimpleRule(
                'year',
                'PublishedYear',
                function ($value) {
                    $value = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($value) == 4) {
                        return $value;
                    }
                }
            ))
            ->appendRule(new ArrayRule(
                'issn',
                'Identifier',
                function ($value) {
                    return [
                        'Value' => $value,
                        'Type' => 'issn'
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'isbn',
                'Identifier',
                function ($value) {
                    return [
                        'Value' => $value,
                        'Type' => 'isbn'
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'doi',
                'Identifier',
                function ($value) {
                    if (strtolower(substr($value, 0, 4)) === 'doi:') {
                        $value = trim(substr($value, 4)); // Präfix doi: abschneiden
                    }
                    return [
                        'Value' => $value,
                        'Type' => 'doi'
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'arxiv',
                'Identifier',
                function ($value) {
                    $type = 'url';

                    $baseUrl1 = 'http://arxiv.org/abs/';
                    $baseUrl2 = 'https://arxiv.org/abs/';
                    if (substr($value, 0, strlen($baseUrl1)) == $baseUrl1 ||
                        substr($value, 0, strlen($baseUrl2)) == $baseUrl2) {
                        $type = 'arxiv';
                        // URL-Präfix abschneiden, so dass nur die ArXiv-ID übrigbleibt
                        $value = preg_replace('#https?://arxiv.org/abs/#i', '', $value);
                    } else if (strtolower(substr($value, 0, 6)) === 'arxiv:') {
                        $type = 'arxiv';
                        $value = substr($value, 6); // Präfix 'arxiv:' abschneiden
                    }

                    return [
                        'Value' => $value,
                        'Type' => $type
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'title',
                'TitleMain',
                function ($value) {
                    return [
                        'Language' => 'eng',
                        'Value' => $this->deleteBrace($value),
                        'Type' => 'main'
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'journal',
                'TitleParent',
                function ($value) {
                    return [
                        'Language' => 'eng',
                        'Value' => $this->deleteBrace($value),
                        'Type' => 'parent'
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'booktitle',
                'TitleParent',
                function ($value) {
                    return [
                        'Language' => 'eng',
                        'Value' => $this->deleteBrace($value),
                        'Type' => 'parent'
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'keywords',
                'Subject',
                function ($value) {
                    $keywords = explode(', ', $value);
                    $result = [];
                    foreach ($keywords as $keyword) {
                        $result[] = [
                            'Language' => 'eng',
                            'Type' => 'uncontrolled',
                            'Value' => $this->deleteBrace($keyword)
                        ];
                    }
                    return $result;
                }
            ))
            ->appendRule(new ArrayRule(
                'pdfurl',
                'Note',
                function ($value) {
                    return [
                        'Visibility' => 'public',
                        'Message' => 'URL of the PDF: ' . $value
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'slides',
                'Note',
                function ($value) {
                    return [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Slides: ' . $value
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'annote',
                'Note',
                function ($value) {
                    return [
                        'Visibility' => 'public',
                        'Message' => 'Additional Note: ' . $value
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'summary',
                'Note',
                function ($value) {
                    return [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Abstract: ' . $value
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'code',
                'Note',
                function ($value) {
                    return [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Code: ' . $value
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'poster',
                'Note',
                function ($value) {
                    return [
                        'Visibility' => 'public',
                        'Message' => 'URL of the Poster: ' . $value
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                'author',
                'Person',
                function ($value) {
                    $persons = explode(' and ', $value);
                    $result = [];
                    foreach ($persons as $person) {
                        $result[] = array_merge(['Role' => 'author'], $this->extractNameParts($person));
                    }
                    return $result;
                }
            ))
            ->appendRule(new ArrayRule(
                'editor',
                'Person',
                function ($value) {
                    $persons = explode(' and ', $value);
                    $result = [];
                    foreach ($persons as $person) {
                        $result[] = array_merge(['Role' => 'editor'], $this->extractNameParts($person));
                    }
                    return $result;
                }
            ))
            ->appendRule(new ArrayRule(
                '_original',
                'Enrichment',
                function ($value) {
                    return [
                        'KeyName' => self::SOURCE_DATA_KEY,
                        'Value' => $value
                    ];
                }
            ))
            ->appendRule(new ArrayRule(
                '_original',
                'Enrichment',
                function ($value) {
                    return [
                        'KeyName' => self::SOURCE_DATA_HASH_KEY,
                        'Value' => self::HASH_FUNCTION . ':' . (self::HASH_FUNCTION)($value)
                    ];
                }
            ))
            ->appendRule(new ConstantValueRule(
                'Language',
                function () {
                    return 'eng';
                }
            ))
            ->appendRule(new ConstantValueRule(
                'BelongsToBibliography',
                function () {
                    return '0';
                }
            ))
            ->appendRule(new ComplexRule(
                function ($fieldValues, &$documentMetadata) {
                    // behandelt Umlaute, die im BibTeX-File nicht korrekt angegeben wurden (siehe OPUSVIER-4216)
                    foreach ($documentMetadata as $fieldName => $fieldValue) {
                        if (is_array($fieldValue)) {
                            foreach ($fieldValue as $subFieldIndex => $subFieldValue) {
                                if ($fieldName === 'Enrichment' &&
                                    ($subFieldValue['KeyName'] === AbstractMappingConfiguration::SOURCE_DATA_HASH_KEY ||
                                        $subFieldValue['KeyName'] === AbstractMappingConfiguration::SOURCE_DATA_KEY)) {
                                    continue; // der Original-BibTeX-Record soll nicht verändert werden
                                }
                                foreach ($subFieldValue as $name => $value) {
                                    $convertedFieldValue = $this->convertUmlauts($value);
                                    if ($convertedFieldValue !== false) {
                                        $documentMetadata[$fieldName][$subFieldIndex][$name] = $convertedFieldValue;
                                    }
                                }
                            }
                        } else {
                            $convertedFieldValue = $this->convertUmlauts($fieldValue);
                            if ($convertedFieldValue !== false) {
                                $documentMetadata[$fieldName] = $convertedFieldValue;
                            }
                        }
                    }
                }
            ))
        ;
    }
}
