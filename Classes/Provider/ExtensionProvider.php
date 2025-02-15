<?php

namespace T3Monitor\T3monitoringClient\Provider;

/*
 * This file is part of the t3monitoring_client extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\EmConfUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

/**
 * Class ExtensionProvider
 */
class ExtensionProvider implements DataProviderInterface
{

    /**
     * @param array $data
     * @return array
     * @throws \BadFunctionCallException
     */
    public function get(array $data)
    {
        $isv11 = VersionNumberUtility::convertVersionNumberToInteger('11.2') < VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch);
        $isv10 = VersionNumberUtility::convertVersionNumberToInteger('10.2') < VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch);
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $listUtility = $objectManager->get(ListUtility::class);

        $allExtensions = $listUtility->getAvailableExtensions();

        $emConfUtility = GeneralUtility::makeInstance(EmConfUtility::class);
        foreach ($allExtensions as $key => $f) {
            if (is_dir(Environment::getBackendPath() . '/sysext/' . $key . '/')) {
                continue;
            }

            if ($isv11) {
                $emConf = $emConfUtility->includeEmConf($key, $f['packagePath']);
                if ($emConf) {
                    $data['extensions'][$key] = $emConf;
                } else {
                    $data['extensions'][$key] = $f;
                }
            } elseif ($isv10) {
                $data['extensions'][$key] = $emConfUtility->includeEmConf($key, $f);
            } else {
                $data['extensions'][$key] = $emConfUtility->includeEmConf($f);
            }

            $data['extensions'][$key]['isLoaded'] = (int)ExtensionManagementUtility::isLoaded($key);
        }

        return $data;
    }
}
