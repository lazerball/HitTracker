<?php

namespace App\GameBundle\Form\Type;

use App\Model\PlayerData;
use App\Model\Vest;
use App\Repository\VestRepository;
use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    private $settingsManager;
    private $vestRepository;

    public function __construct(SettingsManagerInterface $settingsManager, VestRepository $vestRepository)
    {
        $this->settingsManager = $settingsManager;
        $this->vestRepository = $vestRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gameSettings = $this->settingsManager->load('game');

        $vests = $this->vestRepository->findActiveVests();
        $builder
            ->add('name', TextType::class, [
                  'label' => 'hittracker.game.player_name',
                  'attr' => [
                      'placeholder' => 'hittracker.game.player_name'
                  ]
            ])
            ->add('unit', EntityType::class, [
                  'label' => 'hittracker.game.vest',
                  'class' => Vest::class,
                  'choices' => $vests,
                  'choice_label' => 'id',
                  'placeholder' => 'hittracker.game.vest',
                  'choice_attr' => function (Vest $unit) {
                      return [
                        'data-unit-address' => $unit->getRadioId(),
                        'data-unit-color' => $unit->getColor(),
                      ];
                  },
                  'attr' => [
                      'class' => 'unit',
                  ]
            ])
            ->add('hitPoints', IntegerType::class, [
                  'empty_data' => '',
                  'label' => 'hittracker.game.hit_points',
                  'attr' => [
                    'step' => $gameSettings->get('player_hit_points_deducted'),
                    'class' => 'd-none'
                  ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlayerData::class,
        ]);
    }
}
