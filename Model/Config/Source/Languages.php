<?php
/**
 * Valitor Module for Magento 2.x.
 *
 * Copyright © 2020 Valitor. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SDM\Valitor\Model\Config\Source;

use Magento\Config\Model\Config\Source\Locale;
use Magento\Framework\Option\ArrayInterface;
use Valitor\Types\LanguageTypes;

class Languages extends Locale implements ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $languages = [];
        $mainlanguages = parent::toOptionArray();
        $response = new LanguageTypes();
        foreach ($mainlanguages as $keylang => $language) {
            list($key, $tmp) = explode('_', $language['value']);
            if (in_array($key, $response->getAllowed())) {
                $languages[$key] = $language;
            }
        }
        return array_merge([
            ['value' => '', 'label' => '- Auto select -']
        ], $languages);
    }
}
