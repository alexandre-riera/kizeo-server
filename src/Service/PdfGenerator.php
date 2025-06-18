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
        $options->set('isRemoteEnabled', true);
        $options->set('isPHPEnabled', true);
        
        // Désactiver les marges par défaut
        $options->set('defaultMediaType', 'print');
        $options->set('isFontSubsettingEnabled', true);
        
        // Initialisation de Dompdf
        $dompdf = new Dompdf($options);
        
        // Modification du HTML pour supprimer toutes les marges
        $htmlWithoutMargins = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page {
                    margin: 0mm;
                    padding: 0mm;
                }
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                html, body {
                    margin: 0 !important;
                    padding-top: 60px !important;
                    padding-left: 10px !important;
                    width: 100%;
                    height: 90%;
                }
            </style>
        </head>
        <body>' . $html . '</body>
        </html>';
        
        $dompdf->loadHtml($htmlWithoutMargins);
        
        // Configuration du format et orientation avec marges à zéro
        $dompdf->setPaper('A4', 'portrait');
        
        // Rendu du PDF
        $dompdf->render();
        
        // Parameters pour la numérotation des pages (ajustés pour pas de marge)
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
        
        // Positionnement ajusté pour absence de marges
        $canvas->page_text(
            520, // x - position ajustée
            820, // y - position ajustée 
            "Page {PAGE_NUM} sur {PAGE_COUNT}",
            $font,
            10,
            [0, 0, 0], // couleur noir
            0.0,
            0.0,
            0.0
        );

        return $dompdf->output();
    }
}