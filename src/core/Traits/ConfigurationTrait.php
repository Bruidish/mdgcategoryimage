<?php
/**
 * @author Michel Dumont <https://michel.dumont.io>
 * @version 1.0.0 [2021-06-24] Michel Dumont
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\categoryimage\core\Traits;

use mdg\categoryimage\Forms\ConfigurationForm;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait ConfigurationTrait
{
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return (new ConfigurationForm())->displayControllerFormHelper();
    }
}
