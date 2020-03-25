<?php

namespace oat\tao\test\integration\form\validators;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\helpers\form\validators\CspHeaderValidator;
use tao_helpers_form_FormFactory;

/**
 * Test cases for the CspHeaderValidator class
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class CspHeaderValidatorTest extends GenerisPhpUnitTestRunner
{
    /**
     * Test the CSP Header form evaluation
     */
    public function testEvaluation()
    {
        $sourceElement = tao_helpers_form_FormFactory::getElement('iframeSourceOption', 'Radiobox');
        $sourceElement->setValue('*');
        $validator = new CspHeaderValidator(['sourceElement' => $sourceElement]);
        $mockValues = $this->getMockValues('invalid');
        $this->assertTrue($validator->evaluate($mockValues), 'Values are ignored, because source option is set to "*"');

        $sourceElement->setValue('list');
        $validator = new CspHeaderValidator(['sourceElement' => $sourceElement]);
        $this->assertFalse($validator->evaluate(''), 'No value is given.');
        $this->assertSame('Please add at least one domain or directive.', $validator->getMessage());

        $validator = new CspHeaderValidator(['sourceElement' => $sourceElement]);
        $this->assertFalse($validator->evaluate($this->getMockValues()), 'Values contain invalid domains/directives');
        $this->assertSame("The following domains are invalid:\n- edgecase.c\n- wrong value\n- thisIsAlsoInvalid\n", $validator->getMessage());

        $validator = new CspHeaderValidator(['sourceElement' => $sourceElement]);
        $this->assertTrue($validator->evaluate($this->getMockValues('valid')), 'Given values are valid');
    }

    /**
     * Get mock values used for testing.
     *
     * @param string $type (both, valid, invalid)
     * @return string
     */
    private function getMockValues($type = 'both')
    {
        $mockValues['valid'] = [
            'http://www.google.com/',
            'yahoo.org',
            '*.reddit.com'
        ];

        $mockValues['invalid'] = [
            'invalid.*domain.com',
            'edgecase.c',
            'wrong value',
            'thisIsAlsoInvalid'
        ];

        switch ($type) {
            case 'valid':
                $returnMockValues = $mockValues['valid'];
                break;
            case 'invalid':
                $returnMockValues = $mockValues['invalid'];
                break;
            case 'both':
            default:
                $returnMockValues = array_merge($mockValues['valid'], $mockValues['invalid']);
                break;
        }


        return implode("\r\n", $returnMockValues);
    }
}
