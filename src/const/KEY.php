<?php 

namespace Zod {
    enum FIELD {
        const ACCEPT = 'accept';
        const IS_INIT_STATE = 'is_init_state';
        const PARSER_ARGUMENTS = 'parser_arguments';
        const PRIORITY = 'priority';
        const DEFAULT_ARGUMENT = 'default_argument';
        const PARSER_CALLBACK = 'parser_callback';
    }
}

namespace Zod {
    // Get a list of all const element on Zod\PARSER\KEY
    enum PARSER {
        const REQUIRED = 'required';
        const OPTIONAL = 'optional';
        const EMAIL = 'email';
        const DATE = 'date';
        const BOOL = 'bool';
        const INPUT = 'input';
        const STRING = 'string';
        const URL = 'url';
        const NUMBER = 'number';
        const OPTIONS = 'options';
        const EACH = 'each';
    }
}
