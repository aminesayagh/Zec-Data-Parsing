<?php
declare(strict_types=1);

namespace Zec\Traits;
use Zec\Parser as Parser;

if(!trait_exists('ParserOrder')) {
    trait ParserOrder {
        private int $order_of_parsing = 0;
        public function incrementOrder(): int {
            $this->order_of_parsing++;
            return $this->order_of_parsing;
        }
        public function getOrderParsing(): int {
            return $this->order_of_parsing;
        }
        public function setOrderParsing(int $order): Parser {
            $this->order_of_parsing = $order;
            return $this;
        }
    }
}