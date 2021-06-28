<?php

require_once __DIR__ . '/../AbstractRESTController.php';
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;

/**
 * This REST endpoint gets details of a product
 */
class SsbtorobrestProductdetailModuleFrontController extends AbstractRESTController
{
    /** @var Product */
    private $product = null;
    private $limit = 100;

    protected function processGetRequest()
    {
	    $page = Tools::getValue('page');
	    if ($page === false) {
		    $page = 1;
	    }

	    if ((int)$page < 1) {
		    $this->ajaxRender(json_encode([
			    'code' => 301,
			    'message' => 'page number must be unsigned int and bigger than zero'
		    ]));
		    die;
	    }

	    //Products Details
	    $productsDetails = array();

	    $products = Product::getProducts(Configuration::get('PS_LANG_DEFAULT'), (($page - 1) * $this->limit), $this->limit, 'date_upd', 'DESC', false, true);
	    foreach ($products as $item) {
		    $this->product = new Product($item['id_product'], true);

		    if (Validate::isLoadedObject($this->product)) {
                $productsDetails[] = $this->getProduct();
		    }
	    }

	    //Pagination details
	    $productsCount = (int)Db::getInstance()->getValue('SELECT COUNT(`id_product`)
			FROM `'._DB_PREFIX_.'product`
			WHERE `active` = 1;');

	    $lastPageNo = (int)($productsCount / $this->limit) + 1;

	    $paging = array();
	    if ($page > 1) {
	    	$paging['previousPageUrl'] = $this->context->link->getBaseLink() . 'torobrest/productdetail?page=' . ($page - 1);
	    }
	    if ($page < $lastPageNo) {
	    	$paging['nextPageUrl'] = $this->context->link->getBaseLink() . 'torobrest/productdetail?page=' . ($page + 1);
	    }
	    $paging['currentPageNo'] = (int)$page;
	    $paging['lastPageNo'] = (int)$lastPageNo;
	    $paging['productCountInCurrentPage'] = (int)count($products);
	    $paging['totalProductCount'] = (int)$productsCount;

	    //Send data
	    if (count($productsDetails) == 0) {
		    $this->ajaxRender(json_encode([
				    'code' => 302,
				    'message' => 'no product available'
			    ]));
			    die;
	    } else {
		    $this->ajaxRender(json_encode([
			    'success'         => true,
			    'code'            => 200,
			    'products' => $productsDetails,
			    'paging'          => $paging,
		    ]));
		    die;
	    }
    }

    protected function processPostRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'POST not supported on this path'
        ]));
        die;
    }

    protected function processPutRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'put not supported on this path'
        ]));
        die;
    }

    protected function processDeleteRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'delete not supported on this path'
        ]));
        die;
    }

    /**
     * Get Product details
     *
     * @return array product data
     */
    public function getProduct()
    {
        $product = array();

        $product['product_id'] = $this->product->id;

        $link = new Link();
	    $url = $link->getProductLink($product['product_id'], null, null, null, Configuration::get('PS_LANG_DEFAULT'));
	    $product['page_url'] = $url;


        if ($this->product->available_for_order) {
	        if ($this->product->out_of_stock == 1) {
		        $product['availability'] = 'instock';
	        } elseif ($this->product->out_of_stock == 0) {
	        	if (StockAvailable::getQuantityAvailableByProduct($this->product->id) > 0) {
			        $product['availability'] = 'instock';
		        } else {
			        $product['availability'] = 'outofstock';
		        }
	        } elseif ($this->product->out_of_stock == 2) {
		        $out_of_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
		        if ($out_of_stock == 1) {
			        $product['availability'] = 'instock';
		        } else {
			        if (StockAvailable::getQuantityAvailableByProduct($this->product->id) > 0) {
				        $product['availability'] = 'instock';
			        } else {
				        $product['availability'] = 'outofstock';
			        }
		        }
	        }
        } else {
	        $product['availability'] = 'outofstock';
        }

        $priceDisplay = Product::getTaxCalculationMethod(0); //(int)$this->context->ccontext->cookieookie->id_customer

	    if (!$priceDisplay || $priceDisplay == 2) {
		    $price = $this->product->getPrice(true);
		    $old_price = $this->product->getPriceWithoutReduct(false);
        } else {
		    $price = $this->product->getPrice(false);
		    $old_price = $this->product->getPriceWithoutReduct(true);
        }

        if ($old_price === $price) {
	        $product['price'] = $price;
        } else {
	        $product['price'] = $price;
	        $product['old_price'] = $old_price;
        }

        return $product;
    }
}