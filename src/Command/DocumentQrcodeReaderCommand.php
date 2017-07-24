<?php

namespace InterInvest\QrCodePdfBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentQrcodeReaderCommand extends ContainerAwareCommand
{

    protected $dirName;

    public function setDirName($dirName){
        $this->dirName = $dirName;
    }

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
        $dir =$this->getContainer()->getParameter('inputdir');
        $destDir = new \DirectoryIterator($dir);
        dump($destDir);
        foreach ($destDir as $file)
        {
            if (!is_dir($file) && strpos($file->getBasename(),".pdf" ))
            {
                dump($file);

dump($this->dirName = substr($file->getBasename(),0,3));
        exit;
        #$file = $dir .  'NCA-20170705100943-yellow.pdf';
        $im = new \Imagick();

        $im->readImage($file);
        $x = 75;
        $nombrePage = $im->getNumberImages();
        $range = range(0, $nombrePage-1);
        $pdfQrCode = [];

        while ($x <= 200 && !empty($range))
        {
            dump("Résolution : ". $x);
            foreach($range as $i){
                $nomFichier = "image".$i.".png";
                $image = $file .'['. $i .']';
                $imagick = $this->imagick($image, $x, $nomFichier);
                $qrcode = $this->qrCode($imagick);

                if ($qrcode ){
                    dump($qrcode);
                    $pdfQrCode[$qrcode][$i] = $nomFichier;
                    unset($range[$i]);
                }
                elseif($x == 200){
                    $pdfQrCode['inconnu'][$i] = $nomFichier;
                    unset($range[$i]);
                }
            }
            $x +=5;
        }

        $this->generePdfs($pdfQrCode,$dir);


            }
        }
        $output->writeln('Command result.');
    }


    public function imagick($image, $x, $nomFichier)
    {
        $imagick = new \Imagick();
        $imagick->setResolution($x,$x);
        $imagick->readImage($image);
        $imagick->writeImage($nomFichier);
        return $imagick;
    }

    public function qrCode($im)
    {
        try {
            $qrCode = new \QrReader($im, \QrReader::SOURCE_TYPE_RESOURCE);
        }catch (\Exception $e){
            echo "non reconnu";
        }
        return $qrCode->decode();
    }

    public function generePdfs($pdfQrcode, $dir){

        foreach ($pdfQrcode as $qrcode => $page){
            $this->pdf($dir, $page,$qrcode);

        }
    }

    public function pdf($dir, $images, $qrcode)
    {
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        foreach ($images as $image) {
            $pdf->AddPage();
            $pdf->Image($image);
            unlink($image);

        }
        $pdf->Output($dir.'/'. $this->dirName . '/' .$qrcode . '.pdf', "F");
    }

}
