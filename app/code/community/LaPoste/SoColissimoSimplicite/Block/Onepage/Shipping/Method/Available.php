<?php
/**
 * Modes de livraison disponibles
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    protected $_itemRenders = array();

    /**
     * Constructeur
     *
     * @return void
     */
    protected function _construct()
    {
        // rendu par défaut
        $this->addItemRender(
            'default',
            'socolissimosimplicite/onepage_shipping_method_available',
            'socolissimosimplicite/onepage/shipping_method/available/default.phtml'
        );
    }

    /**
     * Ajoute une correspondance dans la table des rendus des modes de livraison
     * Cela permet de personnaliser l'affichage d'un mode de livraison parmi
     * la liste des modes proposés lors du passage d'une commande
     *
     * @param string $type
     * @param string $block
     * @param string $template
     * @return LaPoste_SoColissimoSimplicite_Block_Onepage_Shipping_Method_Available
     */
    public function addItemRender($type, $block, $template)
    {
        $this->_itemRenders[$type] = array(
            'block' => $block,
            'template' => $template,
            'blockInstance' => null
        );

        return $this;
    }

    /**
     * Retourne le renderer approprié selon le mode de livraison
     * Le renderer par défaut est "default"
     *
     * @param  string $type exemples : default|socolissimosimplicite
     * @return  array
     */
    public function getItemRenderer($type)
    {
        if (!isset($this->_itemRenders[$type])) {
            $type = 'default';
        }

        if (is_null($this->_itemRenders[$type]['blockInstance'])) {
             $this->_itemRenders[$type]['blockInstance'] = $this->getLayout()
                ->createBlock($this->_itemRenders[$type]['block'])
                    ->setTemplate($this->_itemRenders[$type]['template'])
                    ->setRenderedBlock($this);
        }

        return $this->_itemRenders[$type]['blockInstance'];
    }

    /**
     * Retourne le code html du mode de livraison donné
     *
     * @param  Mage_Shipping_Model_Carrier_Abstract $item
     * @return  string
     */
    public function getItemHtml($item)
    {
        // le code html retourné dépend du mode de livraison
        $renderer = $this->getItemRenderer($item->getMethod())->setRate($item);

        return $renderer->toHtml();
    }
}
