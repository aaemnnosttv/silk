<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;
use Sami\Version\GitVersionCollection;
use Sami\RemoteRepository\GitHubRemoteRepository;

$repository_root = dirname(__DIR__);

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in("$repository_root/src")
;

// generate documentation for all tags, and the master branch
$versions = GitVersionCollection::create($repository_root)
    ->addFromTags('*')
    ->add('master', 'master branch')
;

return new Sami($iterator, array(
    'versions'             => $versions,
    'title'                => 'Silk API',
    'build_dir'            => __DIR__ . '/../../silk-api-docs/%version%',
    'cache_dir'            => __DIR__ . '/../../silk-api-docs-cache/%version%',
    'remote_repository'    => new GitHubRemoteRepository('aaemnnosttv/silk', $repository_root),
    'default_opened_level' => 2,
));
