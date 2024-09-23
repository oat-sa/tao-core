<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\Translation;

use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\TaoOntology;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @example php index.php 'oat\tao\scripts\tools\Translation\PopulateTranslationProperties' [[ --debug ]]
 *          -t http://www.tao.lu/Ontologies/TAOItem.rdf#Item
 */
class PopulateTranslationProperties extends ScriptAction
{
    private const OPTION_TYPE = 'type';
    private const OPTION_DEBUG = 'debug';

    private const SUPPORTED_TYPES = [
        TaoOntology::CLASS_URI_ITEM,
        TaoOntology::CLASS_URI_TEST,
    ];

    protected function provideOptions(): array
    {
        return [
            self::OPTION_TYPE => [
                'prefix' => 't',
                'longPrefix' => self::OPTION_TYPE,
                'defaultValue' => TaoOntology::CLASS_URI_ITEM,
                'description' => 'Resources type to populate translation properties.',
            ],
            self::OPTION_DEBUG => [
                'prefix' => 'd',
                'longPrefix' => self::OPTION_DEBUG,
                'flag' => true,
                'defaultValue' => false,
                'description' => 'Enable debug mode.',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Script to populate translation properties for instances in given type.';
    }

    protected function run(): Report
    {
        try {
            if (!$this->getFeatureFlagChecker()->isEnabled('FEATURE_FLAG_TRANSLATION_ENABLED')) {
                return Report::createError('Translation properties cannot be populated because this feature is disabled');
            }

            $type = $this->getType();
            $debug = $this->getOption(self::OPTION_DEBUG);
            $ontology = $this->getOntology();
            $rootClass = $ontology->getClass($type);

            if (!$rootClass->exists()) {
                throw new InvalidArgumentException(sprintf('Type %s not found', $this->getOption($type)));
            }

            $translationTypeProperty = $ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_TYPE);
            $languageProperty = $ontology->getProperty(TaoOntology::PROPERTY_LANGUAGE);
            $translationStatusProperty = $ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_STATUS);

            $defaultLanguage = TaoOntology::LANGUAGE_PREFIX . $this->getUserLanguageService()->getDefaultLanguage();

            $instances = $rootClass->getInstances(true);
            $numberOfInstances = count($instances);

            $report = Report::createInfo(sprintf('Populating translation properties for type %s...', $type));
            $report->add(Report::createInfo('Number of instances to populate properties: ' . $numberOfInstances));

            $counter = 0;
            $skipped = 0;

            foreach ($instances as $instance) {
                if (++$counter % 10 === 0 || $counter === $numberOfInstances) {
                    $report->add(
                        Report::createInfo(
                            sprintf(
                                'Progress: %d / %d.',
                                $counter,
                                $numberOfInstances,
                            )
                        )
                    );
                }

                $translationType = $instance->getOnePropertyValue($translationTypeProperty);

                if ($translationType !== null) {
                    if ($debug) {
                        $report->add(
                            Report::createInfo(
                                sprintf(
                                    '[%s] Instance properties already populated. Skipping...',
                                    $instance->getUri()
                                )
                            )
                        );
                    }

                    ++$skipped;

                    continue;
                }

                $instance->editPropertyValues(
                    $translationTypeProperty,
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL
                );
                $instance->editPropertyValues(
                    $translationStatusProperty,
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_STATUS_NOT_READY
                );
                $instance->editPropertyValues($languageProperty, $defaultLanguage);
            }

            return $report
                ->add(
                    Report::createSuccess(
                        'Successfully populated translation properties for type ' . $type
                    )
                )->add(
                    Report::createInfo(
                        sprintf(
                            'Skipped %d instances because their properties are already populated',
                            $skipped
                        )
                    )
                );
        } catch (Throwable $exception) {
            return Report::createError(
                'An error occurred while populating translation properties: ' . $exception->getMessage()
            );
        }
    }

    private function getType(): string
    {
        $type = $this->getOption(self::OPTION_TYPE);

        if (!in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Type %s is not supported. Allowed types: %s',
                    $type,
                    implode(', ', self::SUPPORTED_TYPES)
                )
            );
        }

        return $type;
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getPsrContainer()->get(FeatureFlagChecker::class);
    }

    private function getOntology(): Ontology
    {
        return $this->getPsrContainer()->get(Ontology::SERVICE_ID);
    }

    private function getUserLanguageService(): UserLanguageServiceInterface
    {
        return $this->getPsrContainer()->get(UserLanguageServiceInterface::SERVICE_ID);
    }

    private function getPsrContainer(): ContainerInterface
    {
        return $this->getServiceManager()->getContainer();
    }
}
