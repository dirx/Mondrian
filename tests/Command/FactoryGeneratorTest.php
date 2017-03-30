<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Trismegiste\Mondrian\Command\FactoryGenerator;

/**
 * FactoryGeneratorTest tests the command factory generator
 */
class FactoryGeneratorTest extends RefactorTestCase
{

    protected function createCommand()
    {
        return new FactoryGenerator();
    }

    public function testDryRun()
    {
        $command = $this->application->find($this->cmdName);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => __DIR__ . '/../Fixtures/Refact/ForFactory.php',
            '--dry' => true,
        ]);
    }

}
