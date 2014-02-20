<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportType extends AbstractType
{
    private $removeCheckbox;

    /**
     * Constructor
     *
     * @param string $removeCheckbox
     */
    public function __construct($removeCheckbox)
    {
        $this->removeCheckbox = $removeCheckbox;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('attachment', 'file', array(
                'mapped' => false
            ))
        ;
        if ($this->removeCheckbox) {
            $builder
                ->add('remove-existing-entries', 'checkbox', array(
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => array('class' => 'tmsmodelio_removal-warning')
                ))
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tms_model_io_import';
    }
}
