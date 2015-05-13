<?php

namespace ride\application\orm\model\behaviour\initializer;

use ride\library\generator\CodeClass;
use ride\library\generator\CodeGenerator;
use ride\library\orm\definition\ModelTable;
use ride\library\orm\model\behaviour\initializer\BehaviourInitializer;
use ride\library\orm\definition\field\PropertyField;
use ride\application\orm\model\behaviour\SpotlightBehaviour;

/**
 * Setup the unique behaviours based on the model fields
 */
class SpotlightBehaviourInitializer implements BehaviourInitializer {

    /**
     * Gets the behaviours for the model of the provided model table
     * @param \ride\library\orm\definition\ModelTable $modelTable
     * @return array An array with instances of Behaviour
     * @see \ride\library\orm\model\behaviour\Behaviour
     */
    public function getBehavioursForModel(ModelTable $modelTable) {
        $spotLightMaximum = $modelTable->getOption('behaviour.spotlight');
        if (!$spotLightMaximum) {
            return array();
        }

        if (!$modelTable->hasField('inSpotlight')) {
            $isSpotLightField = new PropertyField('inSpotlight', 'boolean');

            $options = array(
                'label.name' => 'label.in.the.spotlight',
                'label.description' => 'label.in.the.spotlight.description'
            );

            //If tabs are used, add a new tab 'visibility' and use this tab for the spotlight checkbox
            if ($tabs = $modelTable->getOption('scaffold.form.tabs')) {
                $options['scaffold.form.tab'] = 'visibility';

                //Add the tab if it isn't available yet
                $tabArray = explode(',', $tabs);
                if (!in_array('visiblity', $tabArray)) {
                    $tabs .= ',visibility';
                    $modelTable->setOption('scaffold.form.tabs', $tabs);
                }

            }
            $isSpotLightField->setOptions($options);

            $spotLightWeightField = new PropertyField('spotlightWeight', 'integer');
            $spotLightWeightField->setOptions(array(
                'scaffold.form.omit' => '1'
            ));

            $modelTable->addField($isSpotLightField);
            $modelTable->addField($spotLightWeightField);
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
