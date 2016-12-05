# Translation extractor

[![Latest Version](https://img.shields.io/github/release/php-translation/extractor.svg?style=flat-square)](https://github.com/php-translation/extractor/releases)
[![Build Status](https://img.shields.io/travis/php-translation/extractor.svg?style=flat-square)](https://travis-ci.org/php-translation/extractor)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/extractor.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/extractor)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/extractor.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/extractor)
[![Total Downloads](https://img.shields.io/packagist/dt/php-translation/extractor.svg?style=flat-square)](https://packagist.org/packages/php-translation/extractor)

**Extract translation messages from source code**


## Install

Via Composer

``` bash
$ composer require php-translation/extractor
```

## Usage

```php
$extractor = new Extractor();

// Create extractor for PHP files
$fileExtractor = new PHPFileExtractor()

// Add visitors
$fileExtractor->addVisitor(new ContainerAwareTrans());
$fileExtractor->addVisitor(new ContainerAwareTransChoice());
$fileExtractor->addVisitor(new FlashMessage());
$fileExtractor->addVisitor(new FormTypeChoices());

// Add the file extractor to Extactor
$extractor->addFileExtractor($this->getPHPFileExtractor());

// Define where the source code is
$finder = new Finder();
$finder->in('/foo/bar');

//Start extractring files
$sourceCollection = $extractor->extract($finder);
```