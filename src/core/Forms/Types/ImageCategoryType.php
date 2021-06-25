<?php
/** Propose une nouvelle extension pour les formulaires symphony
 *
 * @author  Michel Dumont <michel.dumont.io>
 * @version 1.0.0 [2020-06-30]
 * @copyright 2020
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\categoryimage\core\Forms\Types;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageCategoryType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'image_url' => false,
            ])
        ;
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['widget'] = 'mdgImageCategory';
        $view->vars['image_url'] = isset($options['image_url']) ? $options['image_url'] : null;
        $view->vars['image_delete_key'] = $view->vars['name'];
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return FileType::class;
    }
}
