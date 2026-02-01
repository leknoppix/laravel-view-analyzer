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
        $analyzer = new NotificationAnalyzer(['notifications_path' => '/non/existent/notifications']);
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

    public function test_it_filters_paths_not_containing_notifications_keyword()
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Other_' . uniqid();
        mkdir($tempDir);

        // Ce chemin ne contient pas "Notifications", il sera filtré
        $analyzer = new NotificationAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
        rmdir($tempDir);
    }

    public function test_it_skips_invalid_directories_in_scan_paths()
    {
        $tempFile = sys_get_temp_dir() . '/NotADirectoryNotifications.php';
        touch($tempFile);

        // Le chemin contient "Notifications" mais c'est un fichier
        $analyzer = new NotificationAnalyzer(['scan_paths' => [$tempFile]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
        unlink($tempFile);
    }

    public function test_it_skips_unreadable_files()
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Notifications_unreadable_' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/UnreadableNotification.php';
        touch($file);
        chmod($file, 0000);

        $analyzer = new NotificationAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        chmod($file, 0644);
        unlink($file);
        rmdir($tempDir);
    }

    public function test_it_returns_empty_if_notifications_directory_exists_but_is_empty(): void
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Notifications_empty_dir_' . uniqid();
        mkdir($tempDir);

        $analyzer = new NotificationAnalyzer(['notifications_path' => $tempDir]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
        rmdir($tempDir);
    }

    public function test_it_uses_default_app_path_when_no_config_provided()
    {
        $path = app_path('Notifications');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $analyzer = new NotificationAnalyzer();
        $results = $analyzer->analyze();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }

    public function test_it_skips_non_existent_default_directory()
    {
        // On fait pointer vers un dossier qui n'existe pas
        $analyzer = new NotificationAnalyzer(['notifications_path' => '/non/existent/path']);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
    }
}
