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
 * @package     Opus\Bibtex\Import\Config
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import\Config;

use Opus\Bibtex\Import\Rules\ConstantValues;
use Opus\Bibtex\Import\Rules\RuleInterface;
use Opus\Bibtex\Import\Rules\SimpleRule;

use function array_key_exists;
use function array_merge;
use function class_exists;
use function strpos;
use function ucfirst;

/**
 * Ein BibTeX-Mapping definiert, wie die einzelnen BibTeX-Felder verarbeitet und auf das OPUS4-Datenmodell
 * abgebildet werden. Jedes BibTeX-Mapping hat einen Namen, eine Beschreibung und eine Menge von Regeln, die
 * nacheinander ausgeführt werden.
 */
class BibtexMapping
{
    /**
     * Eindeutiger Name der Regelkonfiguration für die Auswahl, z.B. in Administrations-Frontend oder im CLI-Kommando.
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setzt den Namen der Mapping-Konfiguration.
     *
     * @param string $name Name der Mapping-Konfiguration
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Liefert eine Beschreibung der Mapping-Konfiguration zurück. In dieser können
     * Hinweise oder Bemerkungen zum vorliegenden Mapping hinterlegt werden, so dass
     * die spätere Auswahl der Mapping-Konfiguration im Frontend erleichtert wird.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setzt die Beschreibung der Mapping-Konfiguration.
     *
     * @param string $description textuelle Beschreibung der Mapping-Konfiguration
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Gibt die momentan konfigurierte Liste der Mapping-Regeln zurück.
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Fügt die übergebene Regel am Anfang der Regelliste hinzu, d.h. die übergebene Regel
     * wird vor der Ausführung aller anderen Regeln ausgeführt. Existiert bereits eine Regel mit dem
     * übergebenen Namen, so wird diese Regel vor dem Hinzufügen der übergebenen Regel entfernt.
     *
     * @param string $name Name der Regel
     * @param RuleInterface|null $rule die hinzuzufügende Regel (wenn null, dann wird die Regel aus dem Namen abgeleitet)
     * @return $this
     */
    public function prependRule($name, $rule = null)
    {
        if ($rule === null) {
            $rule = $this->getRuleInstance(['name' => $name]);
        }

        $this->removeRule($name);
        $this->rules = array_merge([$name => $rule], $this->rules);
        return $this;
    }

    /**
     * Fügt die übergebene Regel am Ende der Regelliste hinzu, d.h. die übergebene Regel
     * wird erst nach der Ausführung aller anderen Regeln ausgeführt. Existiert bereits eine Regel mit dem
     * übergebenen Namen, so wird diese Regel vor dem Hinzufügen der übergebenen Regel entfernt.
     *
     * @param string $name Name der Regel
     * @param RuleInterface|null $rule die hinzuzufügende Regel (wenn null, dann wird die Regel aus dem Namen abgeleitet)
     * @return $this
     */
    public function addRule($name, $rule = null)
    {
        if ($rule === null) {
            $rule = $this->getRuleInstance(['name' => $name]);
        }

        $this->removeRule($name);
        $this->rules[$name] = $rule;
        return $this;
    }

    /**
     * Überschreibt die unter dem Namen registrierte Regel oder fügt die Regel am Ende der Regelliste hinzu, falls
     * unter dem übergebenen Namen noch keine Regel existiert.
     *
     * @param string $name Name der Regel
     * @param RuleInterface $rule die zu ersetzende (oder hinzuzufügende) Regel
     * @return $this
     */
    public function updateRule($name, $rule = null)
    {
        if ($rule === null) {
            $rule = $this->getRuleInstance(['name' => $name]);
        }

        if (array_key_exists($name, $this->rules)) {
            // Regel mit bekanntem Namen wird ersetzt
            $this->rules[$name] = $rule;
        } else {
            $this->addRule($name, $rule);
        }
        return $this;
    }

    /**
     * Entfernt die unter dem übergebenen Namen abgelegte Regel, sofern eine solche Regel existiert.
     *
     * @param string $name Name der Regel
     * @return $this
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
     *
     * @return $this
     */
    public function resetRules()
    {
        $this->rules = [];
        return $this;
    }

    /**
     * Erlaubt das Setzen von Mapping-Regeln auf Basis des übergebenen Konfigurationsarrays.
     *
     * @param array $rules
     * @return $this
     *
     * TODO remove dependency on ConstantValues - move responsiblity for processing options into class
     */
    public function setRules($rules)
    {
        $this->resetRules();

        foreach ($rules as $rule) {
            $name         = $rule['name'];
            $ruleInstance = $this->getRuleInstance($rule);
            $this->addRule($name, $ruleInstance);
            if (array_key_exists('options', $rule)) {
                $options = $rule['options'];
                if ($ruleInstance instanceof ConstantValues) {
                    // Spezialbehandlung: hier führen die Options-Einträge nicht zu Setter-Aufrufen auf der Instanz der
                    // Regelklasse (ConstantValues hat keine passenden Setter)
                    $ruleInstance->setOptions($options);
                } else {
                    foreach ($options as $propName => $propValue) {
                        $setter = 'set' . ucfirst($propName);
                        $ruleInstance->$setter($propValue);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Erzeugt eine Instanz der Mapping-Regel, die durch die übergebene Konfiguration beschrieben ist.
     *
     * @param array $rule Konfiguration der Mapping-Regel
     * @return RuleInterface Instanz der Mapping-Regel
     */
    private function getRuleInstance($rule)
    {
        if (array_key_exists('class', $rule)) {
            $className = ucfirst($rule['class']);
        } else {
            // ist in der Konfiguration kein expliziter Klassenname gesetzt, so wird der Klassenname aus dem Namen
            // der Mapping-Regel abgeleitet
            $className = ucfirst($rule['name']);
        }

        if (strpos($className, '\\') === false) {
            // enthält der Klassenname keinen Namespace, so wird der Standard-Namespace, in den die vordefinierten
            // Mapping-Regel-Implementierungen liegen, verwendet
            $className = "Opus\Bibtex\Import\Rules\\$className";
        }

        if ($className === null || ! class_exists($className)) {
            // kann keine Klasse abgeleitet werden, so wird auf SimpleRule als Fallback zurückgegriffen, so dass eine
            // 1:1-Abbildung zwischen einem BibTeX-Feld und einem OPUS-Metadatenfeld erfolgt
            return new SimpleRule();
        }
        return new $className();
    }
}
