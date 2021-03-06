<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3904d221c4282054ed0ff30734bca18d
{
    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'Noodlehaus\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Noodlehaus\\' => 
        array (
            0 => __DIR__ . '/..' . '/hassankhan/config/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3904d221c4282054ed0ff30734bca18d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3904d221c4282054ed0ff30734bca18d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
