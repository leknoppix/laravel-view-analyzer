<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\NotificationAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class NotificationAnalyzerTest extends TestCase
{
    private NotificationAnalyzer $analyzer;

    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'scan_paths' => [__DIR__ . '/../../Fixtures/Notifications'],
            'exclude_paths' => [],
            'analyzers' => [
                'notification' => ['enabled' => true, 'priority' => 32],
            ],
        ];

        $this->analyzer = new NotificationAnalyzer($this->config);
    }

    public function test_it_detects_views_in_notifications()
    {
        // Create a temporary directory for our test notification
        // Must contain "Notifications" to be picked up by the analyzer path filter
        $tempDir = sys_get_temp_dir() . '/view_analyzer_test_Notifications_' . uniqid();
        mkdir($tempDir);

        $content = <<<'PHP'
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoicePaid extends Notification
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->view('emails.invoice.paid', ['invoice' => $this->invoice]);
    }

    public function toMarkdown($notifiable)
    {
        return (new MailMessage)
                    ->markdown('emails.invoice.markdown');
    }
}
PHP;

        file_put_contents($tempDir . '/InvoicePaid.php', $content);

        $analyzer = new NotificationAnalyzer([
            'scan_paths' => [$tempDir],
            'analyzers' => ['notification' => ['enabled' => true]],
        ]);

        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();

        $this->assertContains('emails.invoice.paid', $viewNames);
        $this->assertContains('emails.invoice.markdown', $viewNames);

        $paidRef = $results->where('viewName', 'emails.invoice.paid')->first();
        $this->assertStringContainsString('Notification::toMail', $paidRef->context);
        $this->assertEquals('notification', $paidRef->type);

        // Cleanup
        unlink($tempDir . '/InvoicePaid.php');
        rmdir($tempDir);
    }

    public function test_it_has_correct_metadata()
    {
        $this->assertEquals('Notification Analyzer', $this->analyzer->getName());
        $this->assertTrue($this->analyzer->isEnabled());
        $this->assertEquals(32, $this->analyzer->getPriority());
    }

    public function test_it_respects_disabled_config()
    {
        $analyzer = new NotificationAnalyzer(['analyzers' => ['notification' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());
    }

    public function test_it_skips_non_notification_paths()
    {
        $analyzer = new NotificationAnalyzer(['scan_paths' => ['/some/random/path']]);
        $results = $analyzer->analyze();
        $this->assertCount(0, $results);
    }

    public function test_it_returns_empty_if_default_directory_missing(): void
    {
        $path = app_path('Notifications');
        if (is_dir($path)) {
            $files = glob($path . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($path);
        }

        $analyzer = new NotificationAnalyzer(['scan_paths' => []]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
    }

    public function test_it_skips_empty_files(): void
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Notifications_' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/EmptyNotification.php';
        touch($file);

        $analyzer = new NotificationAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($file);
        rmdir($tempDir);
    }
}
