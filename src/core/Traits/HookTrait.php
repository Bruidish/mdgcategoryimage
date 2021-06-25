<?php
/**
 * @author Michel Dumont <https://michel.dumont.io>
 * @version 1.0.0 [2021-06-24] Michel Dumont
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\categoryimage\core\Traits;

use mdg\categoryimage\Forms\CategoryForm;
use mdg\categoryimage\Models\CategoryModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait HookTrait
{
    public function hookFilterCategoryContent(array $params)
    {
        if (isset($params['object'])) {
            $params['object']['mdgCategoryImage'] = [];
            for ($i = 1; $i <= \Configuration::get('MDG_CATEGORYIMAGE_NB_IMAGES'); $i++) {
                $params['object']['mdgCategoryImage'][] = CategoryModel::getImageUrlBySuffix($params['object']['id'], $i);
            }
        }

        return $params;
    }

    #region BO Category
    /** Modification du formulaire
     * @since > PS 1.7.6
     *
     * @inheritdoc
     */
    public function hookActionCategoryFormBuilderModifier(array $params)
    {
        $categoryId = (int) $params['id'];
        $legacyContext = $this->get('prestashop.adapter.legacy.context');
        $categoryForm = new CategoryForm($categoryId, $legacyContext);

        // Suppression de l'image
        if ($deleteImageKey = $params['request']->get('delete-mdgImageCategory')) {
            $categoryForm->processDeleteImage($deleteImageKey);
        }

        // Render du formulaire
        return $categoryForm->modifyFormBuilder($params);
    }

    /** Après création d'une catégorie
     * @since > PS 1.7.6
     *
     * @inheritdoc
     */
    public function hookActionAfterCreateCategoryFormHandler(array $params)
    {
        $categoryId = (int) $params['id'];
        $legacyContext = $this->get('prestashop.adapter.legacy.context');
        $formData = $params['form_data'];
        return (new CategoryForm($categoryId, $legacyContext))->processFormBuilder($formData);
    }

    /** Après modification d'une catégorie
     * @since > PS 1.7.6
     *
     * @inheritdoc
     */
    public function hookActionAfterUpdateCategoryFormHandler(array $params)
    {
        return $this->hookActionAfterCreateCategoryFormHandler($params);
    }
    #endregion BO Category

}
