#Nette Framework Generator

Nette CRUD generator allows you create Nette Framework 2.2.X based application for managing your databases or even more.

##Data sources
- MySQL InnoDB tables (with foreign keys support)
- MySQL MyISAM tables (without foreign keys support)
- Doctrine2 entities (with automatic creating MySQL InnoDB tables)

##Generated stuff
- Depends on used templates, but everything can be generated

##Main features
- Choose data source (MySQL tables, Doctrine2 entities)
- Choose tables (all possible combinations)
- Choose models ([Nette\Database](https://github.com/nette/database), [Doctrine2](https://github.com/doctrine/doctrine2))
- Choose foreign keys handling (open full table, selectbox with search)
- Choose if you want to use module or not
- Choose template which will be used for code generating
- That's it let generator build your application
  
##Usage
- Create new Nette Framework 2.2 project using Composer: `composer create-project nette/sandbox my-project`
- Move to project: `cd my-project`
- Add latest Nette Framework Generator using Composer: `composer require r-bruha/nette-generator @dev`
- Add latest [Kdyby\Replicator](https://github.com/Kdyby/Replicator) using Composer: `composer require kdyby/forms-replicator 1.2.*@dev`
- Move to Nette Framework Generator folder: `cd vendor/r-bruha/nette-generator`
- Start Nette generator through CLI `php -f index.php` and follow instructions

##Creating templates
 - See **How to create  templates.md** file