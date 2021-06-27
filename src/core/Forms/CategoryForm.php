<?php
/**
 * @author Michel Dumont <https://michel.dumont.io>
 * @version 1.0.0 [2021-06-24] Michel Dumont
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\categoryimage\core\Forms;

use mdg\categoryimage\Forms\Types\MdgImageCategoryType;
use mdg\categoryimage\Models\CategoryModel;

class CategoryForm extends \mdg\categoryimage\Forms\ObjectForm
{
    /**
     * @inheritdoc
     */
    public function __construct($object = null, $legacyContext = null)
    {
        parent::__construct($object, $legacyContext);
        parent::constructFormBuilder(__FILE__, CategoryModel::class, $object);
    }

    /** Ajoute des entrées au Formbuilder de symphony
     *
     * @since PS 1.7.6
     *
     * @param array params du hook
     * @param array données de CategoryModel
     *
     * @return void
     */
    public function modifyFormBuilder(&$params)
    {
        $this->dirExists(_PS_IMG_DIR_ . $this->object::_IMG_DIR_);

        $images = explode("\r\n", \Configuration::get('MDG_CATEGORYIMAGE_NB_IMAGES'));
        foreach ($images as $index => $imageName) {
            $params['form_builder']
                ->add(
                    "image-{$index}", MdgImageCategoryType::class,
                    [
                        'label' => $imageName,
                        'required' => false,
                        'image_url' => $this->object::getImageUrlBySuffix($this->object->id, $index),
                    ]
                );
        }
    }

    /** Supprime une image
     *
     * @return boolean
     */
    public function processDeleteImage($suffix)
    {
        $output = $this->object->deleteImageBySuffix(str_replace('image-', '', $suffix));

        return $output;
    }

    /** Traite datas d'un FormBuilder
     * @see Symfony\Component\Form\FormBuilderInterface
     *
     * @param array datas à enregistrer
     *
     * @return bool
     */
    public function processFormBuilder($formData)
    {
        $output = true;

        $images = explode("\r\n", \Configuration::get('MDG_CATEGORYIMAGE_NB_IMAGES'));
        $psImageGenerationMethod = \Configuration::get('PS_IMAGE_GENERATION_METHOD');
        $width = $psImageGenerationMethod == 2 ? null : $this->object::_IMG_WIDTH_;
        $height = $psImageGenerationMethod == 1 ? null : $this->object::_IMG_HEIGHT_;
        foreach ($images as $index => $imageName) {
            $output &= $this->uploadImage($this->object->id, $index, "image-{$index}", $this->object::_IMG_DIR_, $width, $height, $this->object::_IMG_TYPE_);
        }

        return $output;
    }

    /**
     * Créait un dossier s'il n'existe pas
     *
     * @param string
     *
     * @return bool
     */
    public function dirExists($dirPath)
    {
        if (!is_dir($dirPath)) {
            return mkdir($dirPath);
        }
        return true;
    }

    /** Upload une image
     *
     * @param int id de l'objet
     * @param string suffix de l'image
     * @param string clé de la global FILES
     * @param string nom du dossier
     * @param int largeur souhaité
     * @param int hauteur souhaité
     * @param string extension souhaité
     *
     * @return boolean
     */
    public function uploadImage($id, $suffix, $key, $imgDir, $width, $height, $imgType = 'jpg')
    {
        $maxSize = isset($this->max_image_size) ? $this->max_image_size : 0;
        if (isset($_FILES['category'])) {
            $file = [
                'error' => isset($_FILES['category']['error'][$key]) ? $_FILES['category']['error'][$key] : null,
                'name' => isset($_FILES['category']['name'][$key]) ? $_FILES['category']['name'][$key] : null,
                'size' => isset($_FILES['category']['size'][$key]) ? $_FILES['category']['size'][$key] : null,
                'tmp_name' => isset($_FILES['category']['tmp_name'][$key]) ? $_FILES['category']['tmp_name'][$key] : null,
                'type' => isset($_FILES['category']['type'][$key]) ? $_FILES['category']['type'][$key] : null,
            ];
        } else {
            $file = [
                'error' => isset($_FILES[$key]['error']) ? $_FILES[$key]['error'] : null,
                'name' => isset($_FILES[$key]['name']) ? $_FILES[$key]['name'] : null,
                'size' => isset($_FILES[$key]['size']) ? $_FILES[$key]['size'] : null,
                'tmp_name' => isset($_FILES[$key]['tmp_name']) ? $_FILES[$key]['tmp_name'] : null,
                'type' => isset($_FILES[$key]['type']) ? $_FILES[$key]['type'] : null,
            ];
        }

        $this->dirExists(_PS_IMG_DIR_ . "{$imgDir}");

        if ($file['name'] && !empty($file['name'])) {
            // Supprime l'ancienne image
            if ($id) {
                $this->object->deleteImageBySuffix($suffix);
            }

            // Vérifie la validité de l'image
            if ($error = \ImageManager::validateUpload($file, \Tools::getMaxUploadSize($maxSize))) {
                $this->errors[] = $error;
            }

            // upload du fichier temporaire
            $tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
            if (!$tmpName || !move_uploaded_file($file['tmp_name'], $tmpName)) {
                return false;
            }

            // Test de la mémoir nécessaire pour redimensionner l'image
            if (!\ImageManager::checkImageMemoryLimit($tmpName)) {
                $this->errors[] = 'Due to memory limit restrictions, this image cannot be loaded. Please increase your memory_limit value via your server\'s configuration settings.';
            }

            if (empty($this->errors) && !\ImageManager::resize($tmpName, _PS_IMG_DIR_ . "{$imgDir}{$id}-{$suffix}.{$imgType}", $width, $height, $imgType)) {
                $this->errors[] = 'An error occurred while uploading the image.' . _PS_TMP_IMG_DIR_ . $tmpName;
            }

            if (count($this->errors)) {
                return false;
            }

            unlink($tmpName);
            return true;

        }
        return true;
    }
}
