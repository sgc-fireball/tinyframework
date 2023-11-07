<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\Components\ProgressBar;
use TinyFramework\Console\Output\Components\Punctation;
use TinyFramework\Console\Output\Components\Table;
use TinyFramework\Console\Output\OutputInterface;

class TestCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Start a test.')
            ->sections([
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'WWW' => 'https://github.com/sgc-fireball/tinyframework'
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        $this->output->writeln(trans('messages.trans'));

        $this->output->writeln(trans('messages.trans.value', ['firstname' => 'Richard', 'lastname' => 'Hülsberg']));

        $message = 'messages.trans.choice';
        $this->output->writeln(trans_choice($message, 0));
        $this->output->writeln(trans_choice($message, 1));
        $this->output->writeln(trans_choice($message, 5));
        $this->output->writeln(trans_choice($message, 10));
        $this->output->writeln(trans_choice($message, 1000));
        $this->output->writeln(trans_choice($message, 2000));

        $message = 'messages.trans.choice.value';
        $this->output->writeln(trans_choice($message, 0, ['test' => 'AAAA']));
        $this->output->writeln(trans_choice($message, 1, ['test' => 'AAAA']));
        $this->output->writeln(trans_choice($message, 5, ['test' => 'AAAA']));
        $this->output->writeln(trans_choice($message, 10, ['test' => 'AAAA']));
        $this->output->writeln(trans_choice($message, 1000, ['test' => 'AAAA']));
        $this->output->writeln(trans_choice($message, 2000, ['test' => 'AAAA']));

        $this->testTable();
        $this->testProgressBar();
        $this->testPunctation();
        return 0;
    }

    private function testTable(): void
    {
        $table = new Table($this->output);
        $table->headerTitle('Books');
        $table->header(['ISBN', 'Title', 'Author']);
        $table->rows([
            ['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'],
            ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'],
            'hr',
            ['960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'],
            ['80-902734-1-6', 'And Then There Were None', 'Agatha Christie'],
        ]);
        $table->footerTitle('Page 1/2');
        //$table->columnWidths('auto');
        //$table->columnWidths([0,0,10]);
        $table->render();
    }

    private function testProgressBar(): void
    {
        $max = 1000;
        $progressBar = new ProgressBar($this->output);
        $formats = [
            'normal',
            'normal_nomax',
            'verbose',
            'verbose_nomax',
            'very_verbose',
            'very_verbose_nomax',
            'debug',
            'debug_nomax',
        ];
        foreach ($formats as $format) {
            $this->output->writeln($format);
            $progressBar->format($format);
            $progressBar->max(strpos($format, 'nomax') === false ? $max : 0);
            $progressBar->start();
            for ($i = 1; $i <= $max; $i++) {
                $progressBar->message('Step ' . $i);
                $progressBar->advance();
                usleep(1_000);
            }
            $progressBar->stop();
        }
    }

    private function testPunctation(): void
    {
        $punctation = new Punctation($this->output);
        $punctation

            ->title('Test')->value(time())->display()
            ->title('Test2')->value(false)->display()
            ->title('Test 3')->value(new \stdClass())->display()
            ->title(str_repeat('Test', (int)($this->output->width() / 5)))->value(new \stdClass())->display()
            ->title(str_repeat('Test', (int)($this->output->width() / 2)))->value(new \stdClass())->display()
            ->title('Test')->value(str_repeat('Test', (int)($this->output->width() / 5)))->display()

            ->title(str_repeat('Test', (int)($this->output->width() / 5)))
            ->value(str_repeat('Test', (int)($this->output->width() / 5)))
            ->display()

            ->title(str_repeat('A', (int)($this->output->width() * 1.2)))
            ->value(str_repeat('A', (int)($this->output->width() * 1.2)))
            ->display()
        ;
    }
}
