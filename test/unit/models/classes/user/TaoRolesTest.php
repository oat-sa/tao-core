<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\user;

use oat\tao\model\user\TaoRoles;
use PHPUnit\Framework\TestCase;

class TaoRolesTest extends TestCase
{
    public function testMetadataImportAdministratorConstantHasExpectedUri(): void
    {
        $this->assertSame(
            'http://www.tao.lu/Ontologies/TAO.rdf#MetadataImportAdministrator',
            TaoRoles::METADATA_IMPORT_ADMINISTRATOR
        );
    }
}
