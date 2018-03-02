# Release Notes

## v0.12.0 (2018-03-02)

### Added
- [Query scopes](https://github.com/aaemnnosttv/silk/issues/24) for model queries
- [Soft `Model::find(id)` retrieval method](https://github.com/aaemnnosttv/silk/issues/25)
- `\Silk\Silk::VERSION` Version constant

## Changed
- Raised minimum PHP version to 5.6
- [Updated `tightenco/collect` Collection library](https://github.com/aaemnnosttv/silk/issues/27)  
**_Breaking change from previous version with some methods_** (see issue)
- [Removed deprecated methods](https://github.com/aaemnnosttv/silk/issues/28) **Breaking Change**
  - `Silk\Post\Model::fromWpPost`
  - `Silk\Term\Model::fromWpTerm`
- Removed `Silk\Contracts\Query\BuildsQueries` interface

## v0.11.1 (2016-30-08)

### Changed
- Restrict `tightenco/collect` to 5.2

## v0.11.0 (2016-18-08)

### Added
- `User\Model`
- `User\QueryBuilder`
- `url` methods on `Post\Model` & `Term\Model`
- `Term\Model->children()`
- `Model::make()` named constructor
- `objectAliases` property, allowing for `$model->aliasName == $model->object->targetProperty`
- Shorthand property aliases, Eg: `$postModel->{name} == $postModel->object->post_{name}` (opt-in via trait)

### Changed
- `Hook` callbacks now automatically return the first argument passed if nothing is returned.
- `ObjectMeta` now has a fluent `set(key, value)` method.
- Deprecated `Post\Model::fromWpPost()` and `Term\Model::fromWpTerm()` (use `::make()` instead)
- Simplified internals of `Model->save()`, `->delete()` and `->refresh()`

## v0.10.1 (2016-07-22)

### Fixed
- Strict notice on PHP 5 for abstract static method

## v0.10.0 (2016-07-16)

### Added
- `Term\Model`
- `Taxonomy\Taxonomy`
- `Term\QueryBuilder`
- Conditional Hooks with `onlyIf(callback)` method
- `Meta->replace(old, new)` method
- Models for all WordPress Post Types and Taxonomies

### Changed
- `ObjectMeta->collect()` now returns a Collection of Meta objects
- `ObjectMeta->all()` now returns an array
- Meta add, set, delete are now fluent methods
- `Post\PostType` is now `PostType\PostType`
- `Post\PostTypeBuilder` is now `PostType\Builder`

## v0.9.0 (2016-06-24)

**Initial release! 🎉**

### Added
- Callback
- Hook + helper functions
- Meta
- ObjectMeta
- Post\Model
- PostType
- PostTypeBuilder
- Query\Builder
- Shortcode
