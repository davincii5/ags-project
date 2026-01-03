<?php

namespace App\Form;

use App\Entity\PurchaseRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Entity\User;

class PurchaseRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
            ])
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
            ])
            // requestedBy should be set in controller, not user input
            ->add('quantity')
            ->add('status') // optional: can be set default 'pending' in entity
            ->add('justification')
            // createdAt is removed, it's set automatically
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseRequest::class,
        ]);
    }
}
