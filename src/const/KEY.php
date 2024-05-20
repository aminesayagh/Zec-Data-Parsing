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
    }
}
