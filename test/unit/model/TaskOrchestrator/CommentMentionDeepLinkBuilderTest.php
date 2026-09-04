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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\TaskOrchestrator;

use InvalidArgumentException;
use oat\tao\model\TaskOrchestrator\CommentMentionDeepLinkBuilder;
use PHPUnit\Framework\TestCase;

class CommentMentionDeepLinkBuilderTest extends TestCase
{
    private CommentMentionDeepLinkBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = new CommentMentionDeepLinkBuilder('https://backoffice.ngs.test');
    }

    public function testBuildItemDeepLinkMatchesBackofficeShape(): void
    {
        $uri = 'https://backoffice.ngs.test/ontologies/tao.rdf#i6a96e3923cff32026090116391476b0b622';

        $url = $this->sut->build(CommentMentionDeepLinkBuilder::OBJECT_TYPE_ITEM, $uri);

        $this->assertSame(
            'https://backoffice.ngs.test/tao/Main/index'
            . '?structure=items'
            . '&ext=taoItems'
            . '&section=manage_items'
            . '&uri=' . rawurlencode($uri),
            $url
        );
    }

    public function testBuildTestAndAssetRoutes(): void
    {
        $uri = 'https://backoffice.ngs.test/ontologies/tao.rdf#iTEST';

        $testUrl = $this->sut->build(CommentMentionDeepLinkBuilder::OBJECT_TYPE_TEST, $uri);
        $assetUrl = $this->sut->build(CommentMentionDeepLinkBuilder::OBJECT_TYPE_ASSET, $uri);

        $this->assertStringContainsString('structure=tests', $testUrl);
        $this->assertStringContainsString('ext=taoTests', $testUrl);
        $this->assertStringContainsString('section=manage_tests', $testUrl);

        $this->assertStringContainsString('structure=taoMediaManager', $assetUrl);
        $this->assertStringContainsString('ext=taoMediaManager', $assetUrl);
        $this->assertStringContainsString('section=media_manager', $assetUrl);
    }

    public function testRejectsUnknownObjectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sut->build('delivery', 'https://example/rdf#i1');
    }
}
