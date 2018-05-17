<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegisterBlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => 'Titel des Blogs', 'attr' => array('placeholder' => 'z.B. Mein Blog')))
            ->add('mail', EmailType::class, array('label' => 'Kontakt-Mailadresse', 'attr' => array('placeholder' => 'z.B. kontakt@meinblog.de')))
            ->add('url', TextType::class, array('label' => 'URL des Blog', 'attr' => array('placeholder' => 'z.B. https://www.meinblog.de')))
            ->add('feed', TextType::class, array('label' => 'URL des Blog-Feeds', 'attr' => array('placeholder' => 'z.B. http://meinblog.wordpress.com/feed/')))
            ->add('message', TextType::class, array('label' => 'Nachricht', 'attr' => array('placeholder' => 'z.B. Bitte um eine kurze Rückmeldung, wenn alles ok.')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
