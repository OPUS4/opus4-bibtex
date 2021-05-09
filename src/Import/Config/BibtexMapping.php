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
 * @package     Opus\Bibtex\Import\Configuration
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Config;

use Opus\Bibtex\Import\Rules\SimpleRule;

class BibtexMapping
{
    /**
     * Eindeutiger Name der Regelkonfiguration für die Auswahl.
     *
     * @var string
     */
    private $name;

    /**
     * Textuelle Beschreibung der Regelkonfiguration, z.B. für die Anzeige im Frontend.
     *
     * @var string
     */
    private $description;

    /**
     * Liste der anzuwendenden Regeln. Die Liste kann durch entsprechende Methoden verändert werden.
     * Jede Regel ist über einen eindeutigen Namen referenzierbar.
     *
     * @var array
     */
    private $rules = [];

    /**
     * Liefert den Namen der Mapping-Konfiguration. Diese wird z.B. für die Auswahl der
     * Mapping-Konfiguration in der Administration benötigt.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setzt den Namen der Mapping-Konfiguration.
     *
     * @param $name Name der Mapping-Konfiguration
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Liefert eine Beschreibung der Mapping-Konfiguration zurück. In dieser können
     * Hinweise oder Bemerkung zum vorliegenden Mapping hinterlegt werden, so dass
     * die spätere Auswahl der Mapping-Konfiguration erleichtert wird.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setzt die Beschreibung der Mapping-Konfiguration.
     *
     * @param $description Beschreibung der Mapping-Konfiguration
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Gibt die momentan konfigurierte Liste der Regeln zurück.
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Fügt die übergebene Regel am Anfang der Regelliste hinzu, d.h. die übergebene Regel
     * wird vor der Ausführung aller anderen Regeln ausgeführt.
     *
     * @param $name Name der Regel
     * @param $rule die hinzuzufügende Regel
     */
    public function prependRule($name, $rule)
    {
        $this->rules = array_merge([ $name => $rule ], $this->rules);
        return $this;
    }

    /**
     * Fügt die übergebene Regel am Ende der Regelliste hinzu, d.h. die übergebene Regel
     * wird erst nach der Ausführung aller anderen Regeln ausgeführt.
     *
     * @param $name Name der Regel
     * @param $rule die hinzuzufügende Regel
     */
    public function addRule($name, $rule = null)
    {
        if (is_null($rule)) {
            $rule = $this->getRuleInstance(['name' => $name]);
        }
        $this->rules[$name] = $rule;
        return $this;
    }

    /**
     * Überschreibt die unter dem Namen registrierte Regel oder fügt die Regel am Ende der Regelliste hinzu, falls
     * unter dem übergebenen Namen noch keine Regel existiert.
     *
     * @param $name Name der Regel
     * @param $rule die zu ersetzende (oder hinzuzufügende Regel)
     */
    public function updateRule($name, $rule)
    {
        $this->rules[$name] = $rule;
        return $this;
    }

    /**
     * Entfernt die unter dem übergebenen Namen abgelegte Regel, sofern eine solche Regel existiert.
     *
     * @param $name Name der Regel
     */
    public function removeRule($name)
    {
        if (array_key_exists($name, $this->rules)) {
            unset($this->rules[$name]);
        }
        return $this;
    }

    /**
     * Setzt die Liste der Regeln zurück, so dass die Anwendung der Mapping-Konfiguration einer No-Op entspricht.
     */
    public function resetRules()
    {
        $this->rules = [];
        return $this;
    }

    /**
     * Erlaubt das Setzen von Mappingregeln auf Basis des übergebenen Konfigurationsarrays.
     * @param array $rules
     */
    public function setRules($rules)
    {
        $this->resetRules();
        foreach ($rules as $rule) {
            $name = $rule['name'];
            $ruleInstance = $this->getRuleInstance($rule);
            $this->addRule($name, $ruleInstance);
            if (array_key_exists('options', $rule)) {
                foreach ($rule['options'] as $propName => $propValue) {
                    $setter = 'set' . ucfirst($propName);
                    $ruleInstance->$setter($propValue);
                }
            }
        }
        return $this;
    }

    private function getRuleInstance($rule)
    {
        if (array_key_exists('class', $rule)) {
            $className = ucfirst($rule['class']);
        } else {
            $className = ucfirst($rule['name']);
        }
        if (strpos($className, '\\') === false) {
            $className = "Opus\Bibtex\Import\Rules\\$className";
        }
        if (is_null($className) || ! class_exists($className)) {
            return new SimpleRule();
        }
        return new $className;
    }
}
