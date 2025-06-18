<?php
// src/Service/PdfGenerator.php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public function generatePdf($html, $filename = 'document.pdf')
    {
        // Configuration des options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Pour permettre le chargement d'images externes
        $options->set('isPHPEnabled', true);
        $options->set('marginTop', 0);
        $options->set('marginBottom', 0);
        $options->set('marginLeft', 0);
        $options->set('marginRight', 0);

        // Initialisation de Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        
        // Configuration du format et orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Rendu du PDF
        $dompdf->render();
        
        // Parameters
        $x          = 505;
        $y          = 790;
        $text       = "{PAGE_NUM} of {PAGE_COUNT}";     
        $font       = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');   
        $size       = 10;    
        $color      = array(0,0,0);
        $word_space = 0.0;
        $char_space = 0.0;
        $angle      = 0.0;

        $dompdf->getCanvas()->page_text(
        $x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle
        );

        return $dompdf->output();
    }
}
