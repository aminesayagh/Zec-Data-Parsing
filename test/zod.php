<?php

require '/helpers/zod';

$emailValidator = z()->strng()->email();

$passwordValidator = z()->string()->min([
    'value' => 2,
])->max([
    'value' => 10
])->required()->parse();
