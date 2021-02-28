<?php declare(strict_types=1);

namespace TinyFramework\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Hook
{

    public static function postInstallCommand(Event $event): void
    {
        static::postUpdateCommand($event);
    }

    public static function postUpdateCommand(Event $event): void
    {
        // require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
        static::checkPublicIndex();
    }

    private static function checkPublicIndex(): void
    {
        self::installFolder();
        self::installFiles();
    }

    private static function installFolder(): void
    {
        $folders = [
            'app/Commands',
            'app/Providers',
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

    private static function installFiles(): void
    {
        if (!file_exists('./console')) {
            copy(__DIR__ . '/../Files/console.php', './console');
        }
        if (!is_executable('./console')) {
            chmod('./console', 0700);
        }
        if (!file_exists('public/index.php')) {
            copy(__DIR__ . '/../Files/index.php', 'public/index.php');
        }
    }

}
