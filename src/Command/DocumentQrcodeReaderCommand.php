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
        $dir = $this->getContainer()->get('kernel')->getRootDir() . '/../../uploads/';
        $file = $dir .  'NCA-20170705100943-yellow.pdf';
        $im = new \Imagick();
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $im->readImage($file);
        $x = 75;
        $nombrePage = $im->getNumberImages();
        for ($i=0; $i<$nombrePage; $i++){
            $im = new \Imagick();
            $im->setResolution($x,$x);
            $image = $file .'['. $i .']';
            $im->readImage($image);
            $im->writeImage("image".$i.".png");
            $pdf->AddPage();
            $pdf->Image("image".$i.".png");
            try {
                $qrCode = new \QrReader($im, \QrReader::SOURCE_TYPE_RESOURCE);
            }catch (\Exception $e){
                echo "non reconnu";
                continue;
            }
            dump($qrCode->decode());
            unlink("image".$i.".jpg");

        }
       # $pdf->Output($dir.'/test.pdf', "F");



        $output->writeln('Command result.');
    }

}
