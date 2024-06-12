<?php 
declare(strict_types=1);

namespace Zec\CONST {
    class FIELD {
        const VERSION = '1.0.0';
        const NAME = 'name';
        const PRIORITIZE = 'prioritize';
        const PARSER_ARGUMENTS = 'parser_arguments';
        const DEFAULT_ARGUMENT = 'default_argument';
        const PRIORITY = 'priority';
        const PARSER_CALLBACK = 'parser_callback';
        const IS_INIT_STATE = 'is_init_state';
        const ARGUMENT = 'argument';
        const LOG_ERROR = 'log_error';
    }
}

namespace Zec\CONST {
    // Get a list of all const element on Zec\PARSER\KEY
    class PARSERS_KEY {
        const VERSION = '1.0.0';
        const REQUIRED = 'required';
        const OPTIONAL = 'optional';
        const EMAIL = 'email';
        const DATE = 'date';
        const BOOL = 'bool';
        const STRING = 'string';
        const URL = 'url';
        const MIN = 'min';
        const MAX = 'max';
        const NUMBER = 'number';
        const OPTIONS = 'options';
        const EACH = 'each';
        const INSTANCEOF = 'instanceof';
        const ASSOCIATIVE = 'associative';
        const LENGTH = 'length';
        const ENUM = 'enum';
    }

    class CONFIG_KEY {
        const VERSION = '1.0.0';
        const TRUST_ARGUMENTS = 'trust_arguments';
        const STRICT = 'strict';
    }

    class CONFIG_ROLE {
        const VERSION = '1.0.0';
        const READONLY = 'readonly';
        const READWRITE = 'readwrite';
    }

    class LIFECYCLE_PARSER {
        const VERSION = '1.0.0';
        const CREATE = 'create'; // -> build, create a zec instance
        const BUILD = 'build'; // -> assign, parameter by zec Bundle
        const ASSIGN = 'assign'; // -> parse, assign to a zec instance
        const PARSE = 'parse'; // -> validate, parse the value
        const VALIDATE = 'validate'; // -> finalize, validate the value
        const FINALIZE = 'finalize'; // -> finalize the value
    }

}
