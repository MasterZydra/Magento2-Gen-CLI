# Magento2 Generator CLI

**Content**  
- [Commands](#commands)
- [Usage](#usage)
- [Custom templates](#custom-templates)
- [Installation](#installation)
- [Updating to latest version](#updating-to-latest-version)

Creating a new module for Magento2, adding a controller, etc. is not intuitive.  
You have to look into the official documentation of Magento2 or use a third party tool like [Mage2Gen.com](Mage2Gen.com).  
This extension adds Laravel-like CLI commands to simplify these steps.

## Commands
| Command | Description |
|---------|-------------|
| `make:block` | Create a new block |
| `make:command` | Create a new command |
| `make:controller` | Create a new controller |
| `make:helper` | Create a new helper |
| `make:module` | Create a new module |

## Usage
```
> php bin/magento make:module <vendor name> <module name>

> php bin/magento make:module
Vendor name (e.g. 'Magento'): Magento
Module name (e.g. 'Sales'): Sales
Generating files...
Module 'Magento/Sales' was created.
```

## Custom templates
Don't like the files the commands create?  
With custom templates you can create a folder structure like the `Template` directory of this module.  
You can just place the custom templates for the files you want to change.  
Otherwise the default templates from this module will be used.

The module will take the path from the environment variable `GEN_CLI_TEMPLATE`.

```bash
$ export GEN_CLI_TEMPLATE="/var/www/html/app/code/My/Templates"
```

## Installation
This Magento2 module can be installed using composer:  
`> composer require masterzydra/magento2-gen-cli`

To remove it from the list of required packages use the following command:  
`> composer remove masterzydra/magento2-gen-cli`

## Updating to latest version
With the following command composer checks all packages in the composer.json for the latest version:  
`> composer update`

If you only want to check this package for newer versions, you can use  
`> composer update masterzydra/magento2-gen-cli`
