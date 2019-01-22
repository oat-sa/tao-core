<?php

namespace oat\tao\Model\Command;

use common_cache_FileCache;
use common_exception_Error;
use common_ext_AlreadyInstalledException;
use common_ext_Extension;
use common_ext_ExtensionsManager;
use common_ext_ForbiddenActionException;
use common_ext_ManifestNotFoundException;
use common_ext_MissingExtensionException;
use common_ext_OutdatedVersionException;
use helpers_ExtensionHelper;
use oat\generis\Model\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tao_install_ExtensionInstaller;

/**
 * Command to update the tao instance
 */
class TaoUpdate extends ConsoleCommand
{

    /**
     * @var common_ext_ExtensionsManager
     */
    private $extensionManager;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tao:update')
             ->setDescription('Updates the TAO instance');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->title('Starting TAO Update');

        $merged = array_merge(
            $this->getExtensionManager()->getInstalledExtensions(),
            $this->getMissingExtensions()
        );

        $sorted = helpers_ExtensionHelper::sortByDependencies($merged);

        foreach ($sorted as $extension) {
            try {
                $this->section('Extension: ' . $extension->getName());
                if (!$this->getExtensionManager()->isInstalled($extension->getId())) {
                    $this->note('Extension is not installed yet. Installing it now.');
                    $installer = new tao_install_ExtensionInstaller($extension);
                    $installer->install();
                    $this->writeln('Extension has been installed.');
                } else {
                   $this->updateExtension($extension);
                }
            } catch (common_ext_MissingExtensionException $e) {
                $this->error($e->getMessage());
                break;
            } catch (common_ext_OutdatedVersionException $e) {
                $this->error($e->getMessage());
                break;
            } catch (common_ext_AlreadyInstalledException $e) {
                $this->error($e->getMessage());
                break;
            } catch (common_ext_ForbiddenActionException $e) {
                $this->error($e->getMessage());
                break;
            } catch (Exception $e) {
                $this->error('Update failed');
                $this->error($e->getMessage());
                break;
            }
        }

        $this->success('Update processed finished');
    }

    /**
     * Update a specific extension
     *
     * @param common_ext_Extension $extension
     * @throws common_exception_Error
     * @throws common_ext_ManifestNotFoundException
     * @throws common_ext_MissingExtensionException
     * @throws common_ext_OutdatedVersionException
     */
    private function updateExtension(common_ext_Extension $extension)
    {
        helpers_ExtensionHelper::checkRequiredExtensions($extension);

        $installed = $this->getExtensionManager()->getInstalledVersion($extension->getId());
        $codeVersion = $extension->getVersion();

        if ($installed === $codeVersion) {
            $this->writeln('Extension is up to date');
            return;
        }

        $this->note('Update from required (' . $installed . ' => ' . $codeVersion . ')');

        $updater = $this->getUpdater($extension);

        if ($updater !== null) {
            $returnedVersion = $updater->update($installed);
            $currentVersion = $this->getExtensionManager()->getInstalledVersion($extension->getId());

            if ($returnedVersion !== null && $returnedVersion !== $currentVersion) {
                $this->getExtensionManager()->updateVersion($extension, $returnedVersion);
                $this->writeln('Manually saved extension version');
                $currentVersion = $returnedVersion;
            }

            if ($currentVersion === $codeVersion) {
                $this->writeln('Extension updated successfully');
            } else {
                $this->error('Extension update failed, exited with version ' . $currentVersion);
            }

            common_cache_FileCache::singleton()->purge();
        }
    }

    /**
     * Get the missing extensions.
     *
     * @return array
     * @throws \common_ext_ExtensionException
     */
    private function getMissingExtensions()
    {
        $missingId = helpers_ExtensionHelper::getMissingExtensionIds($this->getExtensionManager()->getInstalledExtensions());

        $missingExt = [];
        foreach ($missingId as $extensionId) {
            $extension = $this->getExtensionManager()->getExtensionById($extensionId);
            $missingExt[$extensionId] = $extension;
        }
        return $missingExt;
    }

    /**
     * Get the Extension manager
     *
     * @return common_ext_ExtensionsManager
     */
    private function getExtensionManager()
    {
        if ($this->extensionManager === null) {
            $this->extensionManager = $this->getServiceManager()->get(common_ext_ExtensionsManager::SERVICE_ID);
        }

        return $this->extensionManager;
    }

    /**
     * @param common_ext_Extension $extension
     * @return \common_ext_ExtensionUpdater
     * @throws common_ext_ManifestNotFoundException
     */
    private function getUpdater(common_ext_Extension $extension)
    {
        $updaterClass = $extension->getManifest()->getUpdateHandler();

        if ($updaterClass === null) {
            $this->error('No Updater found for  ' . $extension->getName());
            return null;
        }

        if (!class_exists($updaterClass)) {
            $this->error('Updater ' . $updaterClass . ' not found');
            return null;
        }

        return new $updaterClass($extension);
    }
}