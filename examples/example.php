<?php

use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Symbol;

$fixer = DriverFactory::make('fixerio');
$fixer->accessKey('your-access-key-goes-here');
$result = $fixer->from(Symbol::EUR)->get(Symbol::USD);

print_r($result->all());
