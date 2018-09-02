<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace CommandLine;

use PHPUnit\Framework\TestCase;

class CommandLineTest extends TestCase
{
    const MOCK_SCRIPT_NAME = 'script.php';
    const EMPTY_ARGV = array(
        self::MOCK_SCRIPT_NAME,
    );

    private function addMockArguments(array $arguments)
    {
        global $argv;
        $argv = array_merge($argv, $arguments);
    }

    private function addMockArgument(string $name)
    {
        $this->addMockArguments(array($name));
    }

    protected function setUp()
    {
        global $argv;
        $argv = self::EMPTY_ARGV;
    }

    public function testNoArguments()
    {
        $cmd = new CommandLine();
        $this->assertEmpty($cmd->options());
        $this->assertEmpty($cmd->params());
    }

    public function testParamsWithNoOptions()
    {
        $this->addMockArguments(array(
            'one', 'TWO', '--three',
        ));
        $cmd = new CommandLine();
        $this->assertEmpty($cmd->options());
        $this->assertEquals(array('one', 'TWO', '--three'), $cmd->params());
    }

    public function testOptionsWithNoParams()
    {
        $this->addMockArguments(array(
            '--one=1', '--TWO=Second'
        ));
        $cmd = new CommandLine();
        $this->assertEquals(array('one' => '1', 'TWO' => 'Second'), $cmd->options());
        $this->assertEmpty($cmd->params());

        $this->addMockArgument('--');
        $cmd = new CommandLine();
        $this->assertEquals(array('one' => '1', 'TWO' => 'Second', '--' => true), $cmd->options());
        $this->assertEmpty($cmd->params());
    }

    public function testOptionsAndParams()
    {
        $this->addMockArguments(array(
            '--start-now', '--END_date=tomorrow', '--should#be#ignored=value', '--comment=',
            'Param1', '--Param2', 'Param3'
        ));
        $cmd = new CommandLine();
        $this->assertEquals(array('start-now' => true, 'END_date' => 'tomorrow', 'comment' => ''), $cmd->options());
        $this->assertEquals(true, $cmd->option('start-now'));
        $this->assertEquals('tomorrow', $cmd->option('END_date'));
        $this->assertEquals('', $cmd->option('comment'));
        $this->assertEquals(null, $cmd->option('fake'));
        $this->assertEquals(null, $cmd->option('should#be#ignored'));
        $this->assertEquals(array('Param1', '--Param2', 'Param3'), $cmd->params());
        $this->assertEquals('Param1', $cmd->param(0));
        $this->assertEquals('--Param2', $cmd->param(1));
        $this->assertEquals('Param3', $cmd->param(2));
        $this->assertEquals(null, $cmd->param(3));
    }

    public function testOptionsWithDashedBreakAndParams()
    {
        $this->addMockArguments(array(
            '--start', '--',  '--Param1', 'Param2'
        ));
        $cmd = new CommandLine();
        $this->assertEquals(array('start' => true, '--' => true), $cmd->options());
        $this->assertEquals(true, $cmd->option('start'));
        $this->assertEquals(true, $cmd->option('--'));
        $this->assertEquals(array('--Param1', 'Param2'), $cmd->params());
        $this->assertEquals('--Param1', $cmd->param(0));
        $this->assertEquals('Param2', $cmd->param(1));
        $this->assertEquals(null, $cmd->param(2));
    }

    public function testExceptionConstructorInvalidArgument()
    {
        $this->addMockArgument('--invalid()argument');
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid argument: --invalid()argument');
        $this->expectExceptionCode(CommandLine::EXCEPTION_INVALID_ARGUMENT);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $cmd = new CommandLine(true);
    }

    public function testConstructorAllowedOptions()
    {
        $this->addMockArguments(array(
            '--something', '--option1', '--other=yes',
            'P1', 'p2'
        ));
        $cmd = new CommandLine(false, array('option1'));
        $this->assertEquals(null, $cmd->option('something'));
        $this->assertEquals(true, $cmd->option('option1'));
        $this->assertEquals(null, $cmd->option('other'));
        $this->assertEquals(array('P1', 'p2'), $cmd->params());

        $cmd = new CommandLine(false, array());
        $this->assertEquals(null, $cmd->option('something'));
        $this->assertEquals(null, $cmd->option('option1'));
        $this->assertEquals(null, $cmd->option('other'));
        $this->assertEquals(array('P1', 'p2'), $cmd->params());
    }

    public function testExceptionConstructorNotAllowedOption()
    {
        $this->addMockArguments(array('--option1', '--option2'));
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Not allowed option \'option2\'');
        $this->expectExceptionCode(CommandLine::EXCEPTION_NOT_ALLOWED_OPTION);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $cmd = new CommandLine(true, array('option1'));
    }

    public function testExceptionNonexistentOption()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Option \'somebody\' does not exist');
        $this->expectExceptionCode(CommandLine::EXCEPTION_NONEXISTENT_OPTION);
        $cmd = new CommandLine();
        $cmd->option('somebody', true);
    }

    public function testExceptionNonexistentParam()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Param \'23\' does not exist');
        $this->expectExceptionCode(CommandLine::EXCEPTION_NONEXISTENT_PARAM);
        $cmd = new CommandLine();
        $cmd->param(23, true);
    }
}
