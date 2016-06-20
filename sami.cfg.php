<?php
use Sami\Sami;
use Sami\Parser\Filter\TrueFilter;
use Sami\Reflection\MethodReflection;
use Sami\Reflection\PropertyReflection;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

class HyperwalletSdkFilter extends TrueFilter {

    public function acceptMethod(MethodReflection $method) {
        return $method->isPublic() && !$method->getTags('internal');
    }

    public function acceptProperty(PropertyReflection $property) {
        return $property->isPublic() && !$property->getTags('internal');
    }

}

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('Tests')
    ->in($dir = __DIR__ . '/src')
;

$versions = GitVersionCollection::create($dir)
    ->addFromTags('v*')
    ->add('master', 'master branch')
;

$sami = new Sami($iterator, array(
    'theme'                => 'default',
    'versions'             => $versions,
    'title'                => 'Hyperwallet REST SDK',
    'build_dir'            => __DIR__ . '/docs/%version%',
    'cache_dir'            => __DIR__. ' /build/docs/%version%',
    'default_opened_level' => 1,
));
$sami['filter'] = function () {
    return new HyperwalletSdkFilter();
};

return $sami;
