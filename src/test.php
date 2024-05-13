<?php


$user = z()->options([
    'name' => z()->string(),
    'email' => z()->url(),
    'age' => z()->number()
]);