<?php
/**
 * class is used to disable editing of admin config fields
 * usage:
 * <field id="fast_prod_js_url" translate="label comment" sortOrder="46" type="text"
 * showInDefault="1" showInWebsite="0" showInStore="0">
 * <frontend_model>Fast\Checkout\Block\System\Config\Form\Field\Disable</frontend_model>
 * <label>Fast PROD JS URL</label>
 * <comment>no trailing slash</comment>
 * </field>
 */

namespace Fast\Checkout\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Disable
 */
class Disable extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    //phpcs:disable
    protected function _getElementHtml(AbstractElement $element)
    {
        //phpcs:enable
        $element->setDisabled('disabled');
        return $element->getElementHtml();

    }
}
