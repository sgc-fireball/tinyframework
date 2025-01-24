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
use TinyFramework\Console\Prompts;

class TestCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Start a test.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>',
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
        $this->testPrompts();
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
        $table->columnWidths('auto');
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
            ->display();
    }

    private function testPrompts(): void
    {
        $options = [
            'a' => 'Stargate',
            'b' => 'Stargate Part 2',
            'c' => 'Stargate Part 3',
            'd' => 'Stargate Die Entdeckung Atlantis',
            'e' => 'Stargate Die Entdeckung Atlantis Part 2',
            'f' => 'Stargate Die Entdeckung Atlantis Part 3',
            'g' => 'Stargate Atlantis steigt auf',
            'h' => 'Stargate Atlantis steigt auf Part 2',
            'i' => 'Stargate Atlantis steigt auf Part 3',
            'j' => 'Stargate Atlantis verlässt den Planet',
            'k' => 'Stargate Atlantis verlässt den Planet Part 2',
            'l' => 'Stargate Atlantis verlässt den Planet Part 3',
            'm' => 'Stargate The Ark of Truth',
            'n' => 'Stargate The Ark of Truth Part 2',
            'o' => 'Stargate The Ark of Truth Part 3',
        ];
        $prompts = new Prompts($this->input, $this->output);

        $years = (int)$prompts->ask('How old are you?', 18);
        $this->output->writeln('Okay! You are <green>' . $years . '</green> years old.');

        do {
            $pw = $prompts->secret('Please give me your password?', password(8));
            $confirm = $prompts->confirm('Is the password <green>' . $pw . '</green> correct?');
        } while (!$confirm);

        $select = $prompts->select('Which film is the best?', $options, 'd');
        $this->output->writeln('Your selection was: <green>' . $options[$select] . '</green>');

        $answer = $prompts->suggest(
            'Which film is the best?',
            function (string $input) use ($options): array {
                if ($input) {
                    $options = array_filter(
                        $options,
                        function (string $answer) use ($input): bool {
                            return str_starts_with($answer, $input);
                        }
                    );
                }
                return $options;
            }
        );
        $this->output->writeln('Your answer was: <green>' . $answer  . '</green>');

        $answers = $prompts->multiselect('Whats are the three best films?', $options, [], 3);
        foreach($answers as $select) {
            $this->output->writeln('Your selection was: <green>' . $options[$select] . '</green>');
        }

        $answer = $prompts->multiselect('What is the best film?', $options, [], 1);
        $this->output->writeln('Your answer was: <green>' . $options[$answer] . '</green>');
    }

}
