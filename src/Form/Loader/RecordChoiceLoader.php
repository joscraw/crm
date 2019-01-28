<?php

namespace App\Form\Loader;


use App\Entity\Property;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Utils\ArrayHelper;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Class RecordChoiceLoader
 * @package App\Form\Loader
 * @see https://stackoverflow.com/questions/35456199/symfony-2-8-dynamic-choicetype-options
 *
 */
class RecordChoiceLoader implements ChoiceLoaderInterface
{
    use ArrayHelper;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;


    /** @var ChoiceListInterface */
    private $choiceList;

    /**
     * RecordChoiceLoader constructor.
     * @param Options $options
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     */
    public function __construct(
        Options $options,
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository
    ) {
        $this->options = $options;
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
    }


    /**
     * Loads a list of choices.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param callable|null $value The callable which generates the values
     *                             from choices
     *
     * @return ChoiceListInterface The loaded choice list
     */
    public function loadChoiceList($value = null)
    {

        // is called on form view create after loadValuesForChoices of form create
        if ($this->choiceList instanceof ChoiceListInterface) {
            return $this->choiceList;
        }

        // if no values preset yet return empty list
        $this->choiceList = new ArrayChoiceList(array(), $value);

        return $this->choiceList;

    }

    /**
     * Loads the choices corresponding to the given values.
     *
     * The choices are returned with the same keys and in the same order as the
     * corresponding values in the given array.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param string[] $values An array of choice values. Non-existing
     *                              values in this array are ignored
     * @param null $callback
     * @return array An array of choices
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadChoicesForValues(array $values, $callback = null)
    {
        // is called on form submit after loadValuesForChoices of form create and loadChoiceList of form view create
        $choices = array();
        foreach ($values as $key => $val) {
            // we use a DataTransformer, thus only plain values arrive as choices which can be used directly as value
            if (is_callable($callback)) {
                $choices[$key] = (string)call_user_func($callback, $val, $key);
            }
            else {
                $choices[$key] = $val;
            }
        }

        $choices = array_filter($choices, function($choice) {
            $isNull = is_null($choice);
            $isEmpty = empty($choice);

            return !($isNull || $isEmpty);
        });

        if(empty($choices)) {
            $this->choiceList = new ArrayChoiceList([], $callback);
            return [];
        }

        /** @var Property $property */
        $property = $this->options['property'];

        $selectizeAllowedSearchableProperties = $property->getField()->getSelectizeSearchResultProperties();
        $results = $this->recordRepository->getSelectizeAllowedSearchablePropertiesByArrayOfIds($choices, $selectizeAllowedSearchableProperties);
        $allowedCustomObjectToSearch = $property->getField()->getCustomObject();

        $internalNameToLabelMap = $this->propertyRepository->findAllInternalNamesAndLabelsForCustomObject($allowedCustomObjectToSearch);

        $labeledValues = [];
        foreach($results as $result) {

            $items = [];
            foreach ($result as $internalName => $value) {
                $item = [];
                $key = array_search($internalName, array_column($internalNameToLabelMap, 'internalName'));
                if ($key !== false) {
                    $label = $internalNameToLabelMap[$key]['label'];
                } elseif ($internalName === 'id') {
                    $label = 'Id';
                } else {
                    continue;
                }

                $item['internalName'] = $internalName;
                $item['label'] = $label;
                $item['value'] = $value;
                $items[] = $item;
            }

            $labels = [];
            foreach ($items as $item) {
                $labels[] = sprintf("%s: %s", $item['label'], $item['value']);
            }

            $labeledValues[implode(', ', $labels)] = $result['id'];
        }


        $this->choiceList = new ArrayChoiceList($labeledValues, $callback);

        return $choices;
    }

    /**
     * Loads the values corresponding to the given choices.
     *
     * The values are returned with the same keys and in the same order as the
     * corresponding choices in the given array.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param array $choices An array of choices. Non-existing choices in
     *                               this array are ignored
     * @param null $callback
     * @return string[] An array of choice values
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadValuesForChoices(array $choices, $callback = null)
    {
        // is called on form create with $choices containing the preset of the bound entity
        $values = array();
        foreach ($choices as $key => $choice) {
            // we use a DataTransformer, thus only plain values arrive as choices which can be used directly as value
            if (is_callable($callback)) {
                $values[$key] = (string)call_user_func($callback, $choice, $key);
            }
            else {
                $values[$key] = $choice;
            }
        }

        // strip null values from array
        $values = array_filter($values, function($value) {
            $isNull = is_null($value);
            $isEmpty = empty($value);

            return !($isNull || $isEmpty);
        });

        if(empty($values)) {
            $this->choiceList = new ArrayChoiceList([], $callback);
            return [];
        }

        $values = $this->getArrayValuesRecursive($values);

        /** @var Property $property */
        $property = $this->options['property'];

        $selectizeAllowedSearchableProperties = $property->getField()->getSelectizeSearchResultProperties();
        $results = $this->recordRepository->getSelectizeAllowedSearchablePropertiesByArrayOfIds($values, $selectizeAllowedSearchableProperties);
        $allowedCustomObjectToSearch = $property->getField()->getCustomObject();

        $internalNameToLabelMap = $this->propertyRepository->findAllInternalNamesAndLabelsForCustomObject($allowedCustomObjectToSearch);

        $labeledValues = [];
        foreach($results as $result) {

            $items = [];
            foreach ($result as $internalName => $value) {
                $item = [];
                $key = array_search($internalName, array_column($internalNameToLabelMap, 'internalName'));
                if ($key !== false) {
                    $label = $internalNameToLabelMap[$key]['label'];
                } elseif ($internalName === 'id') {
                    $label = 'Id';
                } else {
                    continue;
                }

                $item['internalName'] = $internalName;
                $item['label'] = $label;
                $item['value'] = $value;
                $items[] = $item;
            }

            $labels = [];
            foreach ($items as $item) {
                $labels[] = sprintf("%s: %s", $item['label'], $item['value']);
            }

            $labeledValues[implode(', ', $labels)] = $result['id'];
        }

        $this->choiceList = new ArrayChoiceList($labeledValues, $callback);

        return $values;
    }

}