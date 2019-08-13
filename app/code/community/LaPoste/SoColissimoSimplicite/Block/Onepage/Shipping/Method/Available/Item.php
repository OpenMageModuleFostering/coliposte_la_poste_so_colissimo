<?php
/**
 * Mode de livraison
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Block_Onepage_Shipping_Method_Available_Item
    extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * Méthode de livraison
     *
     * @var Mage_Shipping_Model_Carrier_Abstract
     */
    protected $_shippingMethod;

    /**
     * Retourne la méthode de livraison
     *
     * @param Mage_Sales_Model_Quote_Address_Rate $rate
     *
     * @return Mage_Shipping_Model_Carrier_Abstract
     */
    public function getShippingMethod($rate)
    {
        if (is_null($this->_shippingMethod)) {
            $this->_shippingMethod = $rate->getCarrierInstance();
        }

        return $this->_shippingMethod;
    }
}
