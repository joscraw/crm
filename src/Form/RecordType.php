<?php

namespace App\Form;

use App\Entity\Property;
use App\Entity\Record;
use App\Form\DataTransformer\IdArrayToRecordArrayTransformer;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Form\DataTransformer\RecordCheckboxTransformer;
use App\Form\DataTransformer\RecordDateTimeTransformer;
use App\Form\DataTransformer\RecordGenericTransformer;
use App\Form\DataTransformer\RecordMultipleCheckboxTransformer;
use App\Form\DataTransformer\RecordNumberCurrencyTransformer;
use App\Form\DataTransformer\RecordNumberUnformattedTransformer;
use App\Form\DataTransformer\RecordTimeTransformer;
use App\Model\DatePickerField;
use App\Model\FieldCatalog;
use App\Repository\RecordRepository;
use App\Utils\FormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Class RecordType
 * @package App\Form\Property
 */
class RecordType extends AbstractType implements DataMapperInterface
{

    use FormHelper;

    /**
     * @var IdToRecordTransformer
     */
    private $transformer;

    /**
     * @var IdArrayToRecordArrayTransformer
     */
    private $idArrayToRecordArrayTransformer;

    /**
     * @var RecordDateTimeTransformer
     */
    private $recordDateTimeTransformer;

    /**
     * @var RecordNumberCurrencyTransformer
     */
    private $recordNumberCurrencyTransformer;

    /**
     * @var RecordGenericTransformer
     */
    private $recordGenericTransformer;

    /**
     * @var RecordCheckboxTransformer
     */
    private $recordCheckboxTranformer;

    /**
     * @var RecordNumberUnformattedTransformer
     */
    private $recordNumberUnformattedTransformer;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var RecordMultipleCheckboxTransformer
     */
    private $recordMultipleCheckboxTransformer;

    /**
     * @var RecordTimeTransformer
     */
    private $recordTimeTransformer;

    /**
     * RecordType constructor.
     * @param IdToRecordTransformer $transformer
     * @param IdArrayToRecordArrayTransformer $idArrayToRecordArrayTransformer
     * @param RecordDateTimeTransformer $recordDateTimeTransformer
     * @param RecordNumberCurrencyTransformer $recordNumberCurrencyTransformer
     * @param RecordGenericTransformer $recordGenericTransformer
     * @param RecordCheckboxTransformer $recordCheckboxTranformer
     * @param RecordNumberUnformattedTransformer $recordNumberUnformattedTransformer
     * @param RecordRepository $recordRepository
     * @param RecordMultipleCheckboxTransformer $recordMultipleCheckboxTransformer
     * @param RecordTimeTransformer $recordTimeTransformer
     */
    public function __construct(
        IdToRecordTransformer $transformer,
        IdArrayToRecordArrayTransformer $idArrayToRecordArrayTransformer,
        RecordDateTimeTransformer $recordDateTimeTransformer,
        RecordNumberCurrencyTransformer $recordNumberCurrencyTransformer,
        RecordGenericTransformer $recordGenericTransformer,
        RecordCheckboxTransformer $recordCheckboxTranformer,
        RecordNumberUnformattedTransformer $recordNumberUnformattedTransformer,
        RecordRepository $recordRepository,
        RecordMultipleCheckboxTransformer $recordMultipleCheckboxTransformer,
        RecordTimeTransformer $recordTimeTransformer
    ) {
        $this->transformer = $transformer;
        $this->idArrayToRecordArrayTransformer = $idArrayToRecordArrayTransformer;
        $this->recordDateTimeTransformer = $recordDateTimeTransformer;
        $this->recordNumberCurrencyTransformer = $recordNumberCurrencyTransformer;
        $this->recordGenericTransformer = $recordGenericTransformer;
        $this->recordCheckboxTranformer = $recordCheckboxTranformer;
        $this->recordNumberUnformattedTransformer = $recordNumberUnformattedTransformer;
        $this->recordRepository = $recordRepository;
        $this->recordMultipleCheckboxTransformer = $recordMultipleCheckboxTransformer;
        $this->recordTimeTransformer = $recordTimeTransformer;
    }

    /**
     * @param Record|null $viewData
     */
    public function mapDataToForms($viewData, $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof Record) {
            throw new UnexpectedTypeException($viewData, Record::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $data = $viewData->getProperties();

        foreach($forms as $name => $form) {
            $forms[$name]->setData(array_key_exists($name, $data) ? $data[$name] : "");
        }
    }

    public function mapFormsToData($forms, &$viewData)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $data = [];
        foreach($forms as $name => $form) {
            // we don't want to save the value of submit buttons!
            if($form instanceof ClickableInterface) {
                continue;
            }
            $data[$name] = $form->getData();
        }
        $viewData->setProperties($data);
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->setDataMapper($this);
        /** @var Property $properties[] */
        $properties = $options['properties'];

        /** @var Property $property */
        foreach($properties as $property) {
            $this->addField($property, $builder, $options);
        }

        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Record::class
        ));

        $resolver->setRequired([
            'properties'
        ]);
    }
}