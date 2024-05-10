<?php

namespace Zod;
use ArrayObject;

require_once ZOD_PATH . '/src/parsers/Parser.php';
require_once ZOD_PATH . '/src/parsers/Parsers.php';
require_once ZOD_PATH . '/src/Exception.php';

if(!interface_exists('IZod')) {
    interface IZod {

    }
}

if(!class_exists('Zod')) {
    class Zod extends Parsers implements IZod {
        /**
         * Holds the array of errors encountered during validation.
         *
         * @var ZodErrors
         */
        private ZodErrors $_errors = new ZodErrors();
        private string $type = 'field';
        private bool $has_valid_parser_state = false;
        private bool $parser_sorted = false;
        private bool $has_valid_default = false;
        public function __construct(array $parsers = []) {
            parent::__construct($parsers);
        }
        private function _update_type(string $new_type): Zod {
            bundler()->
        }
    }
}