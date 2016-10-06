<?php

namespace Bundle\AppBundle\Twig\Extension;

use Symfony\Bridge\Twig\Extension\AssetExtension as sfAssetExtension;
use Symfony\Component\HttpKernel\KernelInterface;

class AssetTryExtension extends sfAssetExtension
{
    /**
     * @type KernelInterface
     */
    private $appKernel;

    public function setAppKernel(KernelInterface $kernel)
    {
        $this->appKernel = $kernel;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('assetTry', [$this, 'getAssetUrlFallBack']),
        ];
    }

    public function getAssetUrlFallBack($path, $packageName = NULL, ...$fallbackPaths)
    {
        $webRoot = $this->appKernel->getRootDir() . '/../web';
        array_unshift($fallbackPaths, $path);
        foreach ($fallbackPaths as $_path) {
            $assetPath = $this->getAssetUrl($_path, $packageName);
            $assetRealPath = realpath($webRoot . $assetPath);
            if (file_exists($assetRealPath)) {
                return $assetPath;
            }
        }

        return $this->getAssetUrl($path, $packageName);
    }

    public function getName()
    {
        return 'assetTry';
    }
}