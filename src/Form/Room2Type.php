<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\Item;
use App\Entity\Room;
use App\Form\ItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class Room2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
            ->add('Name')
            ->add('Height')
            ->add('Depth')
            ->add('Width')
            ->add('Image', FileType::class, [
                'label' => 'Image',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image',
                    ])
                ],
            ])
            ->add('Items', CollectionType::class, array(
                'entry_type'   => ItemType::class,
                'entry_options' => [
                    'label' => false
                ],
                'by_reference' => false,                
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => ['class' => 'item-list'],
            ))
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success'],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
