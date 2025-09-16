<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ContactMessage;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank(), new Email()]
            ])
            ->add('name', TextType::class, [
                'label' => 'Имя',
                'required' => false
            ])
            ->add('phone', TextType::class, [
                'label' => 'Телефон',
                'required' => false
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Сообщение',
                'constraints' => [new NotBlank()]
            ])
            ->add('recaptcha', EWZRecaptchaType::class, [
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new Recaptcha\IsTrue()
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
        ]);
    }
}
