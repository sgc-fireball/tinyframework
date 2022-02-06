<?php

declare(strict_types=1);

namespace TinyFramework\Composer;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;

class Hook
{
    public static function postInstallCommand(Event $event): void
    {
        self::postUpdateCommand($event);
    }

    public static function postUpdateCommand(Event $event): void
    {
        // require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
        self::checkPublicIndex();
    }

    private static function checkPublicIndex(): void
    {
        if (!is_dir('docs') && !is_dir('src')) {
            self::installFolder();
            self::installFiles();
        }
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
            'storage/xhprof',
        ];
        foreach ($folders as $folder) {
            if (!is_dir('./' . $folder)) {
                mkdir('./' . $folder, 0750, true);
            }
        }
    }

    private static function installFiles(): void
    {
        $consoleTarget = './console';
        copy(__DIR__ . '/../Files/console.php', $consoleTarget);
        if (!is_executable($consoleTarget)) {
            chmod($consoleTarget, 0700);
        }
        copy(__DIR__ . '/../Files/index.php', 'public/index.php');
    }
}
