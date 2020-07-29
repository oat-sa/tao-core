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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\helpers;

use stdClass;
use oat\tao\helpers\Base64;
use oat\generis\test\TestCase;

/**
 * Class Base64Test
 *
 * @package oat\tao\test\unit\helpers
 */
class Base64Test extends TestCase
{
    private const ENCODED_IMAGE = 'data:image/jpeg;base64,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A/E0lFFABQevNFJQAtJS0UAFFFFABRjj/wCvRRQAUAe1FFACUtJS0ABPNL25pD1ooAPfijpRSgjIyMj0oAD9McdqSilBxQAlL79Pajmk59aAFxgDP5UlHbmigA49KDRRQADHeiigUAFGKKOlAAeSeMUlLSUALR2opM0ALRSUUALSUUUAL1opKWgA96KO1HH0oAKSlHT3oHWgA7d6DR3ooAKKO1HH+FABnrx1oopKAFPU4oAz/wDroooAKO1Hc0CgAoope1ACUEmlwPWkx9aAF7dKSl5xjPFJQAdulAznjrR2ooABRR2ooAKO1FHagAoNHb3oIx1oASlpKWgA9KSlpKAFzxRSUuaAEpaKKACjNFJQAvSjPFJS4oABRRRQAc55HNGaKXaduevt6UAJR24P1FGetGMcHrQAUlLQKACjijilBI6EigBDjPFKDg8daQ0UAFFLx+NIKAFJGcgUlKaSgBTgAYz054pKKWgBKPrQevFHagAooxxmgjFABRj6UGigAPHHFHXpRR1oASilpKAFpKWkoAWigdeaKADiiikoAWkpeMe9JQAtJSj1pKAClooznrQAE0HrRxRQAUe/eiigA70dKKMfSgANFBAoOM8dKADvS59qTvQKAFopMUUAHej60uTnOM89D3pM0ALzg4zgCgknAJOB0o/Gg4zjI+tAB/OkpcEjNJQAUY6e/NGaKAA0HknoPaijORQAUpBxnHA4Jx0pDQelABSUUtABSUtJQAvejt0oFFAB069aOv1o69BQaACj6UUDjmgAFFFJQAuaSiigBaKSigApaDnjOaKAE7UtGKSgAopaKACjiigGgAzil69BSE5NHGOlACjGeelIR9ac2BlRtbB+8M802gA7UZopQf8A9dACUH2oo7UAB6+lB9qB160UAFLxgYJzjnNJR2oAM8UUUlABS/hSUtAASScmkpT9MUlABS0lKRgmgANJS+3NHTHvQAUZ5zRSUAFFFL/OgAooAycD9aSgBaKKO9AACAwJAI9PWijtRigA7UlFLQAfWig9qCMHBByOtACk5x0o+8RgYpD1PSigBSxPfp0HpR3zzRn3pP0oAO/FFHeigBaSijrQAdu+aBjNLSUAFB60UUAFFFFAB+GaUsWABJOBge1J19qSgApaKSgBcnpmjtR2pKAFooooASijtRQAUUueP/rUlAC0lL3ooAP5UdTSUtABRRRQAUlLyeaKAEpaKSgApcH0NJS0AHGeKOgFGMngGjHvQAuPSko7dqKADviig4zxRQAUUp4yKSgA7UUYooAPwoo46frRQAUDr0o6UUAH5UUUlABRS8YHJz6YpKAFpKKKAClzSUtACUUUtABRRRQAetJRRQAtFAooAKPag0YoASilAyccc0lACgUlLQKADvR+NJSgUAKoywycDOMntQKTvRQAd6XjFAODkUnagBc0nrSk/LjjAPp6/wD6qSgAoxR0ooAO1HFFFAB+NGPeijHWgA60UUAE8Dk0AHbpQaKMUAJRRRQAUvIPoRRSUALRRRQAUdDRSUAFFLRQAUHHXIoooABijFFJQAvJo4I96KKAAUUUlACgcHPGBnnvSUUuOKACiiigAopSpAzg4PQ460CgBKXtg8UfTpSflQAtJS44zSdqADrRSgrg5B6cc0nWgAoo7d6DQAZ5yefrRRRQAZ5oo5Pajv1oAKKPWigBKKKKACilpKAF7UAc4AyelFFACU48HGMYptLQAlFKKSgBaSiigBR19u+KKKOtABRR+FBxk4JI7ZoAKKO1HGKADtRSUpoASlz/AJzSUtABg4zg46Zo/SgkH60o6UAAAzyeKSijtQAo65zzSUH1ooAKKO1FAC/nR0H/ANekooAOpooo56ZoAD+FFGKKACg0YooASiinADcN3APXHOPwoATt1pKWkoAKWkpSScZJOOBmgBKWgdaO4oAOPxoooFABSGlJz2xRQAUlLRxmgA78UUEHGccHpxSUALxSUtBOfwoAKSlooASlCkjgE0UUAFLjNJ3ooAPxo7UdqM0ABHtQBwaOhooAKKOxoHNABQaU9KT2oAOxo7HkU4AGMt3BA/nTT1oAKKO9BoAKXkjgUlB6UAJRRT3UKFx3Gf1IoAbRSU5VBGfcCgBKSilNABRRj5sUUAGaASCCDgjoaDwaSgApTjAwSeOeOlJS5oAB1oo70AfKTQAE5ycUdTzSUZoAKWjHFJQAtFJRQAtAJHr+dB6migD/2Q==';

    /**
     * @dataProvider dataProvider
     *
     * @param $value
     * @param bool $result
     */
    public function testBase64Encoding($value, bool $result): void
    {
        $this->assertEquals($result, Base64::isEncoded($value));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $value
     * @param bool $result
     */
    public function testBase64EncodingImage($value, bool $result): void
    {
        $this->assertEquals($result, Base64::isEncodedImage($value));
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            'object' => [
                'value' => new stdClass(),
                'result' => false,
            ],
            'string' => [
                'value' => 'string',
                'result' => false,
            ],
            'encodedString' => [
                'value' => self::ENCODED_IMAGE,
                'result' => true,
            ],
            'encodedImage' => [
                'value' => self::ENCODED_IMAGE,
                'result' => true,
            ],
        ];
    }
}
