<?php
declare(strict_types=1);

namespace Zod;
use Zod\FIELD as FK;
use Zod\CONFIG_KEY as CK;

if (!trait_exists('ParserArgument')) {
    trait ParserArgument {
        private array $_arguments = [];
        private callable|null $_parser_arguments = null;
        private mixed $_handler_argument = null;
        private bool $_is_valid_argument = false;
        private array $_default_argument = [];
        public function init_argument(array $default_argument, callable $_parser_arguments) {
            $this->set_default_argument($default_argument);
            $this->set_parser_arguments($_parser_arguments);
        }
        private function set_default_argument(array $default_argument): void {
            if (!is_array($default_argument)) {
                throw new ZodError('Default argument must be an array');
            }
            $this->_default_argument = $default_argument;
        }
        private function set_parser_arguments(callable $_parser_arguments): void {
            if (!is_callable($_parser_arguments)) {
                throw new ZodError('Parser argument must be a callable');
            }
            $this->_parser_arguments = $_parser_arguments;
        }
        public function get_argument(zod|null $owner = null): array {
            $merged_argument = array_merge($this->_default_argument, $this->_arguments);
            $has_to_parse_argument = !$owner->get_config(CK::TRUST_ARGUMENTS);
            if (!$has_to_parse_argument) {
                $this->_is_valid_argument = true;
            } 
            $this->valid_argument($owner);
            return $merged_argument;
        }
        private function valid_argument(Zod $parent = null) {
            $merged_argument = $this->merge_argument();
            if (!$this->_is_valid_argument) {
                $argument_zod_validator = call_user_func($this->_parser_arguments, z()->set_config(CONFIG_KEY::TRUST_ARGUMENTS, true));
                $argument_zod_validator->parse_or_throw($merged_argument, [
                    'parent' => $parent
                ]);
                $this->_is_valid_argument = true;
            }
            return $merged_argument;
        }
        public function set_argument(array $argument): Parser {
            $this->_arguments = $this->_default_argument_handler($argument);
            $this->_is_valid_argument = false;
            return $this;
        }
        private function _default_argument_handler(mixed $argument): array {
            if (is_null($argument)) return [];

            if (is_array($argument)) {
                $is_associative = false;

                foreach ($argument as $key => $value) {
                    if (!is_int($key)) {
                        $is_associative = true;
                        break;
                    }
                }

                if (!$is_associative) {
                    return [
                        $this->_main_key => $argument
                    ];
                }

                $is_associative_of_zod = array_reduce($argument, function ($carry, $item) {
                    return $carry && is_a($item, 'Zod');
                }, true);

                if ($is_associative_of_zod) {
                    return [
                        $this->_main_key => $argument
                    ];
                }
                return $argument;
            }

            return [
                $this->_main_key => $argument
            ];
        }
    }
}