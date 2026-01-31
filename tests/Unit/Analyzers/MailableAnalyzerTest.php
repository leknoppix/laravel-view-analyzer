<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\MailableAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class MailableAnalyzerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(app_path('Mail'))) {
            mkdir(app_path('Mail'), 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up app/Mail
        if (is_dir(app_path('Mail'))) {
            $files = glob(app_path('Mail') . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        parent::tearDown();
    }

    public function test_it_detects_legacy_mailable_views(): void
    {
        $content = <<<'PHP'
<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
class LegacyOrderShipped extends Mailable {
    public function build() {
        return $this->view('emails.orders.shipped');
    }
}
PHP;
        file_put_contents(app_path('Mail/LegacyOrderShipped.php'), $content);

        $analyzer = new MailableAnalyzer();
        $results = $analyzer->analyze();

        $this->assertTrue($results->contains('viewName', 'emails.orders.shipped'));
    }

    public function test_it_detects_modern_content_views(): void
    {
        $content = <<<'PHP'
<?php
namespace App\Mail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
class OrderShipped extends Mailable {
    public function content() {
        return new Content(
            view: 'emails.orders.shipped',
        );
    }
}
PHP;
        file_put_contents(app_path('Mail/OrderShipped.php'), $content);

        $analyzer = new MailableAnalyzer();
        $results = $analyzer->analyze();

        $this->assertTrue($results->contains('viewName', 'emails.orders.shipped'), 'Should detect view in Content object');
    }

    public function test_it_detects_markdown_in_content(): void
    {
        $content = <<<'PHP'
<?php
namespace App\Mail;
use Illuminate\Mail\Mailables\Content;
class OrderShipped extends Mailable {
    public function content() {
        return new Content(
            markdown: 'emails.orders.shipped_markdown',
        );
    }
}
PHP;
        file_put_contents(app_path('Mail/OrderShippedMarkdown.php'), $content);

        $analyzer = new MailableAnalyzer();
        $results = $analyzer->analyze();

        $this->assertTrue($results->contains('viewName', 'emails.orders.shipped_markdown'));
    }

    public function test_it_detects_content_with_positional_arguments(): void
    {
        $content = <<<'PHP'
<?php
namespace App\Mail;
use Illuminate\Mail\Mailables\Content;
class PositionalMail extends Mailable {
    public function content() {
        return new Content('emails.positional');
    }
}
PHP;
        file_put_contents(app_path('Mail/PositionalMail.php'), $content);

        $analyzer = new MailableAnalyzer();
        $results = $analyzer->analyze();

        $this->assertTrue($results->contains('viewName', 'emails.positional'), 'Should detect view as first positional argument of Content');
    }
}
