# OPUS4 BibTeX Module

This module supports *importing* documents in the BibTeX format.

While *exporting* documents' metadata in the BibTeX format is already working in OPUS4, the code is not yet located 
in this module.

Importing BibTeX without user interaction is not really possible because BibTeX files come in many flavors often
with custom fields that are not part of any standard. Therefore, it is necessary to allow the user to decide on 
custom mappings and interpretations of the fields in the specific BibTeX file.

## Requirements

For processing of special characters in BibTeX files the **Pandoc** tool is needed by the BibTeX parser of OPUS4.

Please make sure that you install / use a recent version of Pandoc, at least version 2.0. The current
implementation was not tested against older Pandoc versions.

In Ubuntu / Debian based Linux systems Pandoc can be installed using `apt`.

```shell
$ sudo apt install pandoc
```

To check the version of Pandoc that has been installed, run:

```shell
$ pandoc -v
```

To check for the latest version number of Pandoc, you can browse to https://github.com/jgm/pandoc/releases.

The BibTeX import in this module was developed and heavily tested against Pandoc version 2.9 (released in May 2020) 
which is shipped with recent Ubuntu versions (20.10 and 21.04).

## Configuration Options

The default settings for the import of BibTeX files are located in `src\Import\import.ini`. That file should
not be edited locally. It is possible to extend or modify the settings in the global OPUS 4 configuration.

### Field Mappings 

You can register an arbitrary number of field mappings (see below). Each field mapping needs a name, that is part of 
the key. You can use the name `default` to replace the default mappingThe only option supported right now is `file` 
for the name of an external JSON field mapping configuration file. If the file is not specified with an absolute path,
the base path needs to be specified in `bibtex.mappingsBasePath`.

```ini
bibtex.mappingsBasePath = APPLICATION_PATH "/application/config/bibtex"
bibtex.mappings.default.file = custom-default-mapping.json
bibtex.mappings.custom1.file = custom-mapping.json
```

The default field mapping is given by `src/Import/default-mapping.json`.

### Document Type Mapping

An important step in mapping BibTex records to OPUS4 metadata documents is to determine the OPUS document type 
a given BibTex type is mapped to. You can define custom mappings between a BibTeX type `btype` and 
a OPUS4 document type `otype` in the following manner:

```ini
bibtex.entryTypes.btype = otype
```

Please note that BibTeX type names used in the type mapping are not restricted to the 14 official BibTeX types.
It is allowed to define custom type mappings for unknown BibTeX types, e.g.

```ini
bibtex.entryTypes.journal = article
```

In case the type of a BibTeX record is not contained in the document type mapping, you can provide a default
OPUS4 document type that is used as a fallback. The default fallback is `misc`.

```ini
bibtex.defaultDocumentType = misc
```

## Field Mapping

A field mapping specifies how values in certain BibTeX fields are mapped onto OPUS4 
metadata fields. A field mapping is provided by a configuration file. At the moment OPUS4 
supports configuration files in the JSON format only. It is possible to manage several
field mappings in one OPUS4 instance. 

A default field mapping is given by `src/Import/default-mapping.json`.

The *minimal* definition of a field mapping configuration file looks like:

```json
{
  "name": "default",
  "description": "Default BibTeX Mapping Configuration.",
  "mapping": [
    …
  ]
}
```

where `name` is the (arbitrary) name of the field mapping and `description` allows you to specify the
intended use case for which the given field mapping is suited for. A field mapping consists of one to many
rules, given in the `mapping` list. Please note, that the order of the given mapping rules is meaningful – 
mapping rules that occur later in the list have a higher precedence and can overwrite previously assigned
values of OPUS4 document fields with new values.

### Field Mapping Rules

Each mapping rule must specify at least a name in the corresponding `name` key, e.g.

```json
{
  "name": "publishedYear"
}
```

In this case the BibTeX processor tries to instantiate the class `PublishedYear` (in the default namespace
of rule classes, `Opus\Bibtex\Import\Rules`). If the rule class does not exist, an instance of `SimpleRule`
is created. This class performs a simple one-to-one-mapping between a BibTeX field (given in `bibtexField`)
and an OPUS metadata field (given in `opusField`) without further processing, e.g.

```json
{
  "name": "issue",
  "options": {
    "bibtexField": "number",
    "opusField": "Issue"
  }
}
```

You can add custom rule classes (which needs to implement `RuleInterface`), even from other namespaces.
In this case you need to specify the namespace explicitly, e.g.

```json
{
  "name": "year",
  "class": "Opus\\Bibtex\\Custom\\Year"
}
```

OPUS4 provides you with plenty of pre-defined rule mapping classes that can be reused to formulate custom 
field mappings, even complex ones (see below).

If the rule class in use allows optional configuration (by appropriate setter methods), you can pass in 
configuration property values in the following manner:

```json
{
  "name": "pdfUrl",
  "class": "Note",
  "options": {
    "bibtexField": "pdfurl",
    "messagePrefix": "URL of the PDF: ",
    "visibility": "public"
  }
}
```

In this example the processor maps the content of BibTeX field `pdfurl` to the value (`message`) of an OPUS `Note` 
object by handing the mapping process over to an instance of `Note` – a class that is shipped with OPUS4. 
Additionally, the options `messagePrefix` and `visibility` allow to add a fixed prefix to the `Note`'s message and 
to control the visibility of the `Note`.

Another example mapping entry is given by the rule:

```json
{
  "name": "belongsToBibliography",
  "options": {
    "value": false
  }
}
```

which sets the value of the `belongsToBibliography` field of an OPUS document to a fixed value (`false` in 
this example) independently of certain BibTeX fields.

### Pre-defined rule classes

OPUS4 provides a number of pre-defined rule classes (located in namespace `Opus\Bibtex\Import\Rules`):

| Class Name              | Description |
|-------------------------|-------------|
| `Arxiv`                 | adds an identifier of type `arxiv` |
| `BelongsToBibliography` | sets OPUS document field `belongsToBibliography` to a fixed value |
| `DocumentType`          | sets the OPUS document type according to the configured type mapping |
| `Doi`                   | adds an identifier of type `doi` |
| `Isbn`                  | adds an identifier of type `isbn` |
| `Issn`                  | adds an identifier of type `issn` |
| `Language`              | sets OPUS document field `language` to a fixed value |
| `Note`                  | adds a note (additionally, allows to specify `messagePrefix` and `visibilty`) |
| `Pages`                 | handling of page-specific OPUS metadata fields (`PageFirst`, `PageLast`, `PageNumber`) |
| `Person`                | adds a person |
| `PublishedYear`         | sets the OPUS document field `publishedYear` |
| `SourceData`            | adds the imported BibTeX record to enrichment `opus.import.data` |
| `SourceDataHash`        | adds MD5 hash sum of the imported BibTeX record to enrichment `opus.import.dataHash` |
| `Subject`               | adds a subject (default: language `eng`, type `uncontrolled`) |
| `TitleMain`             | adds a main title |
| `TitleParent`           | adds a parent title |
| `Umlauts`               | converts non-standard escaped umlauts to their corresponding unicode characters |

### Base rule classes

OPUS4 supports several ways to create custom rule implementations. Each rule class has to implement the interface
`RuleInterface`.

A custom rule implementation can be defined by creating a class that extends one of the following base rule classes:

| Base Class Name       | Description |
|-----------------------|-------------|
| `SimpleRule`          | defines a one-to-one mapping between BibTeX field and OPUS4 metadata field |
| `ConstantValue`       | adds a constant value to one OPUS4 metadata field |
| `ConstantValues`      | adds constant values to multiple OPUS4 metadata fields |
| `AbstractArrayRule`   | abstract base class for mapping rules that create array-based OPUS4 metadata fields |
| `AbstractComplexRule` | abstract base class for mapping rules that create or manipulate multiple OPUS4 metadata field |

Alternatively, you can extend one of the pre-defined rule classes listed above and adapt it to your needs.
