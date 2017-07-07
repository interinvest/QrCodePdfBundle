<?php
/**
 * Created by PhpStorm.
 * User: bmicha
 * Date: 07/07/2017
 * Time: 15:43
 */

namespace InterInvest\QrCodePdfBundle\Tests\App;

use InterInvest\QrCodePdfBundle\QrCodePdfBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new QrCodePdfBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
