CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Plugin system
* Maintainers


INTRODUCTION
------------

Currency exchange rates module for Drupal Commerce 2.

Features
1. Manual defined or/and remote exchange rates.
2. Built for Commerce 2
3. Separate config yml files for exchanger definition and imported rates
4. Provide default price conversion service
5. Plugin based system.
6. Integrated with Commerce Currency Resolver module

Out of box are supported following exchange rates plugins:
1. Manual plugin
2. Fixer.io (free and paid)
3. European Central Bank


REQUIREMENTS
------------

This module requires Drupal Commerce 2 and it's submodule price.


INSTALLATION
------------

Install the Commerce Exchanger module as you would normally install
any Drupal contrib module.
Visit https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
--------------

    1. Navigate to Administration > Extend and enable the Commerce Exchanger 
       module.
    2. Navigate to Home > Administration > Commerce > Configuration
                   > Exchange rates.


PLUGIN SYSTEM
--------------
See examples of implementation for external providers.
`\Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\FixerExchanger`
`\Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\EuropeanCentralBankExchanger`


See `\Drupal\commerce_exchanger\Annotation\CommerceExchangerProvider`
for available properties on CommerceExchangerProvider annotation plugin type.

In most cases it would be enough to make proper annotation type 
and implement two functions inside your plugin:
`\Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\ExchangerProviderRemoteInterface::apiUrl`
`\Drupal\commerce_exchanger\Plugin\Commerce\ExchangerProvider\ExchangerProviderRemoteInterface::getRemoteData`

Note that function `getRemoteData()` should return array keyed as examples 
below. If you follow that format, you don't need to implement anything more 
in your plugin.

```
['HRK' => '1.3', 'USD' => '1.666']
```

or 

```
['base' => 'USD', 'rates' => ['HRK' => '1.3', 'EUR' => '1.666']]
```

Both formats are supported. First is mostly used when you know your base 
currency or it is defined by end provider 
(see plugin annotation for European Central Bank).

Second format is redundant, but it is used as fallback on Fixer.io free account.
There your base currency is based upon your profile on Fixer.io and can be 
different from what you are using in Drupal.

MAINTAINERS
-----------

The 8.x-1.x branch was created by:

 * Valentino Medimorec (valic) - https://www.drupal.org/u/valic
