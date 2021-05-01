<?php


namespace Opus\Bibtex\Import\Rules;


class Person extends ArrayRule
{
    public function __construct()
    {
        return parent::__construct(
            'xxx', // FIXME wird durch setBibtexFieldName gesetzt
            'Person',
            function ($value) {
                $persons = explode(' and ', $value);
                $result = [];
                foreach ($persons as $person) {
                    $result[] = array_merge(['Role' => $this->bibtexFieldName], $this->extractNameParts($person));
                }
                return $result;
            }
        );
    }

    protected function extractNameParts($name)
    {
        $name = trim($name);
        $posFirstComma = strpos($name, ',');
        if ($posFirstComma !== false) {
            // Nachname getrennt durch Komma mit Vorname(n)
            // alles nach dem ersten Komma wird hierbei als Vorname interpretiert
            $result = [
                'LastName' => trim(substr($name, 0, $posFirstComma))
            ];
            if ($posFirstComma < strlen($name) - 1) {
                $result['FirstName'] = trim(substr($name, $posFirstComma + 1));
            }
            return $result;
        }

        // mehrere Namensbestandteile sind nicht durch Komma getrennt
        // alles nach dem ersten Leerzeichen wird als Nachname aufgefasst
        // kommt kein Leerzeichen wird, so wurde vermutlich nur der Nachname angegeben
        $posFirstSpace = strpos($name, ' ');
        if ($posFirstSpace === false) {
            return [
                'LastName' => $name
            ];
        }

        $posLastPeriod = strrpos($name, '.');
        if ($posLastPeriod === false) {
            // letztes Zeichen kann kein Leerzeichen sein, daher kein Vergleich der Länge von $name mit $posFirstSpace
            return [
                'FirstName' => trim(substr($name, 0, $posFirstSpace)),
                'LastName' => trim(substr($name, $posFirstSpace + 1))
            ];
        }

        // falls Namensbestandteile abgekürzt werden, so betrachte alles nach dem letzten Punkt als Nachnamen
        $result = [
            'FirstName' => trim(substr($name, 0, $posLastPeriod + 1)),
        ];
        if ($posLastPeriod < strlen($name) - 1) {
            $result['LastName'] = trim(substr($name, $posLastPeriod + 1));
        }
        return $result;
    }
}
