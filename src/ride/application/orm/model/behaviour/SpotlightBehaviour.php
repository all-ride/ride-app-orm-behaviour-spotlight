<?php

namespace ride\application\orm\model\behaviour;

use ride\library\orm\model\Model;
use ride\library\orm\model\behaviour\AbstractBehaviour;
use ride\library\orm\entry\EntryProxy;

/**
 * Behaviour which allows to place a sole entry in the spotlight.
 * If an entry is placed in the spotlight, the previous spotlighted entry will
 * be taken out of the spotlight.
 */
class SpotlightBehaviour extends AbstractBehaviour {

    /**
     * Hook before inserting an entry
     * @param Model $model
     * @param mixed $entry
     * @return null;
     */
    public function preInsert(Model $model, $entry) {
        if ($entry->getInSpotlight()) {
            $entry->saveSpotlight = 1;
        }
    }

    /**
     * Hook before updating a query
     * @param Model $model
     * @param mixed $entry
     * @return null;
     */
    public function preUpdate(Model $model, $entry) {
        if (!isset($entry->saveSpotlight) && $entry instanceof EntryProxy && $entry->isValueLoaded('inSpotlight') && ($entry->getInSpotlight() != $entry->getLoadedValues('inSpotlight'))) {
            $entry->saveSpotlight = 1;
        }
    }

    /**
     * Hook after inserting an entry
     * @param Model $model
     * @param mixed $entry
     * @return null;
     */
    public function postInsert(Model $model, $entry) {
        $this->processSpotlight($model, $entry);
    }

    /**
     * Hook before after updating an entry
     * @param Model $model
     * @param mixed $entry
     * @return null;
     */
    public function postUpdate(Model $model, $entry) {
        $this->processSpotlight($model, $entry);
    }

    /**
     * Processes all spotlight logic.
     * @param \ride\library\orm\model\Model $model
     * @param mixed $entry
     * @return null
     */
    public function processSpotlight(Model $model, $entry) {
        if (!isset($entry->saveSpotlight) || $entry->saveSpotlight <> 1) {
            return;
        }

        // saveSpotlight is immediately set on 2 (which means processed).
        // this way entries don't get saved and processed twice.
        $entry->saveSpotlight = 2;

        $spotlightMaximum = $model->getMeta()->getOption('behaviour.spotlight');

        // adjust all spotlight weights and remove the furthest entry from the
        // spotlight when the maximum number of entries is reached
        $entries = $model->find(array(
            'filter' => array('inSpotlight' => 1),
            'order' => array(
                'field' => 'spotlightWeight',
                'direction' => 'ASC',
            ),
        ));

        $entry->setSpotlightWeight(1);
        $model->save($entry);

        $weight = 2;
        foreach ($entries as $spotlightEntry) {
            if ($spotlightEntry->getId() == $entry->getId()) {
                continue;
            }

            $spotlightEntry->saveSpotlight = 2;

            if ($weight > intval($spotlightMaximum)) {
                $spotlightEntry->setInSpotlight(0);
                $spotlightEntry->setSpotlightWeight(null);
            } else {
                $spotlightEntry->setSpotlightWeight($weight);
            }

            $model->save($spotlightEntry);

            $weight++;
        }
    }

}
