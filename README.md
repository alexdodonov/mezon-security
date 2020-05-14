# Security validations
[![Build Status](https://travis-ci.com/alexdodonov/mezon-security.svg?branch=master)](https://travis-ci.com/alexdodonov/mezon-security) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alexdodonov/mezon-security/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alexdodonov/mezon-security/?branch=master) [![codecov](https://codecov.io/gh/alexdodonov/mezon-security/branch/master/graph/badge.svg)](https://codecov.io/gh/alexdodonov/mezon-security)

## Intro
Mezon provides set of classes wich will help you to validate data from front-end

## Installation

Just print in console

```
composer require mezon/security
```

And that's all )

## Files validations

### Size validations

First of all you need to create validator for the file size:

```php
use \Mezon\Security;

// here we set that file must not be greater then 2KB
$sizeValidator = new Validators\File\Size(2 * Validators\File\Size::KB);
```

Then you can run validation

```PHP
$security = new SecurityRules();
$security->isUploadedFileValid('uploaded-file', $validators);
```

Here 'uploaded-file' is an index in the $_FILES array.

### Mime-types validations

You can validate mime types of the uploading files. To do this construct special validator and pass a list of valid mime types to it's constructor

```php
// here we set that file must not be greater then 2KB
$sizeValidator = new Validators\File\MymeType(['image/png', 'image/jpeg', 'image/jpg']);
```

And then call isUploadedFileValid like in the example above.

### Image validators

You can use validators for the image size:

```php
new Mezon\Security\Validators\File\ImageMaximumWidthHeight(<maximum width>, <maximum height>);

// and

new Mezon\Security\Validators\File\ImageMinimumWidthHeight(<minimum width>, <minimum height>);
```

# I'll be very glad if you'll press "STAR" button )