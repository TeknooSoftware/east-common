# Teknoo Software - Common - Change Log

## [3.0.0] - 2024-11-01
### Stable Release
- Migrate to `Teknoo Recipe` 6.
- Rename `Cookbook` to `Plan`.
  - Old classes and interfaces are deprecated.
- Migrate to `EditablePlan` all previous `Cookbook` / `Plan`.
- Migrate the decoration about the East Foundation Plan to register the `LocaleMiddleware` 
  and `InitParametersBag`.
- The parameter `teknoo.east.common.plan.default_error_template` replace 
  `teknoo.east.common.cookbook.default_error_template`

## [2.13.0] - 2024-10-28
### Stable Release
- Fix wrong file type passed to JS Source Loader to minify javascripts
- Add `SourceLoader` extension module to allow app extension to complete set of assets

## [2.12.6] - 2024-10-14
### Stable Release
- Update requirement libraries
- Use `random_bytes` instead of `uniqid`

## [2.12.5] - 2024-10-07
### Stable Release
- Update dev lib requirements (issue with covering from PHP 11.4)

## [2.12.4] - 2024-08-30
### Stable Release
- API Response are trimed in `TemplateTrait`

## [2.12.3] - 2024-06-14
### Stable Release
- Remove Composer unused
- Switch to PHPUnit 11

## [2.12.2] - 2024-06-03
### Stable Release
- Remove useless dependency to `symfony/templating`

## [2.12.1] - 2024-05-31
### Stable Release
- Fix deprecated : replace `Symfony\Component\HttpKernel\DependencyInjection\Extension`
        by `Symfony\Component\DependencyInjection\Extension\Extension`

## [2.12.0] - 2024-05-17
### Stable Release
- Add methods `openBatch` and `closeBatch` to DBSource managers to create batch of writing operation, like transaction,
  but reading operations are not impacted, it's not a transaction. (This will come soon).

## [2.11.0] - 2024-05-07
### Stable Release
- Drop support of PHP 8.1
- Add sensitive parameter attribute on methods catching throwable to prevent leak.

## [2.10.1] - 2024-04-09
### Stable Release
- `RenderForm` step will set the response statut to 400 when the form is invalid and in api mode.

## [2.10.0] - 2024-04-02
### Stable Release
- `JumpIf` and `JumpIfNot` support a callable for `$expectedJumpValue`.
  - If a callable is passed, it must return a boolean and accepts the `$testValue` as parameter.

## [2.9.3] - 2024-03-22
### Stable Release
- Fix support of last PHPStan 1.10.64
- Use State 6.2

## [2.9.2] - 2024-03-13
### Stable Release
- Use Recipe 5+
- Improve `RecoveringAccessUserProvider` to use new `Promise` feature

## [2.9.1] - 2024-02-26
### Stable Release
- Fix typo `preferRealDate` instead of `prefereRealDate`

## [2.9.0] - 2024-02-25
### Stable Release
- Add steps `StartLoopingOn` and `EndLooping` to perform easily looping on a collection in a recipe
  - The collection must be passed at runtime in `StartLoopingOn`.
      (you can map your ingredient to the parameter `collection`)
    - At each call, the `StartLoopingOn` will put in the workplan the current value of the collection
      - If the value is an object, the workplan's key will be automatically defined from the object class
      - You can set the key value during the construction of `StartLoopingOn`. It is mandatory for non objects values
  - The step's name of the end of the loop must be defined at the construction in the `StartLoopingOn` instance.
  - The step's name of the start loop must be also be defined at the construction of the `EndLooping` instance.
  - The "loop" (aka the `StartLoopingOn` instance) is also injected in the workplan at each loop
- Add `VisitableTrait` to implement easily `VisitableInterface`
- Update `VisitableInterface` to accept direclty the property name and the callable as second argument without use 
  an array

## [2.8.0] - 2024-02-13
### Stable Release
- Remove `formOptions` as empty value in Symfony's routes (useless since 2.7.1)
- Add Symfony contract `FormApiAwareInterface` dedicated for form usable in a Web context and API context
  - A form with this contract MUST accept the option `api`, (must add in `defaults`)
  - If a the form implements this interface, the step `FormHandling` will also injected the ingredient `$api` in the
    form options  
- `UserType` is splitted into `UserType` and `ApiUserType`, the first extends the second and implements `FormApiAware`.
  - Password are manageable only with `UserType`

## [2.7.3] - 2024-02-12
### Stable Release
- Add User's export feature
- Add User' Type support API

## [2.7.2] - 2024-02-11
### Stable Release
- Fix `FormHandling` step with a GET request but it has a `Content-Type` to json. 
  (According to standard, a GET request has no Content-Type, it's just to prevent)

## [2.7.1] - 2024-02-10
### Stable Release
- `formOptions` ingredient in now not mandatory in cookbooks.
- Require `Recipe` 4.6.1+

## [2.7.0] - 2024-01-31
### Stable Release
- Common `DatesService` is deprecated, use Foundation's `DatesService` instead
- Common's components use Foundation's `DatesService`
- In `JumpIf`, if the $testValue is `Stringable`, the value will be automatically cast to string
- Add `JumpIfNot` to Jump to a step if a condition is not valid (contrary to `JumpIf`)
- Add `EmailValue` class as ValueObject compliant with `ObjectInterface`
- Add RecoveryAccess as AuthData for User
- Add Plan interface `PrepareRecoveryAccessEndPointInterface` and `PrepareRecoveryAccessEndPoint` to create
  and send a link to allow an user to recover its access.
  - Add Steps `FindUserByEmail`, `PrepareRecoveryAccess`, and `RemoveRecoveryAccess` for this last cookbook.
- Add `NotifyUserABoutRecoveryAccessInterface` to implement in a step locked in framework to send the notification
  - `RecoveryAccessNotification` is the default implementation of this interface in Symfony
- Add `UserWithRecoveryAccess` and `RecoveringAccessUserProvider` to support this new feature with Symfony Passwordless
  link (with LoginLink feature and Symfony Notifier)
- This provider supports also TOTP.
- Add `EmailFormType` in Symfony's Form to create a form to prompt an email.

## [2.6.6] - 2024-01-16
### Stable Release
- Support Doctrine Mongo ODM Bundle 5.x

## [2.6.5] - 2024-01-11
### Stable Release
- Fix issue in cascadin exprresion conversion 

## [2.6.4] - 2023-12-19
### Stable Release
- Fix 2FA authentication with third party

## [2.6.3] - 2023-12-04
### Stable Release
- Support Symfony 7+

## [2.6.2] - 2023-12-01
### Stable Release
- Update dev lib requirements
- Support Symfony 6.4+ (7+ comming soon)

## [2.6.1] - 2023-11-30
### Stable Release
- Update dev lib requirements
- Support Symfony 6.4+ (7+ comming soon)

## [2.6.0] - 2023-11-26
### Stable Release
- Add regex supper in queries' criteria.
  - Support $regex in MongoDB's queries

## [2.5.1] - 2023-11-24
### Stable Release
- Support of Doctrine ODM 2.6.1+

## [2.5.0] - 2023-11-16
### Stable Release
Add cleaning html feature available for all rendered html template
To enable this behavior, set in the route attributes, the attribute `cleanHtml` to true
Or set the parameter `teknoo.east.common.rendering.clean_html` to true in the DI.
The behavior is available only on environment with the ext tidy enabled (else the output is directly returned).

## [2.4.0] - 2023-11-09
### Stable Release
- JS and CSS Minifier features from specific HTTP endpoint without webpack
  - Use `league/flysystem` to perform I/O operations
  - Use `matthiasmullie/minify` to perform minifing operations
  - The cookbook implements the interface `Teknoo\East\Common\Contracts\Recipe\Plan\MinifierEndPointInterface`
  - Routes available in `infrastructures/symfony/Resources/config/assets_routing.yaml`
  - Compiled file can be versioned by using `_teknoo_common_minifier_css_version`
    and `_teknoo_common_minifier_js_version`.
  - By default, a compiled file is not overwritten but this behavior can be disabled by set the parameter
    `teknoo.east.common.assets.no_overwrite` to false
  - With Symfony, command `teknoo:common:minify:css` and `teknoo:common:minify:js` can be used.
    - The cookbook implements the interface `Teknoo\East\Common\Contracts\Recipe\Plan\MinifierCommandInterface`

## [2.4.0-beta2] - 2023-11-09
### Stable Release
- JS and CSS Minifier features from specific HTTP endpoint without webpack
  - Use `league/flysystem` to perform I/O operations
  - Use `matthiasmullie/minify` to perform minifing operations
  - The cookbook implements the interface `Teknoo\East\Common\Contracts\Recipe\Plan\MinifierEndPointInterface`
  - Routes available in `infrastructures/symfony/Resources/config/assets_routing.yaml`
  - Compiled file can be versioned by using `_teknoo_common_minifier_css_version` 
    and `_teknoo_common_minifier_js_version`.
  - By default, a compiled file is not overwritten but this behavior can be disabled by set the parameter 
    `teknoo.east.common.assets.no_overwrite` to false
  - With Symfony, command `teknoo:common:minify:css` and `teknoo:common:minify:js` can be used.
    - The cookbook implements the interface `Teknoo\East\Common\Contracts\Recipe\Plan\MinifierCommandInterface`
  
## [2.4.0-beta1] - 2023-11-05
### Stable Release
- JS and CSS Minifier features from specific HTTP endpoint without webpack
  - Use `league/flysystem` to perform I/O operations
  - Use `matthiasmullie/minify` to perform minifing operations
- To do :
  - Support version of minified assets compiled
  - Add CLI command to compile offline
  - Support of noOverwrite

## [2.3.2] - 2023-10-06
### Stable Release
- Fix issues with `PHPUnit 10.4+`

## [2.3.1] - 2023-10-05
### Stable Release
- Fix `ThirdPartyAuthenticatedUserProvider` to be used with TOTP

## [2.3.0] - 2023-08-29
### Stable Release
- Add `Stop` special step to stop the execution of a recipe.

## [2.2.0] - 2023-08-15
### Stable Release
- `Render`, `RenderError`, `RenderList`, `RenderForm`, and `TemplateTrait` support api json and return 
 the content type `application/json` when api is at json

## [2.1.0] - 2023-08-15
### Stable Release
- Update DeleteObjectEndPoint to support operations from API.
  - Add `api` option in route `_defaults` or request attribute to disable redirection and render a template
    defined in the option `template`.
- Add `JumIf` step to implement a conditional jump according to a presence or an ingredient/variable in the workplan, 
  or according to the value of this ingredient.
- Support API call in Symfony Form's recipe step, to disable CSRF protection
  - This query can be pushed in GET, POST or PUT, with `application/x-www-form-urlencoded`, `multipart/form-data` 
    or `application/json`

## [2.0.1] - 2023-08-06
### Stable Release
- Reorder options in Symfony Routes

## [2.0.0] - 2023-07-13
### Stable Release
- Support PHP-DI 7.0+
- Support Laminas Diactoros 3.0+

## [2.0.0-beta1] - 2023-07-12
### Beta Release
- Support PHP-DI 7.0+
- Support Laminas Diactoros 3.0+

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
  - Plan `Enable2FA`
  - Plan `Disable2FA`
  - Plan `GenerateQRCode`
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
- Add parameter `$preferRealDateOnUpdate` to method `WriterInterface::save()` to pass the real date instead of the 
  date in cache when a `TimestampableInterface` instance is passed.
- Add property `PersistTrait::$preferRealDateOnUpdate` to set the default behavior for a writer about date to pass
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
