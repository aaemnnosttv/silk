# Release Notes

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
