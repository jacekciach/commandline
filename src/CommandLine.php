<?php

namespace CommandLine;


/**
 * Class CommandLine - a simple command line arguments parser.
 *
 * The arguments are read from left to right:
 *  - DASH-ed (`--`) ones are recognized as `options` and are kept as a key-value array,
 *  - the first non-DASH-ed argument is considered as a `param`, all subsequent arguments are `params` as well,
 *  - the class supports a DASHES-only argument: it works as a break between `options` and `params`,
 *  - if the first argument is not DASH-ed, the `options` will be just empty,
 *  - each DASH-ed argument *MUST* be in format `--`, `--NAME`, `--NAME=` or `--NAME=VALUE`, where `NAME` consists of `[0-9a-z_-]`;
 *    other formats are ignored or cause an exception is thrown (the behaviour is set in the class' constructor)
 *
 * @author Jacek Ciach <jacek.ciach@gmail.com>
 * @version 1.4.0
 */
class CommandLine
{
    const DASHES = "--";

    /**
     * The option is not allowed
     */
    const EXCEPTION_NOT_ALLOWED_OPTION = 1;

    /**
     * The command line argument has a wrong format
     */
    const EXCEPTION_INVALID_ARGUMENT = 2;

    /**
     * The options does not exist
     */
    const EXCEPTION_NONEXISTENT_OPTION = 3;

    /**
     * The param does not exits
     */
    const EXCEPTION_NONEXISTENT_PARAM = 4;

    /**
     * Holds the script's name
     *
     * @var string
     */
    private $script;

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
     * @param bool $throwExceptions throw exceptions instead of ignoring errors
     * @param array|null $allowedOptions options not included in the array will be ignored / throw an exception
     * @throws \InvalidArgumentException
     */
    public function __construct(bool $throwExceptions = false, array $allowedOptions = null)
    {
        global $argv;
        $this->script = $argv[0];
        $rawArguments = array_slice($argv, 1); // skip the script name

        /*
         *  Parsing arguments starting with DASHES.
         *  The parser stops after an argument consisting of DASHES only or the last DASH-ed one.
         */
        $this->options = array();
        $dashedArgumentsCount = 0;
        foreach ($rawArguments as $rawArgument) {

            if (strncmp($rawArgument, self::DASHES, strlen(self::DASHES)) != 0) { // if the $rawArgument doesn't start with DASHES
                break; // stop the parser: the argument does not start with DASHES
            }
            ++$dashedArgumentsCount;

            $option = substr($rawArgument, strlen(self::DASHES)); // read the part after the DASHES
            if (empty($option)) { // if the option is just DASHES
                $this->options[self::DASHES] = true;
                break; // stop the parser: a DASHES only argument has been found
            } elseif (preg_match("/^([\w-]+)(=(.*))?$/", $option, $matches)) { // if the option has the format like NAME=VALUE
                /*
                 *  - if the option has format NAME=VALUE, VALUE is added to $this->options with key NAME
                 *  - if the option has format NAME=, an empty string is added to $this->options with key NAME
                 *  - if the option has format NAME, boolean TRUE is added to $this->options with key NAME
                 */
                $optionName = $matches[1];
                if (is_array($allowedOptions) && !in_array($optionName, $allowedOptions)) {
                    if ($throwExceptions) {
                        throw new \InvalidArgumentException(
                            "Not allowed option '$optionName'",
                            self::EXCEPTION_NOT_ALLOWED_OPTION
                        );
                    }
                    continue;
                }
                $optionValue = isset($matches[3]) ? $matches[3] : true;
                $this->options[$optionName] = $optionValue;
            } elseif ($throwExceptions) {
                throw new \InvalidArgumentException(
                    "Invalid argument: $rawArgument",
                    self::EXCEPTION_INVALID_ARGUMENT
                );
            }

        } // foreach

        /*
         * Read all arguments after the last DASH-ed one
         */
        $this->params = array_slice($rawArguments, $dashedArgumentsCount);
    }

    /**
     * Returns the value of PHP_BINARY
     *
     * @return string
     */
    public function binary(): string
    {
        return PHP_BINARY;
    }

    /**
     * Returns the script's name
     *
     * @return string
     */
    public function script(): string
    {
        return $this->script;
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
            throw new \InvalidArgumentException(
                "Option '$optionName' does not exist",
                self::EXCEPTION_NONEXISTENT_OPTION
            );
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
     * Returns a param.
     *
     * If the param does not exist, returns null or throws an exception.
     *
     * @param int $index
     * @param bool $throwException
     * @return mixed|null
     * @throws \InvalidArgumentException
     */
    public function param(int $index, bool $throwException = false)
    {
        if ($index < 0) {
            throw new \InvalidArgumentException("Index cannot be lower than 0");
        }
        if (isset($this->params[$index])) {
            return $this->params[$index];
        } elseif ($throwException) {
            throw new \InvalidArgumentException(
                "Param '$index' does not exist",
                self::EXCEPTION_NONEXISTENT_PARAM
            );
        }
        return null;
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
