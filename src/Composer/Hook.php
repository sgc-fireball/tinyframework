<?php declare(strict_types=1);

namespace TinyFramework\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Hook
{

    public static function postInstallCommand(Event $event)
    {
        static::postUpdateCommand($event);
    }

    public static function postUpdateCommand(Event $event)
    {
        // require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
        static::checkPublicIndex();
    }

    private static function checkPublicIndex()
    {
        self::installFolder();
        self::installIndex();
    }

    private static function installFolder()
    {
        $folders = [
            'public',
            'routes',
            'resources/views',
            'storage/logs',
            'storage/psych',
            'storage/cache',
            'storage/sessions',
        ];
        foreach ($folders as $folder) {
            if (!is_dir('./' . $folder)) {
                mkdir('./' . $folder, 0755, true);
            }
        }
    }

    private static function installIndex()
    {
        if (!file_exists('public/index.php')) {
            copy(__DIR__ . '/../Files/index.php', 'public/index.php');
        }
    }

}
