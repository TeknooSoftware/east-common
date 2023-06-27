# Teknoo Software - Common - Change Log

## [1.9.6] - 2023-06-27
### Stable Release
- Fix PSR4 issue with MediaTest file. (Tests can be reused in others libaries)

## [1.9.5] - 2023-06-07
### Stable Release
- Update Teknoo libs
- Require Symfony 6.3 or newer

## [1.9.4] - 2023-05-15
### Stable Release
- Update dev lib requirements
- Update copyrights

## [1.9.3] - 2023-05-05
### Stable Release
- savedObject var is also set for created object and not only for updated object

## [1.9.2] - 2023-04-16
### Stable Release
- Update dev lib requirements
- Support PHPUnit 10.1+
- Migrate phpunit.xml

## [1.9.1] - 2023-04-11
### Stable Release
- Allow psr/http-message 2

## [1.9.0] - 2023-03-20
### Stable Release
- Migrate Website Media into Common namespace
- Migrate Website LocalMiddleware into Common namespace

## [1.8.2] - 2023-03-12
### Stable Release
- Q/A

## [1.8.1] - 2023-03-08
### Stable Release
- When an object is saved entry `objectSaved` is set a true into workplan and view

## [1.8.0] - 2023-03-03
### Stable Release
- Add `LazyLoadableCollection` to create lazy collection from Common Queries.
- Add `EmptyObject`.

## [1.7.1] - 2023-03-03
### Stable Release
- Fix deprecated in Doctrine ODM

## [1.7.0] - 2023-02-28
### Stable Release
- Support 2FA with TOTP (like Google Authenticator) credentials in common namespace
  - Add `TOTPAuth` class as `AuthDataInterface`
  - Add `getOneAuthData` to return a specific instance of `AuthData`
  - Fix bug to allow one instance of `AuthData` type per user
- Support official 2FA Bundle provided by Sheb for Symfony
- Update Users providers in Symfony Bundle to return user with `UserWithTOTPAuthInterface`
  and sheb's interfaces, and use its bundle out of the box without any adaptation for this bundle.
- Add, in Symfony bundle, endpoints and step to enable or disable TOTP and QRCode generation : 
  - Cookbook `Enable2FA`
  - Cookbook `Disable2FA`
  - Cookbook `GenerateQRCode`
  - Step `EnableTOTP`
  - Step `DisableTOTP`
  - Step`GenerateQRCodeTextFORTOTP`
  - Step interface `BuildQrCodeInterface` to implement response with image from QRCode.
  - Step `ValidateTOTP`
- Add routing file `2fa_routing.yaml`.

## [1.6.2] - 2023-02-11
### Stable Release
- Remove phpcpd and upgrade phpunit.xml

## [1.6.1] - 2023-02-03
### Stable Release
- Update dev libs to support PHPUnit 10 and remove unused phploc

## [1.6.0] - 2023-01-22
### Stable Release
- Support East Foundation 6.2
- `DatesService` reuse Foundation's Dates Service 

## [1.5.0] - 2022-12-16
### Stable Release
- Some QA fixes
- Drop support of Symfony 6.0 and Doctrine 2.x, supports SF 6.1+ and Doctrine 3+

## [1.4.7] - 2022-12-13
### Stable Release
- Add `OriginalRecipeInterface::class . ':CRUD'` in DI to allow custom Recipe for CRUD
- Add `OriginalRecipeInterface::class . ':Static'` in DI to allow custom Recipe for Static

## [1.4.6] - 2022-11-25
### Stable Release
- `FormHandlingInterface` step is recalled in `EditObjectEndPoint` before re-rendering form when the form has been
  successfully saved.

## [1.4.5] - 2022-11-25
### Stable Release
- Update symfony configuration for behat
- Migrate Behat's bootstrap into tests directory
- Add strict_types=1 to all tests

## [1.4.4] - 2022-10-29
### Stable Release
- Fix PSR4 error in Test namespace

## [1.4.3] - 2022-10-14
### Stable Release
- Support Recipe 4.2+

## [1.4.2] - 2022-09-18
### Stable Release
- Add `VisitableInterface` to expose indirectly internal values / attributes to an method.

## [1.4.1] - 2022-08-27
### Stable Release
- Use `getSingleResult` instead of `execute` for single result 

## [1.4.0] - 2022-08-26
### Stable Release
- Add `$hydrate` arguments to auto load all persisted objects linked to fetched main object, from the name of the 
  relation in the main object. (Not available with the common doctrine implementation)
- Implement the previous behavior on Doctrine ODM thanks to [Doctrine priming reference](https://www.doctrine-project.org/projects/doctrine-mongodb-odm/en/2.3/reference/priming-references.html) feature.

## [1.3.0] - 2022-08-14
### Stable Release
- Add parameter `$prefereRealDateOnUpdate` to method `WriterInterface::save()` to pass the real date instead of the 
  date in cache when a `TimestampableInterface` instance is passed.
- Add property `PersistTrait::$prefereRealDateOnUpdate` to set the default behavior for a writer about date to pass
  to a `TimestampableInterface` instance.

## [1.2.6] - 2022-08-06
### Stable Release
- Fix composer 

## [1.2.5] - 2022-08-01
### Stable Release
- Fix DatesService, update internal date instance when the real date is claimed in `passMeTheDate`

## [1.2.4] - 2022-06-18
### Stable Release
- Improve exception message and code in steps
- Set exception's code when they are throwed

## [1.2.3] - 2022-06-17
### Stable Release
- Clean code and test thanks to Rector
- Update libs requirements

## [1.2.2] - 2022-05-28
### Stable Release
- Promise simplification in PaginationQuery

## [1.2.1] - 2022-05-16
### Stable Release
- Fix admin list route

## [1.2.0] - 2022-05-15
### Stable Release
- Add `NotIn` query expression
- Add `BatchManipulationManagerInterface` contract, extending `ManagerInterface` to define manager able to perform 
  batch data manipulations.
- Add `BatchManipulationManager` (in `Doctrine\ODM`) implementing `BatchManipulationManagerInterface` with fallback to
 `Manager` (in `Doctrine\Common`) for individual persisting operations.
- Add `UpdatingQueryInterface` contract to perform batch object updates.
- Add `DeletingQueryInterface` contract to perform batch object deletes.
- Add `QueryExecutorInterface` contract to translate previous queries (update and delete) to the Data layer query in 
  adapter.
- Add `QueryExecutor` in (`Doctrine\ODM`) to transform east query to Doctrine ODM query.

## [1.1.0] - 2022-04-20
### Stable Release
- All cookbooks provided by Common accepts a new argument in constructorm called `defaultErrorTemplate` to set in the
  initial workplan the `errorTemplate` ingredient to avoid to set for each use.
- This variable can be set in the DI via the special key `teknoo.east.common.cookbook.default_error_template`
- `ListObjectEndPoint` accepts `$loadListObjectWiths` in constructor to define mapping for `LoadListObjects` step.
- `EditObjectEndPoint` accepts `$loadObjectWiths` in constructor to define mapping for `LoadObject` step.
- `DeleteObjectEndPoint` accepts `$loadObjectWiths` in constructor to define mapping for `LoadObject` step.
- `CreateObjectEndPoint` accepts `$createObjectWiths` in constructor to define mapping for `CreateObject` step.

## [1.0.4] - 2022-04-19
### Stable Release
- `SlugPreparation` is not mandatory for cookbooks `CreateObjectEndPoint` and `EditObjectEndPoint`

## [1.0.3] - 2022-04-17
### Stable Release
- Rename `.yml` files to `.yaml`

## [1.0.2] - 2022-04-11
### Stable Release
- Fix `Common\RepositoryTrait::$repository` definition
- Upgrade dev libs requirements

## [1.0.1] - 2022-04-10
### Stable Release
- Fix `UserType` form to be use as subform *(missed `data_class`) option. 

## [1.0.0] - 2022-04-08
### Stable Release
- Fork from `East Website 7.0.3`
- Remove all CMS features (`Content`, `Item`, `Media`, `Type`)
- Remove all Doctrine translation extension
- Rename `Teknoo\East\Common` to `Teknoo\East\Common`
- Migrate all interfaces in `Teknoo\East\Common` to `Teknoo\East\Common\Contracts`

## [1.0.0-beta2] - 2022-04-08
### Stable Release
- Migrate all interfaces in `Teknoo\East\Common` to `Teknoo\East\Common\Contracts`

## [1.0.0-beta1] - 2022-04-08
### Stable Release
- Fork from `East Website 7.0.3`
- Remove all CMS features (`Content`, `Item`, `Media`, `Type`)
- Remove all Doctrine translation extension
- Rename `Teknoo\East\Common` to `Teknoo\East\Common`
- Migrate all interfaces in `Teknoo\East\Common` to `Teknoo\East\Common\Contracts`
