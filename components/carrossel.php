<?php
return [
    'label' => 'Carrossel de Banners',
    'tipo' => 'carrossel',
    'estrutura' => 'dinamico',
    'min' => 2,
    'bloco' => 1,
    'campos' => [
        [
            'nome' => 'imagem',
            'label' => 'Imagem do banner',
            'tipo' => 'url'
        ],
        [
            'nome' => 'link',
            'label' => 'Link do banner',
            'tipo' => 'url'
        ]
    ]
];
