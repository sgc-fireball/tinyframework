<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Mail;

use PHPUnit\Framework\TestCase;
use TinyFramework\Mail\Mail;

class MailTest extends TestCase
{
    public function testHeader(): void
    {
        $mail = Mail::create();
        $mail->header('test1', 'test1');
        $this->assertEquals(['test1'], $mail->header('test1'));
        $this->assertEquals(['test1' => ['test1']], $mail->header());
        $mail->header('test1', 'test2', false);
        $this->assertEquals(['test1', 'test2'], $mail->header('test1'));
        $this->assertEquals(['test1' => ['test1', 'test2']], $mail->header());
        $mail->header('test1', 'test3', true);
        $this->assertEquals(['test3'], $mail->header('test1'));
        $this->assertEquals(['test1' => ['test3']], $mail->header());
    }

    public function testSender(): void
    {
        $mail = Mail::create();
        $this->assertNull($mail->sender());
        $mail->sender('test@example.net');
        $this->assertEquals('test@example.net', $mail->sender());
    }

    public function testReturnPath(): void
    {
        $mail = Mail::create();
        $this->assertNull($mail->returnPath());
        $mail->returnPath('test@example.net');
        $this->assertEquals('test@example.net', $mail->returnPath());
    }

    public function testReplyTo(): void
    {
        $mail = Mail::create();
        $this->assertNull($mail->replyTo());
        $mail->replyTo('test@example.net');
        $this->assertEquals('test@example.net', $mail->replyTo());
    }

    public function testFrom(): void
    {
        $mail = Mail::create();
        $this->assertIsArray($mail->from());
        $this->assertTrue(empty($mail->from()));
        $this->assertTrue(empty($mail->sender()));
        $this->assertTrue(empty($mail->returnPath()));
        $mail->from('test@example.net');
        $this->assertEquals('test@example.net', $mail->sender());
        $this->assertEquals('test@example.net', $mail->returnPath());
        $this->assertEquals(['email' => 'test@example.net', 'name' => 'test@example.net'], $mail->from());
        $mail->from('test2@example.net', 'test');
        $this->assertEquals('test@example.net', $mail->sender());
        $this->assertEquals('test@example.net', $mail->returnPath());
        $this->assertEquals(['email' => 'test2@example.net', 'name' => 'test'], $mail->from());
    }

    public function testTo(): void
    {
        $mail = Mail::create();
        $this->assertIsArray($mail->to());
        $this->assertTrue(empty($mail->to()));
        $mail->to('test1@example.net');
        $this->assertEquals([['email' => 'test1@example.net', 'name' => 'test1@example.net']], $mail->to());
        $mail->to('test2@example.net', 'test2');
        $this->assertEquals([['email' => 'test1@example.net', 'name' => 'test1@example.net'], ['email' => 'test2@example.net', 'name' => 'test2']], $mail->to());
    }

    public function testCc(): void
    {
        $mail = Mail::create();
        $this->assertIsArray($mail->cc());
        $this->assertTrue(empty($mail->cc()));
        $mail->cc('test1@example.net');
        $this->assertEquals([['email' => 'test1@example.net', 'name' => 'test1@example.net']], $mail->cc());
        $mail->cc('test2@example.net', 'test2');
        $this->assertEquals([['email' => 'test1@example.net', 'name' => 'test1@example.net'], ['email' => 'test2@example.net', 'name' => 'test2']], $mail->cc());
    }

    public function testBcc(): void
    {
        $mail = Mail::create();
        $this->assertIsArray($mail->bcc());
        $this->assertTrue(empty($mail->bcc()));
        $mail->bcc('test1@example.net');
        $this->assertEquals([['email' => 'test1@example.net', 'name' => 'test1@example.net']], $mail->bcc());
        $mail->bcc('test2@example.net', 'test2');
        $this->assertEquals([['email' => 'test1@example.net', 'name' => 'test1@example.net'], ['email' => 'test2@example.net', 'name' => 'test2']], $mail->bcc());
    }

    public function testPriority(): void
    {
        $mail = Mail::create();
        $this->assertEquals(Mail::PRIORITY_NORMAL, $mail->priority());
        $mail->priority(-1);
        $this->assertEquals(Mail::PRIORITY_HIGHEST, $mail->priority());
        $mail->priority(6);
        $this->assertEquals(Mail::PRIORITY_LOWEST, $mail->priority());
    }

    public function testSubject(): void
    {
        $mail = Mail::create();
        $this->assertNull($mail->subject());
        $mail->subject('test');
        $this->assertEquals('test', $mail->subject());
    }

    public function testText(): void
    {
        $mail = Mail::create();
        $this->assertEquals('', $mail->text());
        $mail->html('test1<br>test2');
        $this->assertEquals("test1\ntest2", $mail->text());
        $mail->text('test1 test2');
        $this->assertEquals("test1 test2", $mail->text());
        $mail->html('test3<br>test4');
        $this->assertEquals("test1 test2", $mail->text());
    }

    public function testHtml(): void
    {
        $mail = Mail::create();
        $this->assertEquals('', $mail->html());
        $mail->text("test1\ntest2");
        $this->assertEquals("test1<br />\ntest2", $mail->html());
        $mail->html('<b>test1</b>');
        $this->assertEquals('<b>test1</b>', $mail->html());
        $mail->text('test3');
        $this->assertEquals('<b>test1</b>', $mail->html());
    }

    public function testAttachments(): void
    {
        $mail = Mail::create();
        $this->assertIsArray($mail->attachments());
        $this->assertTrue(empty($mail->attachments()));
    }

    public function testAttachmentFile(): void
    {
        $mail = Mail::create();
        $mail->attachmentFile(__FILE__, basename(__FILE__), 'text/plain');
        $this->assertEquals([[
            'path' => __FILE__,
            'filename' => basename(__FILE__),
            'mimetype' => 'text/plain'
        ]], $mail->attachments());
    }

    public function testAttachmentBody(): void
    {
        $mail = Mail::create();
        $mail->attachmentBody('test1', 'test.txt', 'text/plain');
        $this->assertEquals([[
            'content' => 'test1',
            'filename' => 'test.txt',
            'mimetype' => 'text/plain'
        ]], $mail->attachments());
    }
}
