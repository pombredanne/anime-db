<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\CatalogBundle\Composer;

use Composer\Script\PackageEvent;
use Composer\Script\CommandEvent;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Composer\Script\Event;
use Composer\Package\PackageInterface;
use Symfony\Component\Finder\Finder;

/**
 * Composer script handler
 *
 * @package AnimeDb\Bundle\CatalogBundle\Composer
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class ScriptHandler
{
    /**
     * Add plugin to AppKernel
     *
     * @param \Composer\Script\PackageEvent $event
     */
    public static function addPluginToAppKernel(PackageEvent $event)
    {
        // TODO write PluginBundle class into app/AppKernel
    }

    /**
     * Remove plugin from AppKernel
     *
     * @param \Composer\Script\PackageEvent $event
     */
    public static function removePluginFromAppKernel(PackageEvent $event)
    {
        // TODO remove PluginBundle class from app/AppKernel
    }

    /**
     * Migrate plugin
     *
     * @param \Composer\Script\PackageEvent $event
     */
    public static function migratePlugin(PackageEvent $event)
    {
        $command = 'doctrine:migrations:migrate --no-interaction';

        /* @var $package \Composer\Package\PackageInterface */
        switch ($event->getOperation()->getJobType()) {
            case 'uninstall':
                $command .= ' 0'; // migration to first version
            case 'install':
                $package = $event->getOperation()->getPackage();
                break;
            case 'update':
                $package = $event->getOperation()->getTargetPackage();
        }

        // migrate only plugin
        if ($package->getType() != 'anime-db-plugin') {
           return;
        }

        if ($config = self::getMigrationsConfig($package)) {
            self::executeCommand($event, $command.' --configuration='.$config);
        }
    }

    /**
     * Add plugin to routing
     *
     * @param \Composer\Script\PackageEvent $event
     */
    public static function addPluginToRouting(PackageEvent $event)
    {
        // TODO add @PluginBundle/Resources/config/routing.yml into app/config/routing.yml
    }

    /**
     * Remove plugin from routing
     *
     * @param \Composer\Script\PackageEvent $event
     */
    public static function removePluginFromRouting(PackageEvent $event)
    {
        // TODO remove @PluginBundle/Resources/config/routing.yml from app/config/routing.yml
    }

    /**
     * Save plugin into DB
     *
     * @param \Composer\Script\PackageEvent $event
     */
    public static function savePlugin()
    {
        // TODO save plugin into db
    }

    /**
     * Delete plugin from DB
     *
     * @param \Composer\Script\PackageEvent $event
     */
    public static function deletePlugin()
    {
        // TODO delete plugin from db
    }

    /**
     * Get path to migrations config file from package
     *
     * @param \Composer\Package\PackageInterface $package
     *
     * @return string|boolean
     */
    protected static function getMigrationsConfig(PackageInterface $package)
    {
        $options = self::getPluginOptions($package);
        // specific location
        if ($options['anime-db-migrations']) {
            return $options['anime-db-migrations'];
        }

        $finder = new Finder();
        $finder->files()
            ->in(__DIR__.'/../../../../../vendor/'.$package->getName())
            ->name('/^migrations\.(yml|xml)$/');

        /* @var $file \SplFileInfo */
        foreach ($finder as $file) {
            return $file->getRealPath();
        }
        return false;
    }

    /**
     * Execute command
     *
     * @throws \RuntimeException
     *
     * @param \Composer\Script\Event $event
     * @param string $cmd
     * @param integer $timeout
     */
    protected static function executeCommand(Event $event, $cmd, $timeout = 300)
    {
        $options = array_merge(['symfony-app-dir' => 'app'], $event->getComposer()->getPackage()->getExtra());

        $php = escapeshellarg(self::getPhp());
        $console = escapeshellarg($options['symfony-app-dir'].'/console');
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $process = new Process($php.' '.$console.' '.$cmd, null, null, null, $timeout);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
        }
    }

    /**
     * Get plugin options
     *
     * @param \Composer\Package\PackageInterface $package
     *
     * @return array
     */
    protected static function getPluginOptions(PackageInterface $package)
    {
        return array_merge(array(
            'anime-db-routing' => '',
            'anime-db-migrations' => '',
        ), $package->getExtra());
    }

    /**
     * Get path to php executable
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected static function getPhp()
    {
        $phpFinder = new PhpExecutableFinder;
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }
        return $phpPath;
    }

    /**
     * Global migrate
     *
     * @param \Composer\Script\CommandEvent $event
     */
    public static function migrate(CommandEvent $event)
    {
        self::executeCommand($event, 'doctrine:migrations:migrate --no-interaction');
    }
}