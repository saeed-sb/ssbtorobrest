<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ssbtorobrest extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'ssbtorobrest';
        $this->tab = 'others';
        $this->version = '0.1.0';
        $this->author = 'Saeed Sattar Beglou';
        $this->need_instance = 0;

	    $this->bootstrap = true;

	    parent::__construct();

        $this->displayName = $this->l('Torob Rest API');
        $this->description = $this->l('This module exposes REST API endpoint for access Torob Marketplace');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('moduleRoutes');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
	    $this->context->smarty->assign(array(
	        'access_url' => $this->context->link->getBaseLink() . 'torobrest/productdetail',
        ));

	    return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
    }

    public function hookModuleRoutes()
    {
        return [
            'module-ssbtorobrest-productdetail' => [
                'rule' => 'torobrest/productdetail',
                'keywords' => [],
                'controller' => 'productdetail',
                'params' => [
                    'fc' => 'module',
                    'module' => 'ssbtorobrest'
                ]
            ],
        ];
    }
}
