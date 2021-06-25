<?php
/** Ajoute des fonctionalités à Prestashp/Category
 *
 * @author Michel Dumont <https://michel.dumont.io>
 * @version 1.0.0 [2021-06-24] Michel Dumont
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\categoryimage\core\Models;

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class CategoryModel extends \mdg\categoryimage\Models\ObjectModel
{
    /** @var string dossier contenant les images dans _PS_IMG_DIR_ */
    public const _IMG_DIR_ = "mdgcatgoreyimage/";

    /** @var string|null largeur de l'image en px */
    public const _IMG_WIDTH_ = "805";

    /** @var string|null hauteur de l'image en px */
    public const _IMG_HEIGHT_ = null;

    /** @var string Type de l'image */
    public const _IMG_TYPE_ = 'png';

    public static $definition = [
        'table' => 'categoryimage_category',
        'primary' => 'id_association',
    ];

    /**
     * Builds the object
     *
     * @param int|null $id If specified, loads and existing object from DB (optional).
     * @param int|null $id_lang Required if object is multilingual (optional).
     * @param int|null $id_shop ID shop for objects with multishop tables.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        if ($id_lang && $id_shop) {
            for ($i = 1; $i <= \Configuration::get('MDG_CATEGORYIMAGE_NB_IMAGES'); $i++) {
                $this->{"image-{$i}_url"} = static::getImageUrlBySuffix($this->id, $i);
            }
        }
    }

    /**
     * As we don't save data in base this function is useless but needed to the good work of my module framework
     *
     * @param int
     *
     * @return object
     */
    public static function getInstanceByIdObject($idCategory)
    {
        $that = new static();
        $that->id = $idCategory;
        $that->id_object = $idCategory;
        return $that;
    }

    #region IMAGE
    /** Retourne le chemin de l'icon de la catégorie par rapport à l'état souhaité
     *
     * @param int id de l'objet
     * @param string|int suffix de l'image
     *
     * @return string|false
     */
    public static function getImagePathBySuffix($id, $suffix)
    {
        $imageType = static::_IMG_TYPE_;
        if (file_exists(_PS_IMG_DIR_ . self::_IMG_DIR_ . "{$id}-{$suffix}.{$imageType}")) {
            return _PS_IMG_DIR_ . self::_IMG_DIR_ . "{$id}-{$suffix}.{$imageType}";
        }

        return false;
    }

    /** Retourne l'url de l'icon de la catégorie par rapport à l'état souhaité
     *
     * @param int id de l'objet
     * @param string|int suffix de l'image
     *
     * @return string|false
     */
    public static function getImageUrlBySuffix($id, $suffix)
    {
        $imageType = static::_IMG_TYPE_;
        if (file_exists(_PS_IMG_DIR_ . self::_IMG_DIR_ . "{$id}-{$suffix}.{$imageType}")) {
            return _PS_IMG_ . self::_IMG_DIR_ . "{$id}-{$suffix}.{$imageType}";
        }

        return false;
    }

    /** Supprime l'image de la catégorie par rapport à son index
     *
     * @param string|int
     *
     * @return bool
     */
    public function deleteImageBySuffix($suffix)
    {
        $imageType = static::_IMG_TYPE_;
        $output = true;

        if (is_file(_PS_IMG_DIR_ . self::_IMG_DIR_ . "{$this->id}-{$suffix}.{$imageType}")) {
            $output &= (bool) unlink(_PS_IMG_DIR_ . self::_IMG_DIR_ . "{$this->id}-{$suffix}.{$imageType}");
            clearstatcache(true, _PS_IMG_DIR_ . self::_IMG_DIR_ . "{$this->id}-{$suffix}.{$imageType}");

            $this->{"{$suffix}"} = false;
            $this->{"{$suffix}_url"} = false;
        }

        return $output;
    }
    #endregion IMAGE
}
