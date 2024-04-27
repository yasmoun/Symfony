<?php

namespace App\Form;

use App\Entity\PriceSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PriceSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('minPrice', IntegerType::class, [
                'label' => 'Prix minimum',
            ])
            ->add('maxPrice', IntegerType::class, [
                'label' => 'Prix maximum',
            ]);
       
    }

    public function getBlockPrefix()
    {
        return 'price_search';
    }
}