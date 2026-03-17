<?php

namespace App\Helpers;

class AssetHelper
{
    /**
     * Generate asset URL with cache-busting version
     * Replaces the need for filemtime() in blade files
     */
    public static function versionedAsset($path)
    {
        $publicPath = public_path($path);
        
        // If file exists, use its modification time as cache buster
        if (file_exists($publicPath)) {
            $version = filemtime($publicPath);
            return asset($path) . '?v=' . $version;
        }
        
        // Fallback to just the asset path
        return asset($path);
    }
}
