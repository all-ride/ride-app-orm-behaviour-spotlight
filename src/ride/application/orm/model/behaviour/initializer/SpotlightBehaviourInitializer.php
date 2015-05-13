<?php

namespace ride\application\orm\model\behaviour\initializer;

use ride\library\generator\CodeClass;
use ride\library\generator\CodeGenerator;
use ride\library\orm\definition\ModelTable;
use ride\library\orm\model\behaviour\initializer\BehaviourInitializer;
use ride\library\orm\definition\field\PropertyField;
use ride\application\orm\model\behaviour\SpotlightBehaviour;

/**
 * Setup the spotlight behaviour based on the model fields
 */
class SpotlightBehaviourInitializer implements BehaviourInitializer {

    /**
     * Gets the behaviours for the model of the provided model table
     * @param \ride\library\orm\definition\ModelTable $modelTable
     * @return array An array with instances of Behaviour
     * @see \ride\library\orm\model\behaviour\Behaviour
     */
    public function getBehavioursForModel(ModelTable $modelTable) {
        $spotlightMaximum = $modelTable->getOption('behaviour.spotlight');
        if (!$spotlightMaximum || !is_numeric($spotlightMaximum) || $spotlightMaximum <= 0) {
            return array();
        }

        if (!$modelTable->hasField('inSpotlight')) {
            $options = array(
                'label.name' => 'label.spotlight',
                'label.description' => 'label.spotlight.description'
            );

            // if tabs are used, add a new tab 'visibility' and use this tab for
            // the spotlight checkbox
            $tabs = $modelTable->getOption('scaffold.form.tabs');
            if ($tabs) {
                $options['scaffold.form.tab'] = 'visibility';

                // add the tab if it isn't available yet
                $tabArray = explode(',', str_replace(' ', '', $tabs));
                if (!in_array('visiblity', $tabArray)) {
                    $tabs .= ',visibility';
                    $modelTable->setOption('scaffold.form.tabs', $tabs);
                }
            }

            $inSpotlightField = new PropertyField('inSpotlight', 'boolean');
            $inSpotlightField->setOptions($options);

            $spotlightWeightField = new PropertyField('spotlightWeight', 'integer');
            $spotlightWeightField->setOptions(array(
                'scaffold.form.omit' => '1'
            ));

            $modelTable->addField($inSpotlightField);
            $modelTable->addField($spotlightWeightField);
        }

        return array(new SpotLightBehaviour());
    }

    /**
     * Generates the needed code for the entry class of the provided model table
     * @param ModelTable $modelTable
     * @param \ride\library\generator\CodeGenerator $generator
     * @param \ride\library\generator\CodeClass $class
     * @return null
     * @internal param ModelTable $table
     */
    public function generateEntryClass(ModelTable $modelTable, CodeGenerator $generator, CodeClass $class) {

    }

}
