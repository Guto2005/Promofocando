<?php
return [
    'label' => 'Cinturão de Banners',
    'tipo' => 'cinto',
    'estrutura' => 'blocos',
    'min' => 5,
    'bloco' => 5,
    'campos' => [
        [
            'nome' => 'imagem',
            'label' => 'Imagem',
            'tipo' => 'url'
        ],
        [
            'nome' => 'texto',
            'label' => 'Texto da imagem',
            'tipo' => 'text'
        ],
        [
            'nome' => 'link',
            'label' => 'Link do botão',
            'tipo' => 'url'
        ]
    ]
];
