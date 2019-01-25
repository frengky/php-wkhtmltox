# PHP WkHtmlToX

This package contains clean, easy wrapper class for **wkhtmltopdf** and **wkhtmltoimage**.
Wkhtmltopdf is the top choice to render your html into a pdf [read more](https://wkhtmltopdf.org/).

This classes are demonstrate working directly (read/write) with wkhtmltopdf's process (stdin and stdout), without creating any temporary files.

## Usage

Convert HTML string into a PDF file:
```php
<?php
use Frengky\WkHtml\PDF;

$htmlString = 'Hello, <strong>World</strong>!'
$result = PDF::fromHtml($contents)
           ->set('--page-size',  'A4') // the args to wkhtmltopdf
           ->set('--orientation',  'Portrait')
           ->saveAs('storage/files/test-success.pdf');

if ($result) {
   echo "Generated pdf output file path: " . $result;
}

```
It can also accept stream. See PSR-7 *StreamInterface* and check out [guzzle/psr7](https://github.com/guzzle/psr7):
```php
$htmlSource = Psr7\stream_for('Hello, <strong>World</strong>!');
$result = PDF::fromHtml($contents)
           ->set('--page-size',  'A4') // the args to wkhtmltopdf
           ->set('--orientation',  'Portrait')
           ->render(function(StreamInterface $output) {
				// Do something with the stream
				file_put_contents('output.pdf', $output->getContents());
			});

```
> This is the recommended way if you working with *large* pdf file output.
