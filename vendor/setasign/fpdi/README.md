FPDI - Free PDF Document Importer
=================================

<<<<<<< HEAD
[![Latest Stable Version](https://poser.pugx.org/setasign/fpdi/v/stable.svg)](https://packagist.org/packages/setasign/fpdi) [![Total Downloads](https://poser.pugx.org/setasign/fpdi/downloads.svg)](https://packagist.org/packages/setasign/fpdi) [![Latest Unstable Version](https://poser.pugx.org/setasign/fpdi/v/unstable.svg)](https://packagist.org/packages/setasign/fpdi) [![License](https://poser.pugx.org/setasign/fpdi/license.svg)](https://packagist.org/packages/setasign/fpdi)

A clone of [FPDI](https://www.setasign.com/fpdi) for GitHub/[Composer](https://packagist.org/packages/setasign/fpdi).

FPDI is a collection of PHP classes facilitating developers to read pages from existing PDF documents and use them as templates in FPDF, which was developed by Olivier Plathey. Apart from a copy of FPDF, FPDI does not require any special PHP extensions.

## Installation with [Composer](https://packagist.org/packages/setasign/fpdi)

FPDI is an add-on for [FPDF](http://fpdf.org/). Additionally FPDI can be used with [TCPDF](http://www.tcpdf.org/).
For completion we added a [FPDF repository](https://github.com/Setasign/FPDF) which simply clones the offical releases.

This package comes without any dependency configuration in the composer.json file. It's up to you to load the desired package as described below.

A basic installation via Composer could be done this way:

```bash
$ composer require setasign/fpdi:1.6.2
```

or you can include the following in your composer.json file:

```json
{
    "require": {
        "setasign/fpdi": "1.6.2"
    }
}
```

### Evaluate Dependencies Automatically

To load dependencies automatically we prepared kind of metadata packages. To use FPDI with FPDF use [this](https://github.com/Setasign/FPDI-FPDF) package:

```json
{
    "require": {
        "setasign/fpdi-fpdf": "1.6.2"
    }
}
```

For TCPDF use [this](https://github.com/Setasign/FPDI-TCPDF):

```json
{
    "require": {
        "setasign/fpdi-tcpdf": "1.6.2"
    }
}
```

### Manual Dependencies

To support both FPDF and TCPDF its up to you to load the preferred package before the classes of FPDI are loaded. By default FPDI will extend FPDF. If the TCPDF class exists, a new FPDF class will be created which will extend TCPDF while FPDI will extend this.
=======
[![Latest Stable Version](https://poser.pugx.org/setasign/fpdi/v/stable.svg)](https://packagist.org/packages/setasign/fpdi)
[![Total Downloads](https://poser.pugx.org/setasign/fpdi/downloads.svg)](https://packagist.org/packages/setasign/fpdi)
[![License](https://poser.pugx.org/setasign/fpdi/license.svg)](https://packagist.org/packages/setasign/fpdi)

:heavy_exclamation_mark: This document refers to FPDI 2. Version 1 is deprecated and development is discontinued. :heavy_exclamation_mark: 

FPDI is a collection of PHP classes facilitating developers to read pages from existing PDF
documents and use them as templates in [FPDF](http://www.fpdf.org), which was developed by Olivier Plathey. Apart
from a copy of [FPDF](http://www.fpdf.org), FPDI does not require any special PHP extensions.

FPDI can also be used as an extension for [TCPDF](https://github.com/tecnickcom/TCPDF) or 
[tFPDF](http://fpdf.org/en/script/script92.php), too.

## Installation with [Composer](https://packagist.org/packages/setasign/fpdi)

Because FPDI can be used with FPDF, TCPDF or tFPDF we haven't added a fixed dependency in the main
composer.json file. You need to add the dependency to the PDF generation library of your choice
yourself.
>>>>>>> 919dddeba74d51ea28d15252ec51fbbb73acb2a4

To use FPDI with FPDF include following in your composer.json file:

```json
{
    "require": {
<<<<<<< HEAD
        "setasign/fpdf": "1.8",
        "setasign/fpdi": "1.6.2"
=======
        "setasign/fpdf": "1.8.*",
        "setasign/fpdi": "^2.5"
>>>>>>> 919dddeba74d51ea28d15252ec51fbbb73acb2a4
    }
}
```

<<<<<<< HEAD
If you are using TCPDF, your have to update your composer.json respectively to:
=======
If you want to use TCPDF, you have to update your composer.json to:
>>>>>>> 919dddeba74d51ea28d15252ec51fbbb73acb2a4

```json
{
    "require": {
<<<<<<< HEAD
        "tecnickcom/tcpdf": "6.2.13",
        "setasign/fpdi": "1.6.2"
=======
        "tecnickcom/tcpdf": "6.6.*",
        "setasign/fpdi": "^2.5"
>>>>>>> 919dddeba74d51ea28d15252ec51fbbb73acb2a4
    }
}
```

<<<<<<< HEAD
Additionally you have to trigger composers autoloader for the TCPDF class before you are initiating FPDI:

```php
class_exists('TCPDF', true); // trigger Composers autoloader to load the TCPDF class
$pdf = new FPDI();
```

=======
If you want to use tFPDF, you have to update your composer.json to:

```json
{
    "require": {
        "setasign/tfpdf": "1.33.*",
        "setasign/fpdi": "^2.3"
    }
}
```

## Manual Installation

If you do not use composer, just require the autoload.php in the /src folder:

```php
require_once('src/autoload.php');
```

If you have a PSR-4 autoloader implemented, just register the src path as follows:
```php
$loader = new \Example\Psr4AutoloaderClass;
$loader->register();
$loader->addNamespace('setasign\Fpdi', 'path/to/src/');
```

## Changes to Version 1

Version 2 is a complete rewrite from scratch of FPDI which comes with:
- Namespaced code
- Clean and up-to-date code base and style
- PSR-4 compatible autoloading
- Performance improvements by up to 100%
- Less memory consumption
- Native support for reading PDFs from strings or stream-resources
- Support for documents with "invalid" data before their file-header
- Optimized page tree resolving
- Usage of individual exceptions
- Several test types (unit, functional and visual tests)

We tried to keep the main methods and logical workflow the same as in version 1 but please
notice that there were incompatible changes which you should consider when updating to
version 2:
- You need to load the code using the `src/autoload.php` file instead of `classes/FPDI.php`.
- The classes and traits are namespaced now: `setasign\Fpdi`
- Page boundaries beginning with a slash, such as `/MediaBox`, are not supported anymore. Remove
  the slash or use a constant of `PdfReader\PageBoundaries`.
- The parameters $x, $y, $width and $height of the `useTemplate()` or `getTemplateSize()`
  method have more logical correct default values now. Passing `0` as width or height will
  result in an `InvalidArgumentException` now.
- The return value of `getTemplateSize()` had changed to an array with more speaking keys
  and reusability: Use `width` instead of `w` and `height` instead of `h`.
- If you want to use **FPDI with TCPDF** you need to refactor your code to use the class `Tcpdf\Fpdi`
(since 2.1; before it was `TcpdfFpdi`) instead of `FPDI`.

## Example and Documentation

A simple example, that imports a single page and places this onto a new created page:

```php
<?php
use setasign\Fpdi\Fpdi;
// or for usage with TCPDF:
// use setasign\Fpdi\Tcpdf\Fpdi;

// or for usage with tFPDF:
// use setasign\Fpdi\Tfpdf\Fpdi;

// setup the autoload function
require_once('vendor/autoload.php');

// initiate FPDI
$pdf = new Fpdi();
// add a page
$pdf->AddPage();
// set the source file
$pdf->setSourceFile("Fantastic-Speaker.pdf");
// import page 1
$tplId = $pdf->importPage(1);
// use the imported page and place it at point 10,10 with a width of 100 mm
$pdf->useTemplate($tplId, 10, 10, 100);

$pdf->Output();            
```

A full end-user documentation and API reference is available [here](https://manuals.setasign.com/fpdi-manual/).
>>>>>>> 919dddeba74d51ea28d15252ec51fbbb73acb2a4
