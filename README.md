# Nette Framework CRUD Generator

Nette Framework CRUD Generator allows you create Nette Framework 2.4 based application for managing your databases or even more.

## Live Demos
- [PHP 7.0 | MySQL 5.6 | MyISAM Engine | Nette\Database Models](http://nette-generator.jecool.net/demo-1/www)
- [PHP 7.0 | MySQL 5.6 | InnoDB Engine | Nette\Database Models](http://nette-generator.jecool.net/demo-2/www)
- [PHP 7.0 | MySQL 5.6 | InnoDB Engine | Doctrine2 Models](http://nette-generator.jecool.net/demo-3/www)

## Data sources
- MySQL InnoDB tables (with foreign keys support)
- MySQL MyISAM tables (without foreign keys support)
- Doctrine2 entities (with automatic creating MySQL InnoDB tables)

## Generated stuff
- Depends on used templates, but everything can be generated

## Main features
- Choose data source (MySQL tables, Doctrine2 entities)
- Choose tables (all possible combinations)
- Choose models ([Nette\Database](https://github.com/nette/database), [Doctrine2](https://github.com/doctrine/doctrine2))
- Choose foreign keys handling (open full table, selectbox with search)
- Choose if you want to use module or not
- Choose template which will be used for code generating
- That's it let generator build your application
  
## Usage
### Longer step by step usage
- Create new Nette Framework 2.4 project using Composer: `composer create-project nette/web-project`
- Move to project: `cd web-project`
- Add latest Nette Framework CRUD Generator using Composer: `composer require r-bruha/nette-generator @dev`
- Add latest [Kdyby\Replicator](https://github.com/kdyby/FormsReplicator) using Composer: `composer require kdyby/forms-replicator @dev`
- Move to Nette Framework CRUD Generator folder: `cd vendor/r-bruha/nette-generator`
- Start Nette Framework CRUD Generator through CLI `php -f index.php` and follow instructions
### Shorter copy and paste usage
- Copy this whole code into command line, press Enter and wait for start Nette Framework CRUD Generator
```
composer clearcache
composer create-project nette/web-project
cd web-project
composer require r-bruha/nette-generator @dev
composer require kdyby/forms-replicator @dev
cd vendor/r-bruha/nette-generator
php -f index.php
```

## Creating templates
See [How to create  templates.md](https://github.com/r-bruha/nette-generator/blob/master/How%20to%20create%20templates.md)
