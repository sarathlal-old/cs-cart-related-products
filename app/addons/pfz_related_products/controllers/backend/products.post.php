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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //
    // Update required products
    //
    if ($mode == 'update') {
        if (!empty($_REQUEST['product_id'])) {
            db_query('DELETE FROM ?:product_pfz_related_products WHERE product_id = ?i', $_REQUEST['product_id']);

            if (!empty($_REQUEST['pfz_related_products'])) {
                $pfz_related_products = explode(',', $_REQUEST['pfz_related_products']);
				//var_dump($pfz_related_products);
                $key = array_search($_REQUEST['product_id'], $pfz_related_products);

                if ($key !== false) {
                    unset($pfz_related_products[$key]);
                }

                $entry = array (
                    'product_id' => $_REQUEST['product_id']
                );

                foreach ($pfz_related_products as $entry['pfz_related_id']) {
                    if (empty($entry['pfz_related_id'])) {
                        continue;
                    }

                    db_query('INSERT INTO ?:product_pfz_related_products ?e', $entry);
                }
            }
        }
    }
}

if ($mode == 'update') {
    $product_id = empty($_REQUEST['product_id']) ? 0 : intval($_REQUEST['product_id']);

    Registry::set('navigation.tabs.pfz_related_products', array (
        'title' => __('pfz_related_products'),
        'js' => true
    ));

    $pfz_related_products = db_get_fields('SELECT pfz_related_id FROM ?:product_pfz_related_products WHERE product_id = ?i', $product_id);
//var_dump($pfz_related_products);
    Tygh::$app['view']->assign('pfz_related_products', $pfz_related_products);
}
