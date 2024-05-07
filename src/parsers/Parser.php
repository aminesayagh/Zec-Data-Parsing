<?php 

use \Zod;
use \Zod\Bandler;

if(!class_exists('Parser')) {
    class Parser {
        private string $_accept;
        private bool $_init_state;
        private array $_arguments = [];
        private int $priority;
        private Zod $_parser_arguments;
        private $_parser;
        public function __construct(private string $_key) {
            $parser_rules = Bandler::getConfig($_key);
        }
    }
}