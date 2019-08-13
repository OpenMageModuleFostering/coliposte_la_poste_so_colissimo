<?php
require_once 'PHPUnit/Framework.php';
require_once 'app/Mage.php';
/**
 * Test de la méthode de calcul des prix
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Test_Model_Carrier_ShippingMethodTest extends PHPUnit_Framework_TestCase
{
    private $_setup;

    /**
     * Constructeur
     *
     * @return void
     */
    public function __construct()
    {
        Mage::app('default');
        // initialisation du setup
        if (version_compare(Mage::getVersion(), '1.3.2.4', '>')) {
            $this->_setup = Mage::getModel('core/resource_setup');
        }
    }

    /**
     * Préparation des tests
     *
     * @return void
     */
    public function setUp()
    {
        $this->setConfigData('carriers/socolissimosimplicite/active', '1');
        $this->setConfigData('carriers/socolissimosimplicite/title', 'La Poste');
        $this->setConfigData('carriers/socolissimosimplicite/name', 'So Colissimo');
        $this->setConfigData('carriers/socolissimosimplicite/selectmessage', 'En cliquant sur Poursuivre, vous pourrez choisir votre mode de livraison.');
        $this->setConfigData('carriers/socolissimosimplicite/redirectmessage', 'Vous allez être redirigé(e) vers So Colissimo dans un instant...');
        $this->setConfigData('carriers/socolissimosimplicite/account', '05464540472665');
        $this->setConfigData('carriers/socolissimosimplicite/encryption_key', '506336340381');
        $this->setConfigData('carriers/socolissimosimplicite/url_fo', 'http://217.108.161.163/pudo-fo/storeCall.do');
        $this->setConfigData('carriers/socolissimosimplicite/active_service_is_available', '1');
        $this->setConfigData('carriers/socolissimosimplicite/url_service_is_available', 'http://ws.colissimo.fr/supervision-pudo/supervision.jsp');
        $this->setConfigData('carriers/socolissimosimplicite/not_available_message', "La plateforme technique So Colissimo est temporairement indisponible. Votre commande sera livrée à l\'adresse de livraison que vous venez d\'indiquer.");
        $this->setConfigData('carriers/socolissimosimplicite/sallowspecific', '1');
        $this->setConfigData('carriers/socolissimosimplicite/specificcountry', 'FR');
        $this->setConfigData('carriers/socolissimosimplicite/sort_order', '1');
    }

    /**
     * Valorise la configuration en fonction de la version de Magento
     *
     * @param string $path
     * @param string $value
     * @return void
     */
    private function setConfigData($path, $value)
    {
        $this->_setup->setConfigData($path, $value);
    }

    /**
     * Test de la méthode de calcul des montants de livraison
     *
     * @return void
     */
    public function testCollectRatesPerAmount()
    {
        $this->setConfigData('carriers/socolissimosimplicite/minquotepriceforfree', '200');
        $this->setConfigData('carriers/socolissimosimplicite/amountbasetype', 'per_amount');
        $this->setConfigData('carriers/socolissimosimplicite/amountcalculation', '{"0":"3","50":"5","100":"8","250":"0"}');

        // total < 50€ -> 3
        $total = 35.99;
        Mage::getModel('checkout/cart')->getQuote()->getShippingAddress()->setData('subtotal', $total);
        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setData(array('package_value_with_discount' => $total));
        $carrier = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();
        $result = $carrier->collectRates($request);
        $rates = $result->getAllRates();
        $this->assertEquals(count($rates), 1);
        $rate = current($rates);
        $this->assertNotNull($rate);
        $this->assertEquals($rate->getData('carrier'), 'socolissimosimplicite');
        $this->assertEquals($rate->getData('price'), 3);

        // 50€ < total < 100€ -> 5
        $total = 99.99;
        Mage::getModel('checkout/cart')->getQuote()->getShippingAddress()->setData('subtotal', $total);
        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setData(array('package_value_with_discount' => $total));
        $carrier = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();
        $result = $carrier->collectRates($request);
        $rates = $result->getAllRates();
        $this->assertEquals(count($rates), 1);
        $rate = current($rates);
        $this->assertNotNull($rate);
        $this->assertEquals($rate->getData('carrier'), 'socolissimosimplicite');
        $this->assertEquals($rate->getData('price'), 5);

        // 100€ < total < 250€ -> 8
        $total = 125.99;
        Mage::getModel('checkout/cart')->getQuote()->getShippingAddress()->setData('subtotal', $total);
        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setData(array('package_value_with_discount' => $total));
        $carrier = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();
        $result = $carrier->collectRates($request);
        $rates = $result->getAllRates();
        $this->assertEquals(count($rates), 1);
        $rate = current($rates);
        $this->assertNotNull($rate);
        $this->assertEquals($rate->getData('carrier'), 'socolissimosimplicite');
        $this->assertEquals($rate->getData('price'), 8);

        // 250€ < total
        $total = 250.01;
        Mage::getModel('checkout/cart')->getQuote()->getShippingAddress()->setData('subtotal', $total);
        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setData(array('package_value_with_discount' => $total));
        $carrier = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();
        $result = $carrier->collectRates($request);
        $rates = $result->getAllRates();
        $this->assertEquals(count($rates), 1);
        $rate = current($rates);
        $this->assertNotNull($rate);
        $this->assertEquals($rate->getData('carrier'), 'socolissimosimplicite');
        $this->assertEquals($rate->getData('price'), 0);

        // frais de livraison gratuit si total > 200€ -> false, 8
        $total = 115.50;
        Mage::getModel('checkout/cart')->getQuote()->getShippingAddress()->setData('subtotal', $total);
        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setData(array('package_value_with_discount' => $total));
        $carrier = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();
        $result = $carrier->collectRates($request);
        $rates = $result->getAllRates();
        $this->assertEquals(count($rates), 1);
        $rate = current($rates);
        $this->assertNotNull($rate);
        $this->assertEquals($rate->getData('carrier'), 'socolissimosimplicite');
        $this->assertEquals($rate->getData('price'), 8);

        // frais de livraison gratuit si total > 200€ -> true, 0
        $total = 201;
        Mage::getModel('checkout/cart')->getQuote()->getShippingAddress()->setData('subtotal', $total);
        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setData(array('package_value_with_discount' => $total));
        $carrier = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();
        $result = $carrier->collectRates($request);
        $rates = $result->getAllRates();
        $this->assertEquals(count($rates), 1);
        $rate = current($rates);
        $this->assertNotNull($rate);
        $this->assertEquals($rate->getData('carrier'), 'socolissimosimplicite');
        $this->assertEquals($rate->getData('price'), 0);

        // frais de livraison par règle panier
        $total = 25;
        Mage::getModel('checkout/cart')->getQuote()->getShippingAddress()->setData('subtotal', $total);
        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setData(array('package_value_with_discount' => $total, 'free_shipping' => true));
        $carrier = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();
        $result = $carrier->collectRates($request);
        $rates = $result->getAllRates();
        $this->assertEquals(count($rates), 1);
        $rate = current($rates);
        $this->assertNotNull($rate);
        $this->assertEquals($rate->getData('carrier'), 'socolissimosimplicite');
        $this->assertEquals($rate->getData('price'), 0);
    }
}
