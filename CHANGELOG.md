#Teknoo Software - Website - Change Log

##[2.0.0-beta1] - 2019-11-28
###Change
- Most methods have been updated to include type hints where applicable. Please check your extension points to make sure the function signatures are correct.
_ All files use strict typing. Please make sure to not rely on type coercion.
- PHP 7.4 is the minimum required
- Switch to typed properties
- Remove some PHP useless DockBlocks
- Replace array_merge by "..." operators

###Info
This version is not compatible with Doctrine ODM2.0 because Gedmo Extension does not support this version.

##[1.0.2] - 2019-10-24
###Release
- Maintenance release, QA and update dev vendors requirements

##[1.0.1] - 2019-06-09
###Release
- Maintenance release, upgrade composer dev requirement and libs

##[1.0.0] - 2019-02-10
###Release
- Remove support of PHP 7.1
- Remove support of Symfony 4.0 and 4.1 (keep 3.4, LTS)
- Switch to PHPUnit 8.0
- First major stable release

##[0.0.15] - 2019-01-08
###Update
- Need Teknoo East Foundation ^^0.0.11

##[0.0.14] - 2019-01-04
###Add
- Check technical debt and add support for php 7.3

##[0.0.13] - 2018-10-27
###Fix
- Fix syntax of template layout to follow normalize form "@BundleName/Controller/action.format.engine"

##[0.0.12] - 2018-09-02
###Fix
- Fix exception when order is empty

##[0.0.11] - 2018-09-02
###Add
- Pass query param to the view list

##[0.0.10] - 2018-09-02
###Add
- Add direction attribute support to sort results with AdminListEndPoint
- Add change/set default column to order and sort direction to sort results with AdminListEndPoint

##[0.0.9] - 2018-08-15
###Fix
- Fix Recipe bowl, they have an extra looping because the loop counter had a bug.
- Fix recipe compiling when several steps share the same name, firsts was lost.

##[0.0.8] - 2018-07-19
###Fix
- Item object use an array instead of ArrayObject to avoid error mapping
- RepositoryTrait Doctrine bridge suppports DocumentRepository and use a query for findBy() method in this case

##[0.0.7] - 2018-07-18
Stable release

##[0.0.7-beta2] - 2018-07-14
###Update
- Create DBSource interfaces to define object manager and object repository and allow loaders 
  and writers to be independent of Doctrine common. Theses interfaces are inspirated from Doctrine interfaces.
- Main Website namespace is Doctrine independent. Loaders and writers are agnostics.
- Create default implementation of DBSources interfaces with Doctrine ODM.
- Loader are simplified, queries are externalized into independent class.
- LoaderInterface load method accepts only ids, no other criteria are allowed.

###Added
- LoaderInterface query method to execute a QueryInterface instance about objects managed by the loader.

##[0.0.7-beta1] - 2018-06-15
###Update
- update to use recipe 1.1 and east foundation 0.0.8

##[0.0.6] - 2018-06-02
###Release
- Stable release

##[0.0.6-beta4] - 2018-04-18
###Fixed
- Fix getDeletedAt can be null

##[0.0.6-beta3] - 2018-02-26
###Fixed
- Fix error on writer when it's fail but not promise passed

##[0.0.6-beta2] - 2018-02-24
###Updated
- Use States 3.2.1 and last East Foundation 0.0.7-beta3
- Fix admin routing

##[0.0.6-beta2] - 2018-02-14
###Updated
- Use East Foundation 0.0.7-beta1

##[0.0.5] - 2018-01-25
###Updated
- Add tests files into package (remove from export-ignore

##[0.0.4] - 2018-01-23
###Updated
- Create a LoaderTrait to factorize code for non publishable object

##[0.0.3] - 2018-01-20
###Fix
fix doctrine mongodb configuration

##[0.0.2] - 2018-01-20
###Change
Update composer requirement (optional, only to use with Symfony) : require symfony/psr-http-message-bridge 1.0+ and zendframework/zend-diactoros 1.7+

##[0.0.1] - 2018-01-01
###First stable release
###Added
- add 404 response behavior when a content was not found
###Fixed
- update composer dev requirement and minimum stability

##[0.0.1-beta8] - 2017-12-27
###Fixed
- Remove Lexik bundle (useless)
- Set content type on media in Symfony Admin
- Fix deprecation with Symfony 3.4+
- Fix sluggable behavior

###Added
- Locale middleware dedicated to symfony translator updating,

##[0.0.1-beta7] - 2017-12-21
###Fixed
- Fix item loader to loading top
- Fix symfony routing failback for content in front
- QA

###Updated
- Update locale middleware to inject also locale in the view parameters list
- Add block type row

##[0.0.1-beta6] - 2017-11-29
###Fixed
- Update AdminEditEndPoint to recreate a form instance if the object has been updated to avoid error with dynamic form
 following a state of the object

###Updated
- Add pagination capacities in the Admin list endpoint
- Update collection loader to allow use iterator and countable results set to manage pagination
- Split mongo logic into a separated trait, added automatically in the DI

##[0.0.1-beta5] - 2017-11-27
###Fixed
- Fix category use Document Standard trait from states instead of entity
- Fix menu generator to use TopByLocation instead slug and replace TopBySlug method in Category loader by TopByLocation

###Updated
- Remove link in content to category rename Category to item
- Add reference to content into Items.

##[0.0.1-beta4] - 2017-11-24
###Fixed
- Not show solft deletd content into admin crud

##[0.0.1-beta3] - 2017-11-22
###Fixed
- Add publishing button and behavior of Publishable content in AdminEditEndPoint
- Migrate \DateTime type hitting to \DateTimeInterface
- Fix bug in MongoDB document postLoad

##[0.0.1-beta2] - 2017-11-22
###Changed
- Symfony optional support requires now 3.4-rc1 or 4.0-rc1

##[0.0.1-beta1] - 2017-11-21
###First beta release

###Added
- Interface to manages objects : ObjectInterface, to define the getter, PublishableInterface, DeletableInterface (to be soft deletable)
- Base objects :
    - Type : type of page, linked to a template and a list of block available in the template, to populate dynamically.
    - User : represent user able to manage the website's content.
    - Category : To create a set of page.
    - Media : To store image or other resources.
    - Content : Represent a page, owning a type and some categories, translatable.
- Loader and Writer to manage these objects.
- DeletingService to able soft delete some object.
- Trait to implement easily endpoints to display contents, media an static template in your framework.
- Symfony endpoints implementing previous traits.
- Middleware to manage locale to display the page.
- PHP-DI configuration to use universal package with any PSR11 applications.
- Symfony User class to wrap the user base class and branch it with Symfony' user provider / authentication.
- Symfony forms and admin end points to manage base objects.
