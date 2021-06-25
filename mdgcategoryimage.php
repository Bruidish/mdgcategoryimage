<?php
/**
 * @author Michel Dumont <https://michel.dumont.io>
 * @version 1.0.0 [2021-06-24] Michel Dumont
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class mdgcategoryimage extends \Module
{
    use mdg\categoryimage\Traits\ConfigurationTrait;
    use mdg\categoryimage\Traits\HookTrait;

    public function __construct()
    {
        $this->name = 'mdgcategoryimage';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Michel Dumont';
        $this->need_instance = 0;
        $this->bootstrap = 1;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
        $this->ps_versions_dir = 'v17';

        foreach (glob(_PS_MODULE_DIR_ . "{$this->name}/controllers/front/*.php") as $file) {
            $fileName = basename($file, '.php');
            if ($fileName !== 'index') {
                $this->controllers[] = $fileName;
            }
        }

        parent::__construct();

        $this->displayName = $this->l('(mdg) Category images');
        $this->description = $this->l('Display more images for your categories');
    }

    #region INSTALL
    /**
     * @inheritdoc
     */
    public function install()
    {
        if (parent::install()) {
            return (new \mdg\categoryimage\Controllers\InstallerController)->install();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            return (new \mdg\categoryimage\Controllers\InstallerController)->uninstall();
        }

        return false;
    }
    #endregion

}
