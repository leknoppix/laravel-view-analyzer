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

    public function test_it_has_correct_name()
    {
        $analyzer = new MailableAnalyzer();
        $this->assertEquals('Mailable Analyzer', $analyzer->getName());
    }

    public function test_it_has_correct_priority()
    {
        $analyzer = new MailableAnalyzer();
        $this->assertEquals(30, $analyzer->getPriority());
    }

    public function test_it_respects_enabled_config()
    {
        $analyzer = new MailableAnalyzer(['analyzers' => ['mailable' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());

        $analyzer = new MailableAnalyzer(['analyzers' => ['mailable' => ['enabled' => true]]]);
        $this->assertTrue($analyzer->isEnabled());
    }

    public function test_it_skips_empty_files(): void
    {
        $file = app_path('Mail/EmptyMail.php');
        touch($file);

        $analyzer = new MailableAnalyzer();
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($file);
    }

    public function test_it_returns_empty_if_mail_directory_does_not_exist(): void
    {
        $mailPath = app_path('Mail');
        if (is_dir($mailPath)) {
            $files = glob($mailPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($mailPath);
        }

        $analyzer = new MailableAnalyzer();
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
    }
}
