# Teknoo Software - Common - Change Log

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
- Rename `Teknoo\East\Website` to `Teknoo\East\Common`
- Migrate all interfaces in `Teknoo\East\Common` to `Teknoo\East\Common\Contracts`

## [1.0.0-beta2] - 2022-04-08
### Stable Release
- Migrate all interfaces in `Teknoo\East\Common` to `Teknoo\East\Common\Contracts`

## [1.0.0-beta1] - 2022-04-08
### Stable Release
- Fork from `East Website 7.0.3`
- Remove all CMS features (`Content`, `Item`, `Media`, `Type`)
- Remove all Doctrine translation extension
- Rename `Teknoo\East\Website` to `Teknoo\East\Common`
- Migrate all interfaces in `Teknoo\East\Common` to `Teknoo\East\Common\Contracts`
