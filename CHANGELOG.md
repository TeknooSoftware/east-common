#Teknoo Software - Website - Change Log

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
