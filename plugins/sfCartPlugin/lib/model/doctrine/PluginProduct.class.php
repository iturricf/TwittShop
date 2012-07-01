<?php

/**
 * PluginProduct
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginProduct extends BaseProduct
{

	public static function getProductFromSlug($slug) {
		$product = Doctrine::getTable('product')->createQuery('a')->where('slug=?', $slug)->execute();
		if (count($product) > 0) {
			return $product[0];
		}
		else { return null; }
	}

	public function display() {
		include_partial('cart/productView', array('product' => $this));
	}


}