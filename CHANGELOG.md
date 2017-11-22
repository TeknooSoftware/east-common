#Teknoo Software - Website - Change Log

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
