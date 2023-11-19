<?php

namespace Laurel\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as PendingResponse;
use Swoole\Http\Server;
use Symfony\Component\Console\Input\InputArgument;

class ServeCommand extends Command
{
    protected $signature = 'laurel:serve';

    protected $description = 'Start serving your application using Laurel.';

    protected Kernel $kernel;

    protected Repository $config;

    public function __construct(Kernel $kernel, Repository $config)
    {
        $this->config = $config;

        $this->kernel = $kernel;

        parent::__construct();
    }

    public function handle(): int
    {
        if (! $server = $this->makeServer($this->argument('address'))) {
            return self::FAILURE;
        }

        $this->registerRequestCallback($server, $this->kernel);

        return ! $server->start();
    }

    protected function makeServer(string $address): ?Server
    {
        [$host, $port] = $this->parseAddress($address);

        $server = new Server($host, $port);

        $server->set(['worker_num' => $this->config->get('laurel.workers')]);

        return $server;
    }

    protected function registerRequestCallback(Server $server, Kernel $kernel): void
    {
        $server->on('request', static function (SwooleRequest $request, PendingResponse $pending) use ($kernel) {
            /** trash all output, we don't need it */
            ob_start(fn () => null);

            $response = $kernel->handle(
                $request = Request::swoole($request),
            );

            $kernel->terminate($request, $response);

            ob_end_clean();

            $pending->setStatusCode($response->getStatusCode());

            $pending->header = $response->headers->all();

            $pending->end($response->getContent());
        });
    }

    /**
     * @return array{string, int}
     */
    protected function parseAddress(string $address): array
    {
        try {
            [$host, $port] = explode(':', $address, 2);
        } catch (\Throwable) {
            $this->components->error("Address {$address} does not have a port specified.");

            die(1);
        }

        return [$host, (int) $port];
    }

    protected function configure(): void
    {
        $this->addArgument('address', InputArgument::OPTIONAL, 'The address your application will be served on', explode('://', $this->config->get('app.url'), 2)[1]);
    }
}
