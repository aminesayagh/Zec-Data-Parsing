<?php 
declare(strict_types=1);

namespace Zod {
    class FIELD {
        const PRIORITIZE = 'prioritize';
        const PARSER_ARGUMENTS = 'parser_arguments';
        const DEFAULT_ARGUMENT = 'default_argument';
        const PRIORITY = 'priority';
        const PARSER_CALLBACK = 'parser_callback';
        const IS_INIT_STATE = 'is_init_state';
        CONST ARGUMENT = 'argument';
    }
}

namespace Zod {
    // Get a list of all const element on Zod\PARSER\KEY
    class PARSERS_KEY {
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
    }

    class CONFIG_KEY {
        const TRUST_ARGUMENTS = 'trust_arguments';
        const STRICT = 'strict';
    }

    class CONFIG_ROLE {
        const READONLY = 'readonly';
        const READWRITE = 'readwrite';
    }

    class LIFECYCLE_PARSER {
        const CREATE = 'create'; // -> build, create a zod instance
        const BUILD = 'build'; // -> assign, parameter by zod Bundle
        const ASSIGN = 'assign'; // -> parse, assign to a zod instance
        const PARSE = 'parse'; // -> validate, parse the value
        const VALIDATE = 'validate'; // -> finalize, validate the value
        const FINALIZE = 'finalize'; // -> finalize the value
    }

}
