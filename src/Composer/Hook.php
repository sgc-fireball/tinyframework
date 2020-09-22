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
        self::installConsole();
        self::installIndex();
    }

    private static function installConsole()
    {
        if (!file_exists('console')) {
            copy(__DIR__ . '/../Files/console', 'console');
        }
        chmod('console', 0755);
    }

    private static function installIndex()
    {
        if (!is_dir('./public')) {
            mkdir('./public', 0755, true);
        }
        if (!file_exists('public/index.php')) {
            copy(__DIR__ . '/../Files/index.php', 'public/index.php');
        }
    }

}
