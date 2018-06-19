<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdd55988d9fbd9fd61edcae1dfc3b3381
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'SpotifyWebAPI\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'SpotifyWebAPI\\' => 
        array (
            0 => __DIR__ . '/..' . '/jwilsson/spotify-web-api-php/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdd55988d9fbd9fd61edcae1dfc3b3381::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdd55988d9fbd9fd61edcae1dfc3b3381::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
