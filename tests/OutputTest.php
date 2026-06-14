<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\Output;

class OutputTest extends TestCase
{
    /** @var resource */
    private $stdout;
    /** @var resource */
    private $stderr;

    protected function setUp(): void
    {
        $this->stdout = fopen('php://memory', 'r+');
        $this->stderr = fopen('php://memory', 'r+');
    }

    protected function tearDown(): void
    {
        fclose($this->stdout);
        fclose($this->stderr);
    }

    private function getContents($stream): string
    {
        rewind($stream);
        return stream_get_contents($stream);
    }

    public function testWrite(): void
    {
        $output = new Output($this->stdout, $this->stderr);
        $output->write('hello');
        $this->assertEquals('hello', $this->getContents($this->stdout));
    }

    public function testWriteln(): void
    {
        $output = new Output($this->stdout, $this->stderr);
        $output->writeln('hello');
        $this->assertEquals('hello' . PHP_EOL, $this->getContents($this->stdout));
    }

    public function testError(): void
    {
        $output = new Output($this->stdout, $this->stderr);
        $output->error('err');
        $this->assertEquals('err', $this->getContents($this->stderr));
        $this->assertEquals('', $this->getContents($this->stdout));
    }

    public function testErrorLine(): void
    {
        $output = new Output($this->stdout, $this->stderr);
        $output->errorLine('err');
        $this->assertEquals('err' . PHP_EOL, $this->getContents($this->stderr));
    }

    public function testSuccessPlain(): void
    {
        $output = new Output($this->stdout, $this->stderr, false);
        $output->success('done');
        $this->assertEquals('done' . PHP_EOL, $this->getContents($this->stdout));
    }

    public function testWarningPlain(): void
    {
        $output = new Output($this->stdout, $this->stderr, false);
        $output->warning('careful');
        $this->assertEquals('careful' . PHP_EOL, $this->getContents($this->stdout));
    }

    public function testInfoPlain(): void
    {
        $output = new Output($this->stdout, $this->stderr, false);
        $output->info('info');
        $this->assertEquals('info' . PHP_EOL, $this->getContents($this->stdout));
    }

    public function testDecoratedOutput(): void
    {
        $output = new Output($this->stdout, $this->stderr, true);
        $output->success('done');
        $this->assertEquals("\033[32mdone\033[0m" . PHP_EOL, $this->getContents($this->stdout));

        // Reset memory stream
        ftruncate($this->stdout, 0);
        rewind($this->stdout);

        $output->warning('careful');
        $this->assertEquals("\033[33mcareful\033[0m" . PHP_EOL, $this->getContents($this->stdout));

        // Reset memory stream
        ftruncate($this->stdout, 0);
        rewind($this->stdout);

        $output->info('info');
        $this->assertEquals("\033[36minfo\033[0m" . PHP_EOL, $this->getContents($this->stdout));
    }

    public function testDecoratedGetterSetter(): void
    {
        $output = new Output($this->stdout, $this->stderr, false);
        $this->assertFalse($output->decorated());
        $output->setDecorated(true);
        $this->assertTrue($output->decorated());
    }

    public function testMemoryStreamOutput(): void
    {
        $output = new Output('php://memory', 'php://memory');
        $output->write('test string');
        $this->addToAssertionCount(1);
    }
}
