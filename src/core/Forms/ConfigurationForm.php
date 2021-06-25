<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 [2021-06-24] Michel Dumont
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\categoryimage\core\Forms;

class ConfigurationForm extends \mdg\categoryimage\Forms\ObjectForm
{
    public $table = null;
    public $identifier = null;
    public $form_name = 'ConfigurationForm';
    public $_html = '';

    /**
     * @inheritdoc
     */
    public function displayControllerFormHelper()
    {
        // Display process
        if (\Tools::isSubmit('submitAdd')) {
            $process = $this->processForm($_POST);
            if ($process) {
                $this->_html .= $this->module->displayConfirmation($this->module->l('Settings saved', $this->form_name));
            } else {
                $this->_html .= $this->module->displayError($this->module->l('An error occured during process', $this->form_name));
            }
        }

        // Prepare Form values
        $this->fields_value = [
            'MDG_CATEGORYIMAGE_NB_IMAGES' => \Configuration::get('MDG_CATEGORYIMAGE_NB_IMAGES'),
        ];

        // Prepare form content
        $this->fields_form = [];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Category settings', $this->form_name),
                    'icon' => 'icon-cog',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Number of additional images', $this->form_name),
                        'name' => 'MDG_CATEGORYIMAGE_NB_IMAGES',
                        'class' => 'fixed-width-xs',
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save', $this->form_name),
                    'icon' => 'process-icon-save',
                ],
            ],
        ];

        #region HELPER
        $helper = new \HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->module = $this->module;
        $helper->identifier = $this->identifier;

        // START FIX BUG - Language
        $languages = \Language::getLanguages(true);
        foreach ($languages as &$language) {
            $language['is_default'] = ($language['id_lang'] == $this->context->language->id);
        }

        // END FIX BUG - Language
        $helper->languages = $languages;
        $helper->default_form_language = $this->context->language->id;
        $helper->id_language = $this->context->language->id;

        $helper->submit_action = $this->form_action;
        $helper->currentIndex = $this->getModuleIndex();
        $helper->token = \Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = ['fields_value' => $this->fields_value];
        $helper->override_folder = '/';

        $this->_html .= $helper->generateForm($this->fields_form);

        return $this->_html;
        #endregion HELPER
    }

    /** Traite l'enregistrement du formulaire de la page produit
     *
     * @param array datas à enregistrer
     *
     * @return bool
     */
    public function processForm($formData)
    {
        $output = true;

        // Enregistrement des données
        foreach ($formData as $key => $value) {
            if (preg_match('/^MDG_/', $key)) {
                $output &= \Configuration::updateValue($key, $value);
            }
        }

        return $output;
    }
}
