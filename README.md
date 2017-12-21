Aplications for AppYoutube
==========================
This API was developed to manage a simple and simple YouTube application developed with Symfony3 using some third-party Bundle.

### Prerequisites

This bundle requires the following additional packages:

* PHP 7.1
* Doctrine 2.5
* Symfony 3.x.x
* KnpPaginatorBundle 2.6
* NelmioApiDocBundle 2.13
* PHP-JWT 5.0
* FOSJsRoutingBundle 2.0.0



## Installation the packages 
`composer install`

## Create database
`bin/console doctrine:database:create`

## Generate the entities
`bin/console doctrine:mapping:convert xml ./src/BackBundle/Resources/config/doctrine/metadata/orm --from-database --force`
`bin/console doctrine:mapping:import BackBundle annotation`
`bin/console doctrine:generate:entities BackBundle`

## Update database
`bin/console doctrine:schema:update --force`

## Run Server
`bin/console s:r`

## The Code Format in API
`php-cs-fixer fix`
