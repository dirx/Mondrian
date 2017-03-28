<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Trismegiste\Mondrian\Command\SpaghettiCommand;

/**
 * SpaghettiCommandTest is a unit test for SpaghettiCommand
 */
class SpaghettiCommandTest extends TestTemplate
{

    protected function createCommand()
    {
        return new SpaghettiCommand();
    }

    public function testExecute()
    {
        $this->commonExecute();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage loose is not a valid strategy
     */
    public function testBadParameter()
    {
        $command = $this->application->find($this->cmdName);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'dir' => __DIR__,
            '--strategy' => 'loose',
        ]);
    }

}
