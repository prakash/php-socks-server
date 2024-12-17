<?php

namespace Prakash\SocksServer\Tests\Command;

use PHPUnit\Framework\TestCase;
use Prakash\SocksServer\Command\SocksProxyCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class SocksProxyCommandTest extends TestCase
{
    public function testAuthUserIsRequired(): void
    {
        $tester = $this->getCommandTester();
        $tester->execute([]);

        $this->assertStringContainsString('Authorized user is required.', $tester->getDisplay());
    }

    public function testAuthPasswordIsRequired(): void
    {
        putenv('SOCKS_USR=usr');

        $tester = $this->getCommandTester();
        $tester->execute([]);

        $this->assertStringContainsString('Authorized user password is required.', $tester->getDisplay());

        $this->resetEnvVars();
    }

    private function getCommandTester(): CommandTester
    {
        $app = new Application();
        $app->add(new SocksProxyCommand());

        $command = $app->find(SocksProxyCommand::getDefaultName());

        return new CommandTester($command);
    }

    private function resetEnvVars(): void
    {
        putenv('SOCKS_USR');
        putenv('SOCKS_PWD');
        putenv('SOCKS_PORT');
    }
}
