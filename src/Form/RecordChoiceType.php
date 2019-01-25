<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Record;
use App\Form\DataTransformer\CustomTransformer;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecordChoiceType
 * @package App\Form\Property
 * @see https://speakerdeck.com/heahdude/symfony-forms-use-cases-and-optimization?slide=43
 */
class RecordChoiceType extends AbstractType implements ChoiceLoaderInterface
{
    /**
     * @var RecordRepository
     */
    private $recordRepository;

    private $transformer;

    public function __construct(RecordRepository $recordRepository, IdToRecordTransformer $transformer)
    {
        $this->recordRepository = $recordRepository;
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent()
    {
        return ChoiceType::class;
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
        // We aren't showing any records by default.
        // If you change your mind and want to have some
        // default records you can add that query here
        // below is an example of what that would look like
        return new ArrayChoiceList([]);

/*        $records = $this->recordRepository->findAll();

        $recordValue = function($record) {
            return $record === null ? '' : $record->getId();
        };*/

        return new ArrayChoiceList($records, $recordValue);
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
     * @param callable|null $value The callable generating the choice values
     *
     * @return array An array of choices
     * @throws \Exception
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        if(empty($values)) {
            return [];
        }

        $records = [];
        foreach($values as $record) {
            if(empty($record)) {
                continue;
            }

            $record = $this->recordRepository->find($record);

            if(null === $record) {
                throw new \Exception("Record ID not found.");
            }
            $records[] = $record;
        }

        return $records;
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
     * @param callable|null $value The callable generating the choice values
     *
     * @return string[] An array of choice values
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // There was a weird behavior when [multiple => false] option was being set
        // where a single null array element was being passed in. This simply just strips null values
        $choices = array_filter($choices);

        if(empty($choices)) {
            return [];
        }

        $records = [];
        foreach($choices as $record) {
            $records[] = $record->getId();
        }

        return $records;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_loader' => $this,
            'multiple' => false,
            ]);
    }
}