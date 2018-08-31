<?php

/**
 * Class CommandLine - a simple command line arguments parser.
 *
 * The arguments are read from left to right:
 *  - DASH-ed (`--`) ones are recognized as `options` and are kept as a key-value array,
 *  - the first non-DASH-ed argument is considered as a `param`, all subsequent arguments are `params` as well,
 *  - the class supports a DASHES-only argument: it works as a break between `options` and `params`,
 *  - if the first argument is not DASH-ed, the `options` will be just empty,
 *  - each DASH-ed argument *MUST* be in format `--`, `--NAME`, `--NAME=` or `--NAME=VALUE`, where `NAME` consists of `[0-9a-z-]`;
 *    other formats are ignored or cause an exception is thrown (the behaviour is set in the class' constructor)
 *
 * @author Jacek Ciach <jacek.ciach@gmail.com>
 * @version 1.0.1
 */
class CommandLine
{
    const DASHES = "--";

    /**
     * Holds arguments starting with DASHES
     * @var array
     */
    private $options;

    /**
     * Holds arguments after the last DASH-ed one
     * @var array
     */
    private $params;

    /**
     * Constructs the object and reads all arguments from $argv
     *
     * @param bool $throwExceptions throw an exception when an `option` has a wrong format
     * @throws \UnexpectedValueException
     */
    public function __construct(bool $throwExceptions = false)
    {
        global $argv;
        $rawArguments = array_slice($argv, 1); // skip the script name

        /*
         *  Parsing arguments starting with DASHES.
         *  The parser stops after an argument consisting of DASHES only or the last DASH-ed one.
         */
        $this->options = array();
        foreach ($rawArguments as $rawArgument) {

            if (strncmp($rawArgument, self::DASHES, strlen(self::DASHES)) != 0) { // if the $rawArgument doesn't start with DASHES
                break; // stop the parser: the argument does not start with DASHES
            }

            $option = strtolower(substr($rawArgument, strlen(self::DASHES))); // read the part after the DASHES
            if (empty($option)) { // if the option is just DASHES
                $this->options[self::DASHES] = true;
                break; // stop the parser: a DASHES only argument has been found
            } elseif (preg_match("/^([\w-]+)(=(.*))?$/", $option, $matches)) { // if the option has the format like NAME=VALUE
                /*
                 *  - if the option has format NAME=VALUE, then VALUE is added to the $this->options array with key NAME.
                 *  - if the option has format NAME or NAME=, then boolean true is added to the $this->options with key NAME
                 */
                $optionName = $matches[1];
                $optionValue = isset($matches[3]) ? $matches[3] : true;
                $this->options[$optionName] = $optionValue;
            } elseif ($throwExceptions) {
                throw new \UnexpectedValueException("Invalid argument: $rawArgument");
            }

        } // foreach

        /*
         * Read all arguments after the last DASH-ed one
         */
        $this->params = array_slice($rawArguments, count($this->options));
    }

    /**
     * Returns an `option`.
     *
     * If the option does not exist, returns `null` or throws an exception.
     *
     * @param string $optionName `NAME` of the `option`
     * @param bool $throwException throw an exception when an `option` does not exist
     * @return mixed|null
     * @throws \InvalidArgumentException
     */
    public function option(string $optionName, bool $throwException = false)
    {
        if (isset($this->options[$optionName])) {
            return $this->options[$optionName];
        } elseif ($throwException) {
            throw new \InvalidArgumentException("Option '$optionName' does not exist");
        }
        return null;
    }

    /**
     * Returns all options.
     *
     * @return array key-value array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Returns all params as an array.
     *
     * @return array indexed array
     */
    public function params(): array
    {
        return $this->params;
    }
}
