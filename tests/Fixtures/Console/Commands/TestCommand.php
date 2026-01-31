<?php

namespace LaravelViewAnalyzer\Tests\Fixtures\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class TestCommand extends Command
{
    protected $signature = 'test:command';

    public function handle()
    {
        // Direct view helper
        $view = view('emails.test', ['user' => 'John']);

        // View facade
        $content = View::make('emails.facade')->render();

        // Response view
        $response = response()->view('emails.response');

        // Return view
        return view('emails.return');
    }

    public function otherMethod()
    {
        // Nested method call
        $this->sendEmail(view('emails.nested'));
    }

    private function sendEmail($view)
    {
        // ...
    }
}
