<?php

namespace InterInvest\QrCodePdfBundle\Command;

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
        $im->readImage($this->getContainer()->get('kernel')->getRootDir() . '/../../uploads/NCA-20170705100943-yellow.pdf[0]');
        //$nbpage = $im->getNumberImages();

        #for ($i = 0; $i < $nbpage; $i++){
          #  $im->readImage($this->getContainer()->get('kernel')->getRootDir() . '/../../uploads/NCA-20170705100943-yellow.pdf' .'['. $i .']');
          #  $im->writeImage("test". $i .'.pdf');

        #}
       // $im->writeImage($this->getContainer()->get('kernel')->getRootDir() . '/../../uploads/NCA-20170705100943-yellow.jpg');
        try {
            $qrCode = new \QrReader($im, \QrReader::SOURCE_TYPE_RESOURCE);
        }catch (\Exception $e){
            dump($e);
            die();
        }
        dump($qrCode->decode());

        $output->writeln('Command result.');
    }

}
