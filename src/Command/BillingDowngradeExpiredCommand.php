<?php

namespace App\Command;

use App\Repository\BillingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:billing:downgrade-expired',
    description: 'Downgrade all expired subscription',
)]
class BillingDowngradeExpiredCommand extends Command
{
    private $billingRepository;

    public function __construct(BillingRepository $billingRepository)
    {
        $this->billingRepository = $billingRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('dry-run')) {
            $io->note('Dry run');

            $count = $this->billingRepository->countDowngradeExpired();
        } else {
            $count = $this->billingRepository->downgradeExpired();
        }

        $io->success(sprintf('%d subscription(s) downgraded', $count));

        return Command::SUCCESS;
    }
}
