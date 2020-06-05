<?php
/**
 * Valitor Module for Magento 2.x.
 *
 * Copyright © 2020 Valitor. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SDM\Valitor\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Frauddetection implements ArrayInterface
{

    private static $availible = [
        '' => '- Disable -',
        'red' => 'Red',
        'maxmind' => 'Maxmind',
        'test' => 'Test'
    ];

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $output = [];
        foreach (self::$availible as $key => $label) {
            $output[] = ['value' => $key, 'label' => $label];
        }
        return $output;
    }
}
