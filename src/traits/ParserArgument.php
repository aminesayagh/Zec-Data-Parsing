<?php
declare(strict_types=1);

namespace Zec\Traits;

use Zec\CONST\CONFIG_KEY as CK;
use Zec\Parser as Parser;
use function Zec\Utils\is_zec as is_zec;
use Zec\Zec as Zec;

if (!trait_exists('ParserArgument')) {
    trait ParserArgument {
        private array $arguments = [];
        private mixed $parser_arguments;
        private mixed $handler_argument = null;
        private bool $is_valid_argument = false;
        private array $default_argument = [];
        public function setIsValidArgument() {
            if($this->is_valid_argument === true) {
                throw new \Exception('Argument is already valid');
            }
            $this->is_valid_argument = true;
        }
        public function initArgument(array $default_argument, callable $parser_arguments) {
            $this->setDefaultArgument($default_argument);
            $this->setParserArguments($parser_arguments);
        }
        private function setDefaultArgument(array $default_argument): void {
            if (!is_array($default_argument)) {
                throw new \Exception('Default argument must be an array');
            }
            $this->default_argument = $default_argument;
        }
        private function setParserArguments(callable $parser_arguments): void {
            if (!is_callable($parser_arguments)) {
                throw new \Exception('Parser argument must be a callable');
            }
            $this->parser_arguments = $parser_arguments;
        }
        public function getArgument(?Zec $owner = null): array {
            try{
                $has_to_parse_argument = $owner == null ? false : $owner->getMeta(Zec::TRUST_ARGUMENTS);
                if (!$has_to_parse_argument) {
                    // $this->setIsValidArgument();
                }
                // return $this->validArgument($owner);
                return $this->mergeArgument();
            }catch(\Exception $err) {
                throw new \Exception('Error on getArgument ' . $err->getMessage());
            }
        }
        private function mergeArgument(): array {
            return array_merge($this->default_argument, $this->arguments);
        }
        private function validArgument(Zec $parent = null): array {
            $merged_argument = $this->mergeArgument();
            if (!$this->is_valid_argument) {
                $z = \Zec\Utils\z();
                
                $argument_zec_validator = call_user_func($this->parser_arguments, $z);
                $argument_zec_validator->parseOrThrow($merged_argument, [
                    'parent' => $parent,
                ]);
                $this->setIsValidArgument();
            }
            return $merged_argument;
        }
        public function setArgument(mixed $argument): Parser {
            
            $this->arguments = $this->defaultArgumentHandler($argument);
            $this->is_valid_argument = false;
            // $this->validArgument();
            return $this;
        }
        private function defaultArgumentHandler(mixed $argument): array {
            if ($argument == null) return [];

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
                        $this->name => $argument
                    ];
                }

                $is_associative_of_zec = array_reduce($argument, function ($carry, $item) {
                    return $carry && is_zec($item);
                }, true);

                if ($is_associative_of_zec) {
                    return [
                        $this->name => $argument
                    ];
                }
                return $argument;
            }

            return [
                $this->name => $argument
            ];
        }
    }
}