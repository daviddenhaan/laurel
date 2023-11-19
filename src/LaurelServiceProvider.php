<?php

namespace Laurel;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laurel\Commands\ServeCommand;
use Swoole\Http\Request as SwooleRequest;

final class LaurelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(ServeCommand::class);
        }

        $this->mergeConfigFrom($config = __DIR__.'/../config/laurel.php', 'laurel',);

        $this->publishes([
            $config => config_path('laurel.php'),
        ], 'laurel-config');

        Request::macro('swoole', fn (SwooleRequest $request) => Request::create(
            $request->server['request_uri'],
            $request->getMethod(),
            $request->get ?? [],
            $request->cookie ?? [],
            $request->files ?? [],
            $request->server,
            $request->getContent(),
        ));
    }
}
