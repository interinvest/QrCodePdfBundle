<?php

namespace InterInvest\QrCodePdfBundle\Command;

use James2001\Zxing;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentQrcodeReaderCommand extends ContainerAwareCommand
{

    protected $dirName;
    protected $fileDir;
    protected $x;

    public function setDirName($dirName)
    {
        $this->dirName = $dirName;
    }
    public function setFileDir($fileDir){
        $this->fileDir = $fileDir;
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
        $dir = $this->getContainer()->getParameter('inputdir');
        $destDir = new \DirectoryIterator($dir);
        foreach ($destDir as $file) {
            if (!is_dir($file) && strpos($file->getBasename(), ".pdf")) {
                $fileDir = str_replace(".pdf","", $file->getBasename());
                $this->setDirName(substr($file->getBasename(), 0, 3));
                $this->setFileDir($fileDir);
                $file = $dir . $file;
                $im = new \Imagick();
                dump($file);
                $im->readImage($file);
                $this->x = 0;
                $nombrePage = $im->getNumberImages();
                $range = range(0, $nombrePage - 1);
                $pdfQrCode = [];

                while ($this->x <= 200 && !empty($range)) {
                    dump("Résolution : " . $this->x);
                    foreach ($range as $i) {
                        $nomFichier = "image" . $i . ".jpg";
                        $image = $file . '[' . $i . ']';
                        $imagick = $this->imagick($image, $nomFichier);
                        #exec('convert -density 150 -threshold 55%' .$file.'+adjoin' .'pdf.jpg');
                        $qrcode = $this->qrCode($imagick);

                        if ($qrcode) {
                            dump($qrcode);
                            $pdfQrCode[$qrcode][$i] = $nomFichier;
                            unset($range[$i]);
                        } elseif ($this->x == 200) {
                            $pdfQrCode['inconnu'][$i] = $nomFichier;
                            unset($range[$i]);
                        }
                    }
                    if ($this->x == 0){
                        $this->x = 70;
                    }
                        $this->x += 5;
                }

                $this->generePdfs($pdfQrCode, $dir);


            }
        }
        $output->writeln('Command result.');
    }


    public function imagick($image, $nomFichier)
    {
        $imagick = new \Imagick();
       if ($this->x == 0){
            $imagick->readImage($image);
            #$imagick->thresholdImage(55* \Imagick::getQuantum());
            #$imagick->scaleImage(2000,1500);
            $imagick->writeImage($nomFichier);
            return $imagick;
        }
        $imagick->setResolution($this->x,$this->x);
        $imagick->readImage($image);
        $avg = max($imagick->getQuantumRange());
        $imagick->thresholdImage(0.55*$avg);
        #$imagick->scaleImage(2000,1500);
        $imagick->writeImage($nomFichier);
        return $imagick;
    }

    public function qrCode($im)
    {
        try {
            $qrCode = new \QrReader($im, \QrReader::SOURCE_TYPE_RESOURCE);
        } catch (\Exception $e) {
            echo "non reconnu";
            return false;
        }
        return $qrCode->text();
    }

    public function generePdfs($pdfQrcode, $dir)
    {

        foreach ($pdfQrcode as $qrcode => $page) {
            $this->pdf($dir, $page, $qrcode);

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
        if(!is_dir($dir . $this->dirName)){
            mkdir($dir . $this->dirName);
        }
        if (!is_dir($dir. $this->dirName . '/'. $this->fileDir)){
            mkdir($dir. $this->dirName . '/'. $this->fileDir);
        }
        $pdf->Output($dir . $this->dirName. '/'. $this->fileDir .'/' . $qrcode . '.pdf', "F");
    }

}
