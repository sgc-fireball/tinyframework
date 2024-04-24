<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Mail;

use TinyFramework\Mail\IMAP;
use TinyFramework\Tests\Feature\FeatureTestCase;

class IMAPTest extends FeatureTestCase
{

    private IMAP|null $imap = null;

    public function setUp(): void
    {
        if (!extension_loaded('imap')) {
            $this->markTestSkipped('Missing ext-imap.');
        }
        parent::setUp();
        if (!env('IMAP_HOST') || !env('IMAP_USERNAME') || !env('IMAP_PASSWORD')) {
            $this->markTestSkipped('Missing IMAP credentials.');
        }
        $this->imap = new IMAP([
            'option' => [
                'validate-cert' => true,
                'ssl' => in_array(env('IMAP_ENCRYPT', 'ssl'), ['tls', 'ssl']),
            ],
            'host' => env('IMAP_HOST'),
            'proto' => 'IMAP',
            'port' => env('IMAP_PORT', 993),
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'folder' => 'INBOX',
        ]);
        if (!($this->imap->open() instanceof IMAP)) {
            $this->markTestSkipped('Could not connect to IMAP Server.');
        }
    }

    public function testConnect(): void
    {
    }

    public function testListFolder(): void
    {
        $folders = $this->imap->listFolders();
        $this->assertGreaterThan(0, count($folders));
    }

    public function testSwitchFolder(): void
    {
        $folders = $this->imap->listFolders();
        $folder = $folders[mt_rand(0, count($folders) - 1)];
        $this->assertInstanceOf(IMAP::class, $this->imap->switchFolder($folder));
    }

    public function testListEmails(): void
    {
        $emails = $this->imap->listEmails();
        $this->assertIsArray($emails);
        $this->assertGreaterThanOrEqual(0, count($emails));
    }

    public function testFolderInformation(): void
    {
        $info = $this->imap->getFolderInformation();
        $this->assertIsArray($info);
        $this->assertArrayHasKey('date', $info);
        $this->assertGreaterThanOrEqual(now()->sub(new \DateInterval('P5M'))->format('u'), strtotime($info['date']));

        $this->assertArrayHasKey('driver', $info);
        $this->assertEquals('imap', $info['driver']);

        $this->assertArrayHasKey('messages', $info);
        $this->assertIsNumeric($info['messages']);
        $this->assertGreaterThanOrEqual(0, $info['messages']);

        $this->assertArrayHasKey('recent', $info);
        $this->assertIsNumeric($info['recent']);
        $this->assertGreaterThanOrEqual(0, $info['recent']);

        $this->assertArrayHasKey('unread', $info);
        $this->assertIsNumeric($info['unread']);
        $this->assertGreaterThanOrEqual(0, $info['unread']);

        $this->assertArrayHasKey('deleted', $info);
        $this->assertIsNumeric($info['deleted']);
        $this->assertGreaterThanOrEqual(0, $info['deleted']);

        $this->assertArrayHasKey('size', $info);
        $this->assertIsNumeric($info['size']);
        $this->assertGreaterThanOrEqual(0, $info['size']);
    }

    public function testListMessages(): void
    {
        $messages = $this->imap->getMessages();
        $this->assertIsArray($messages);
        $this->assertGreaterThanOrEqual(0, count($messages));

        for ($i = 0; $i <= min(count($messages), 10); $i++) {
            $message = $this->imap->getMessageOverviewByUID($messages[$i]);
            $this->assertIsArray($message);
            $this->assertArrayHasKey('subject', $message);
            $this->assertArrayHasKey('from', $message);
            $this->assertArrayHasKey('to', $message);
            $this->assertArrayHasKey('date', $message);
            $this->assertArrayHasKey('message_id', $message);
            $this->assertArrayHasKey('size', $message);
            $this->assertArrayHasKey('uid', $message);
            $this->assertArrayHasKey('msgno', $message);
            $this->assertArrayHasKey('recent', $message);
            $this->assertArrayHasKey('flagged', $message);
            $this->assertArrayHasKey('answered', $message);
            $this->assertArrayHasKey('deleted', $message);
            $this->assertArrayHasKey('seen', $message);
            $this->assertArrayHasKey('draft', $message);
        }
    }

    public function testCreateAndDeleteFolder(): void
    {
        $this->assertInstanceOf(IMAP::class, $this->imap->createFolder('INBOX.phpunit-test'));
        $this->assertInstanceOf(IMAP::class, $this->imap->deleteFolder('INBOX.phpunit-test'));
    }

    public function testGetSetPermissions(): void
    {
        $permissions = $this->imap->getPermissions('INBOX');
        $this->assertIsArray($permissions);
        $this->assertGreaterThanOrEqual(1, count($permissions));
        foreach ($permissions as $user => $permission) {
            $this->assertInstanceOf(IMAP::class, $this->imap->setPermissions('INBOX', $user, $permission));
        }
        $this->assertInstanceOf(IMAP::class, $this->imap->deleteFolder('INBOX.phpunit-test'));
    }

    public function testSubscribe(): void
    {
        $this->assertInstanceOf(IMAP::class, $this->imap->createFolder('INBOX.phpunit-test'));
        $this->assertInstanceOf(IMAP::class, $this->imap->addSubscribe('INBOX.phpunit-test'));
        $subscriptions = $this->imap->listSubscribed();
        $this->assertIsArray($subscriptions);
        $this->assertGreaterThanOrEqual(2, count($subscriptions));
        $this->assertTrue(in_array('INBOX.phpunit-test', $subscriptions));
        $this->assertInstanceOf(IMAP::class, $this->imap->removeSubscribe('INBOX.phpunit-test'));
        $subscriptions = $this->imap->listSubscribed();
        $this->assertIsArray($subscriptions);
        $this->assertGreaterThanOrEqual(1, count($subscriptions));
        $this->assertFalse(in_array('INBOX.phpunit-test', $subscriptions));
        $this->assertInstanceOf(IMAP::class, $this->imap->deleteFolder('INBOX.phpunit-test'));
    }

    public function tearDown(): void
    {
        $this->assertInstanceOf(IMAP::class, $this->imap->close());
        parent::tearDown();;
    }

}
