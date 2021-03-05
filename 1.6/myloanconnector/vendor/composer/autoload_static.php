<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2a1b74345a37af8345a9c017877fb105
{
    public static $files = array (
        'ce89ac35a6c330c55f4710717db9ff78' => __DIR__ . '/..' . '/kriswallsmith/assetic/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Process\\' => 26,
        ),
        'M' => 
        array (
            'MyLoan\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Process\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/process',
        ),
        'MyLoan\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $prefixesPsr0 = array (
        'C' => 
        array (
            'ComponentInstaller' => 
            array (
                0 => __DIR__ . '/..' . '/robloach/component-installer/src',
            ),
        ),
        'A' => 
        array (
            'Assetic' => 
            array (
                0 => __DIR__ . '/..' . '/kriswallsmith/assetic/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'HcApi\\HcApi' => __DIR__ . '/..' . '/homecreditcz/hc-api/HcApi.php',
        'Loan' => __DIR__ . '/../..' . '/classes/Loan.php',
        'MlcConfig' => __DIR__ . '/../..' . '/classes/MlcConfig.php',
        'MyLoan\\HomeCredit\\AuthAPI' => __DIR__ . '/../..' . '/classes/HomeCredit/AuthAPI.php',
        'MyLoan\\HomeCredit\\EndPointManager' => __DIR__ . '/../..' . '/classes/HomeCredit/EndPointManager.php',
        'MyLoan\\HomeCredit\\EndPoints\\Czech' => __DIR__ . '/../..' . '/classes/HomeCredit/EndPoints/Czech.php',
        'MyLoan\\HomeCredit\\EndPoints\\CzechTest' => __DIR__ . '/../..' . '/classes/HomeCredit/EndPoints/CzechTest.php',
        'MyLoan\\HomeCredit\\EndPoints\\IEndPoint' => __DIR__ . '/../..' . '/classes/HomeCredit/EndPoints/IEndPoint.php',
        'MyLoan\\HomeCredit\\EndPoints\\Slovak' => __DIR__ . '/../..' . '/classes/HomeCredit/EndPoints/Slovak.php',
        'MyLoan\\HomeCredit\\EndPoints\\SlovakTest' => __DIR__ . '/../..' . '/classes/HomeCredit/EndPoints/SlovakTest.php',
        'MyLoan\\HomeCredit\\OrderStateManager' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStateManager.php',
        'MyLoan\\HomeCredit\\OrderStates\\AbstractState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/AbstractState.php',
        'MyLoan\\HomeCredit\\OrderStates\\ProcessingState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/ProcessingState.php',
        'MyLoan\\HomeCredit\\OrderStates\\ReadyPaidState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/ReadyPaidState.php',
        'MyLoan\\HomeCredit\\OrderStates\\ReadyState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/ReadyState.php',
        'MyLoan\\HomeCredit\\OrderStates\\ReadyToDeliveredState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/ReadyToDeliveredState.php',
        'MyLoan\\HomeCredit\\OrderStates\\ReadyToDeliveringState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/ReadyToDeliveringState.php',
        'MyLoan\\HomeCredit\\OrderStates\\ReadyToShipState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/ReadyToShipState.php',
        'MyLoan\\HomeCredit\\OrderStates\\ReadyToShippedState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/ReadyToShippedState.php',
        'MyLoan\\HomeCredit\\OrderStates\\RejectedState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/RejectedState.php',
        'MyLoan\\HomeCredit\\OrderStates\\UnclassifiedState' => __DIR__ . '/../..' . '/classes/HomeCredit/OrderStates/UnclassifiedState.php',
        'MyLoan\\HomeCredit\\RequestAPI' => __DIR__ . '/../..' . '/classes/HomeCredit/RequestAPI.php',
        'MyLoan\\HomeCredit\\ResponseAPI' => __DIR__ . '/../..' . '/classes/HomeCredit/ResponseAPI.php',
        'MyLoan\\Tools' => __DIR__ . '/../..' . '/classes/Tools.php',
        'MyLoan\\Validate' => __DIR__ . '/../..' . '/classes/Validate.php',
        'PaymentMethods' => __DIR__ . '/../..' . '/classes/PaymentMethods.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2a1b74345a37af8345a9c017877fb105::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2a1b74345a37af8345a9c017877fb105::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit2a1b74345a37af8345a9c017877fb105::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2a1b74345a37af8345a9c017877fb105::$classMap;

        }, null, ClassLoader::class);
    }
}
