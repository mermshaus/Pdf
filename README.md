# mermshaus/Pdf

A PHP library for working with PDF documents.

~~~
*** This is an early release. It is NOT intended for production use.
    Interfaces WILL change. ***
~~~


## Install

~~~ bash
$ git clone https://github.com/mermshaus/Pdf.git mermshaus-pdf
$ cd mermshaus-pdf
$ composer install
~~~

There is no release version, yet. There probably will be a package on
[Packagist](https://packagist.org/) in the future. If you already want to add
this package as a dependency, you could include it in Composer with a
[custom package repository](https://getcomposer.org/doc/04-schema.md#repositories).


## Requirements

The following PHP versions are supported:

- PHP 5.5

The code hasn’t been tested with other PHP versions, but it should work with
all versions >= PHP 5.3.


## Documentation

### Goals

- Provide a streaming parser for PDF documents, entirely written in PHP, that
  follows the PDF specification.

Please note: I started this project in July 2013 because there was (to my
knowledge) no comparable FLOSS package available for PHP. The existing
“solutions” to extract data from PDF documents mostly relied on naive hacks
with regular expressions.

“Unfortunately”, just two months later (2013-Aug-30), the
[smalot/pdfparser](https://github.com/smalot/pdfparser) project was started on
GitHub. Partly because of that and partly because of issues in my personal
life, I lost the motivation to continue working on my own solution.

I am still uncertain if there will be substantial additions in the future, but
I also don’t want to discontinue the project because I put some work into it.
So there will at least be a certain level of maintenance.


## Testing

(Tools are not included in this package.)

~~~ bash
$ phpunit
~~~

Further quality assurance:

~~~ bash
$ phpmd ./src text codesize,design,naming
~~~


## Credits

- [Marc Ermshaus](https://github.com/mermshaus)


## License

This package ("mermshaus/Pdf") is free software: you can redistribute it and/or
modify it under the terms of the GNU Affero General Public License as published
by the Free Software Foundation, either version 3 of the License, or (at your
option) any later version. See COPYING for full license info.

Copyright 2013 Marc Ermshaus <http://www.ermshaus.org/>
