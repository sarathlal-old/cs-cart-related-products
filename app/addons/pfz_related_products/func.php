<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_pfz_related_products_get_products(&$params, &$fields, &$sortings, &$condition, &$join, &$sorting, &$group_by)
{
    if (!empty($params['for_pfz_related_product'])) {
        $join .= " LEFT JOIN ?:product_pfz_related_products ON products.product_id = ?:product_pfz_related_products.pfz_related_id";
        $condition .= db_quote(" AND ?:product_pfz_related_products.product_id = ?i", $params['for_pfz_related_product']);
    }

}



function fn_pfz_related_products_get_product_data_post(&$product, &$auth)
{
	
    if (!empty($product['product_id'])) {
		
		list($pfz_related) = fn_get_products(array('for_pfz_related_product' => $product['product_id']));
		//var_dump($pfz_related);
		    if (count($pfz_related)) {
            $product['have_pfz_related'] = 'Y';
            
            $ids = fn_array_column($pfz_related, 'product_id');
            
            $product['pfz_related_products'] = array ();
            fn_gather_additional_products_data($pfz_related, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => true));

            foreach ($pfz_related as $entry) {
                $id = $entry['product_id'];

                $product['pfz_related_products'][$id] = $entry;
                //$product['pfz_related_products'][$id]['bought'] = 'Y';
            }
            
            $product['can_add_to_cart'] = 'Y';
           
			} else {
            $product['have_pfz_related'] = 'N';
        }
		
    }
    
}

function fn_pfz_related_products_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    return true;
}

function fn_pfz_related_products_form_cart_pre_fill(&$order_id, &$cart, &$auth, &$order_info)
{
    $cart['all_order_product_ids'] = db_get_fields("SELECT product_id FROM ?:order_details WHERE order_id = ?i", $order_info['order_id']);
}

function fn_pfz_related_products_delete_cart_product(&$cart, &$cart_id, &$full_erase)
{
    return true;
}



/**
 * Checks pfz_related products on recalculation
 *
 * @param array $cart Array of the cart contents and user information necessary for purchase
 * @param array $cart_products Cart products
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return bool Always true
 */
function fn_check_calculated_pfz_related_products(&$cart, &$cart_products, $auth)
{
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $key => $entry) {
            if (!empty($entry['product_id'])) {
                $ids = fn_get_required_products_ids($entry['product_id']);

                if (!empty($ids)) {
                    $have = fn_required_products_get_existent($auth, $ids, $cart);
                    if (empty($have) || count($have) != count($ids)) {
                        if (empty($entry['extra']['parent'])) {
                            $cart['amount'] -= $entry['amount'];
                        }
                        unset($cart['products'][$key]);
                        unset($cart_products[$key]);
                        if (isset($cart['product_groups'])) {
                            foreach ($cart['product_groups'] as $key_group => $group) {
                                if (in_array($key, array_keys($group['products']))) {
                                    unset($cart['product_groups'][$key_group]['products'][$key]);
                                }
                            }
                        }
                        fn_check_calculated_required_products($cart, $cart_products, $auth);
                    }
                }
            }
        }
    }

    return true;
}


function fn_pfz_related_products_calculate_cart_items(&$cart, &$cart_products, &$auth)
{
    fn_check_calculated_pfz_related_products($cart, $cart_products, $auth);
}
