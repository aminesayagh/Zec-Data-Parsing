<?php
declare(strict_types=1);

namespace Zec;
use Zec\FIELD as FK;

if(!trait_exists('ParserOrder')) {
    trait ParserOrder {
        private int $_order_of_parsing = 0;
        public function increment_order(): int {
            $this->_order_of_parsing++;
            return $this->_order_of_parsing;
        }
        public function get_order_parsing(): int {
            return $this->_order_of_parsing;
        }
        public function set_order_parsing(int $order): Parser {
            $this->_order_of_parsing = $order;
            return $this;
        }
    }
}