<?php
declare(strict_types=1);


namespace Zec;

use Exception;
use Zec\CONST\FIELD as FK;
use Zec\CONST\LIFECYCLE_PARSER as LC;
use Zec\Traits;



if (!class_exists('Parser')) {
    class Parser {
        use Traits\ParserArgument, Traits\ParserOrder, Traits\ParserPriority, Traits\ParserLifecycle, Traits\ParserDefault, Traits\ParserOwner;
        private ?string $name = null;
        private array $prioritize = [];
        private mixed $parser_callback = null;
        
        public function __construct(string $name, array $args = []) {
            $default_args = [
                FK::PRIORITIZE => [],
                FK::PRIORITY => self::$DEFAULT_PRIORITY,
                FK::PARSER_ARGUMENTS => null,
                FK::DEFAULT_ARGUMENT => [],
                FK::PARSER_CALLBACK => null
            ];
            $args = array_merge($default_args, $args);

            $this->name = $name;

            $this->setPrioritize($args[FK::PRIORITIZE]);
            $this->setPriorityOfParser($args[FK::PRIORITY]);
            $this->initArgument(
                $args[FK::DEFAULT_ARGUMENT],
                $args[FK::PARSER_ARGUMENTS]
            );

            // assign parser callback
            $this->setParserCallback($args[FK::PARSER_CALLBACK]);

            $this->setLifecycleState(LC::BUILD);
        }
        public function __get($name) {
            return match ($name) {
                'name' => $this->name,
                'prioritize' => $this->prioritize,
                'priority' => $this->priority,
                'order_of_parsing' => $this->order_of_parsing,
                'parser_arguments' => $this->parser_arguments,
                'default_argument' => $this->default_argument,
                'parser_callback' => $this->parser_callback,
                default => throw new Exception("Property $name not found"),
            };
        }
        public function clone(): Parser { 
            return (new Parser($this->name, [
                FK::PRIORITIZE => $this->prioritize,
                FK::PRIORITY => $this->priority,
                FK::PARSER_ARGUMENTS => $this->parser_arguments,
                FK::DEFAULT_ARGUMENT => $this->default_argument,
                FK::PARSER_CALLBACK => $this->parser_callback, 
            ]))->setArgument($this->getArgument());
        }
        public function getConfig(): array {
            return array_merge([
                FK::PRIORITIZE => $this->prioritize,
                FK::PARSER_CALLBACK => $this->parser_callback
            ], $this->argument_parser->getArgument());
        }
        static function proxyResponseZod(bool $is_valid, bool $close = false): array {
            return [
                'is_valid' => $is_valid,
                'close' => $close
            ];
        }
        public function parse(mixed $value): array {
            if (!is_callable($this->parser_callback)) {
                throw new Exception('The parser_callback field must be a callback function');
            }
            $this->setLifecycleState(LC::PARSE);
            
            $argument = $this->getArgument($this->owner); // get the argument of the parser, and check the config of the zec_owner

            // Call the parser callback function
            $response = call_user_func($this->parser_callback, [
                'value' => $value,
                'default' => $this->default,
                'argument' => $argument,
                'owner' => $this->owner
            ]);

            if (is_string($response)) {
                $this->owner->setError(
                    ZecError::fromMessagePath($response, $this->owner->getPath(), [
                        'value' => $value,
                        'parser' => $this->name,
                    ])
                );
                return self::proxyResponseZod(false);
            } else if (is_array($response)) {
                foreach ($response as $value) {
                    $this->owner->setError(
                        ZecError::fromMessagePath($value, $this->owner->getPath(), [
                            'value' => $value,
                            'parser' => $this->name,
                        ])
                    );
                }
                return self::proxyResponseZod(false);
            } else if (is_bool($response) && $response == true) {
                $this->setLifecycleState(LC::VALIDATE)->setLifecycleState(LC::FINALIZE);
                return self::proxyResponseZod(true);
            } else if (is_bool($response) && $response == false) {
                return self::proxyResponseZod(true, true);
            }

            throw new Exception('The parser_callback field must return a string or a boolean');
        }
        private function setPrioritize(array $prioritize): Parser {
            if (!isset($prioritize)) {
                throw new Exception('The accept field is already set');
            }
            if (!is_array($prioritize)) {
                throw new Exception('The accept field must be an array');
            }

            $this->prioritize = $prioritize;
            return $this;
        }
        private function setParserCallback(callable $parser): Parser {
            // if $this->parser is not a callback function, return an error
            if (!is_callable($parser)) {
                throw new Exception('The parser field must be a callback function');
            }
            $this->parser_callback = $parser;
            return $this;
        }
    }
}