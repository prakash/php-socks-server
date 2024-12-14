<?php

namespace Prakash\SocksServer\Command;

use Clue\React\Socks\Server;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:socks-proxy', description: 'Run PHP SOCKS Proxy Server.')]
final class SocksProxyCommand extends Command
{
    private string $authUser;
    private string $authPassword;
    private int $socksPort;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->authUser = getenv('SOCKS_USR');
        $this->authPassword = getenv('SOCKS_PWD');
        $this->socksPort = getenv('SOCKS_PORT') ?: 1080;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($this->authUser)) {
            $output->writeln('Authorized user is required.');
        } elseif (empty($this->authPassword)) {
            $output->writeln('Authorized user password is required.');
        } else {

            // this covers all available IPv4 and IPv6 addresses
            $listenAddress = '[::]';
            $listenUri = sprintf('%s:%s', $listenAddress, $this->socksPort);

            $this->addServer($listenUri);
        }

        return Command::SUCCESS;
    }

    private function addServer(string $uri): void
    {
        $socket = new SocketServer($uri);

        // A workaround to make outbound connections use the same IP
        // as the incoming SOCKS connection by using
        // a public method marked as internal. Sorry! :(
        $socket->on('connection', function (ConnectionInterface $connection) {

            $ipAddress = parse_url($connection->getLocalAddress(), PHP_URL_HOST);

            // IP addresses will be enclosed in brackets
            $ipAddress = trim($ipAddress, '[]');

            // IPv4 addresses will be in IPv6 space,
            // so we need to strip this to obtain the correct IPv4 address
            $prefix = '::ffff:';
            if (str_starts_with($ipAddress, $prefix)) {
                $ipAddress = substr($ipAddress, strlen($prefix));
            }

            $connector = null;
            if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {

                // we need to enclose IPv6 addresses in brackets
                if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $ipAddress = sprintf('[%s]', $ipAddress);
                }

                $connector = new Connector([
                    'tcp' => [
                        'bindto' => sprintf('%s:0', $ipAddress)
                    ]
                ]);
            }

            $handler = new Server(null, $connector, [
                $this->authUser => $this->authPassword
            ]);

            // Here, we are using a public method marked as internal. Sorry! :(
            $handler->onConnection($connection);
        });
    }
}
