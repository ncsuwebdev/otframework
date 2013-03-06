<?php
class Ot_Var_Type_Theme extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Select($this->getName(), array('label' => $this->getLabel() . ':'));

        /* Obtain all directories in the theme folder, add them to the theme
         * array.
         */
        $dirs = array(
            'otThemes'  => 'public/themes/ot/',
            'appThemes' => 'public/themes/'
        );

        foreach ($dirs as $dir) {

            $dirPath = APPLICATION_PATH . '/../' . $dir;

            $themeDirs = scandir($dirPath);

            foreach ($themeDirs as $theme) {

                $path = $dirPath . $theme;

                /* Keep only the directories that are themes (criteria being
                 * that they contain a config.xml); load name and description
                 * into the array
                 */
                if (file_exists($path . '/config.xml')) {

                    $xml = simplexml_load_file($path . '/config.xml');
                    $name        = trim((string)$xml->production->theme->name);
                    $description = trim((string)$xml->production->theme->description);

                    $elm->addMultiOption($theme, $name . ' - ' . $description);
                }
            }
        }

        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        return $elm;
    }
}