# PHP WkHtmlToX

[Wkhtmltopdf](https://wkhtmltopdf.org/) is the top choice to render your html into a pdf or image.
This package is a clean and easy wrapper for **wkhtmltopdf** and **wkhtmltoimage**.
This package is working directly with wkhtmltopdf's process (stdin and stdout) without creating any temporary files.

## Requirements
Install the [Wkhtmltopdf](https://wkhtmltopdf.org/) binaries for your operating system, make sure **wkhtmltopdf** and **wkhtmltoimage** executable in your PATH.

## Installation
Install the package via `Composer`
```
composer require frengky/php-wkhtmltox
```

## Usage
Convert HTML string into a PDF file:
```php
<?php
use Frengky\WkHtml\PDF;

$htmlString = 'Hello, <strong>World</strong>!'
$outputPath = PDF::fromHtml($contents)
           ->set('--page-size',  'A4') // the args to wkhtmltopdf
           ->set('--orientation',  'Portrait')
           ->saveAs('storage/files/test-success.pdf');

if ($result) {
   echo "Generated pdf output file path: " . $outputPath;
}

```
You can also working with stream. See PSR-7 *StreamInterface* and check out [guzzle/psr7](https://github.com/guzzle/psr7):
```php
$htmlSource = Psr7\stream_for('Hello, <strong>World</strong>!');
$retval = PDF::fromHtml($htmlSource)
           ->set('--page-size',  'A4') // the args to wkhtmltopdf
           ->set('--orientation',  'Portrait')
           ->render(function(StreamInterface $output) {
				// Do something with the stream
				file_put_contents('output.pdf', $output->getContents());
			});

```
> This is the recommended way if you working with *large* pdf file output.
