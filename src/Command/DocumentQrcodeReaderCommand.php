<?php

namespace InterInvest\QrCodePdfBundle\DocumentQrcodeReaderCommand;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentQrcodeReaderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('document:qrcode-reader')
            ->setDescription('lecteur Qrcode')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $im = new \Imagick();
        $im->setResolution(200, 200);
        $im->readImage($this->getContainer()->get('kernel')->getRootDir() . '/../web/uploads/inputTask/2017_6_27_94_1755104.pdf[1]');

        $qrCode = new \QrReader($im, \QrReader::SOURCE_TYPE_RESOURCE);
        dump($qrCode->decode());

        $output->writeln('Command result.');
    }

}
