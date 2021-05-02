# OPUS4 BibTeX Module

This module supports importing documents in the BibTeX format.

While exporting documents in BibTeX is already working in OPUS4, the code is not yet located in this module.

Importing BibTeX without user interaction is not really possible because BibTeX files come in many flavors often
with custom fields that are not part of any standard. Therefore, it is necessary to allow the user to decide on 
custom mappings and interpretations of the fields in the specific file.

## Requirements

For processing of special characters the *pandoc* tool is needed by the BibTeX parser of OPUS4. 
In Ubuntu / Debian based Linux systems it can be installed using `apt`.

```shell
$ sudo apt install pandoc
```

## Configuration Options

### Import Configuration

The whole import process of BibTeX records is controlled by the settings configured in `import.ini`.

The configuration file `import.ini` allows you to register an arbitrary number of field mappings (see above). 
Each field mapping is given by a separate line where the value (on the right-hand side of `=`) refers to the
name of an external JSON field mapping configuration file:

```ini
fieldMappings[] = custom-field-mapping.json
```

### Document Type Mapping

An important step in mapping BibTex records to OPUS4 metadata documents is to determine the OPUS document type 
a given BibTex type is mapped to. `import.ini` allows you to define a mapping between BibTeX type `btype` and 
OPUS4 document type `otype` in the following manner:

```ini
documentTypeMapping[btype] = otype
```

Please note that the BibTeX type names used in the type mapping are not restricted to the 14 official BibTeX types.
It is allowed to define custom type mappings for unknown BibTeX types, e.g.

```ini
documentTypeMapping[journal] = article
```

In case the type of a BibTeX record is not contained in the document type mapping, you can provide a default
OPUS4 document type that is used as a fallback:

```ini
defaultDocumentType = misc
```

### Field Mapping

A field mapping specifies how the values in certain BibTeX fields are mapped onto OPUS4 
metadata fields. A field mapping is provided by a configuration file. At the moment OPUS4 
supports configuration files in the JSON format only. It is possible to manage several
field mappings in one OPUS4 instance. 

A default field mapping is given by `default-field-mapping.json`.

The *minimal* definition of a field mapping configuration file looks like:

```json
{
  "name": "default",
  "description": "Default BibTeX Mapping Configuration.",
  "rules": [
    {
      "name": "1stRule",
      "class": "SomeClass"
    }    
  ]
}
```

where `name` is the (arbitrary) name of the field mapping and `description` allows you to specify the
intended use case for which the given field mapping is suited for. A field mapping consists of one to many
rules, given in the `rules` list. Please note, that the order of the given rules is meaningful – rules that
occur later in the list have a higher precedence and can overwrite previously assigned values of 
OPUS4 document fields with new values.

Each mapping rule must specify at lease a `name` (`1stRule` in this case) and the name of a PHP class (in
`class`) that defines the mapping logic (`SomeClass` in this case). The PHP class has to be located in 
the namespace `Opus\Bibtex\Import\Rules`. OPUS4 provides you with plenty of predefined PHP classes that can 
be reused to formulate custom field mappings, even complex ones. 

Additionally, you can even use custom PHP classes in field mappings. Please note that custom PHP classes need to
implement the `IRule` interface and has to be located in the aforementioned namespace in order to be considered 
by the BibTeX field mapper.

If the class in use allows optional configuration (by appropriate setter methods), you can pass in configuration
property values in the following manner:

```json
{
  "name": "volume",
  "class": "SimpleRule",
  "properties": {
    "bibtexFieldName": "volume",
    "opusFieldName": "Volume"
  }
}
```

In this example the rule (with name `volume`) maps the BibTeX field `volume` to OPUS document field `Volume` 
by handing the mapping process to `SimpleRule` – a class that is shipped with OPUS4 and performs trivial
one-to-one mappings between BibTeX fields and OPUS document fields without further processing.

Another example rule list entry is given by:

```json
{
  "name": "belongsToBibliography",
  "class": "ConstantValueRule",
  "properties": {
    "opusFieldName": "BelongsToBibliography",
    "value": "0"
  }
}
```

`ConstantValueRule` allows you to set fixed values to OPUS document fields – in this case `BelongsToBibliography`
is set to `0` (independently of certain BibTeX fields).
