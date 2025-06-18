<?php
// src/Service/PdfGenerator.php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public function generatePdf($html, $filename = 'document.pdf')
    {
        // Configuration des options optimisées
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPHPEnabled', false); // Désactiver PHP pour la sécurité et performance
        
        // Optimisations pour éviter les timeouts
        $options->set('defaultMediaType', 'print');
        $options->set('isFontSubsettingEnabled', false); // Désactiver pour améliorer les performances
        $options->set('isPhpEnabled', false);
        $options->set('debugKeepTemp', false);
        $options->set('debugCss', false);
        $options->set('debugLayout', false);
        $options->set('debugLayoutLines', false);
        $options->set('debugLayoutBlocks', false);
        $options->set('debugLayoutInline', false);
        $options->set('debugLayoutPaddingBox', false);
        
        // Augmenter les limites de mémoire et temps
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes
        
        // Initialisation de Dompdf
        $dompdf = new Dompdf($options);
        
        // HTML simplifié et optimisé
        $optimizedHtml = $this->optimizeHtmlForPdf($html);
        
        $dompdf->loadHtml($optimizedHtml);
        
        // Configuration du format
        $dompdf->setPaper('A4', 'portrait');
        
        // Rendu du PDF
        $dompdf->render();
        
        // Numérotation des pages
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        
        $canvas->page_text(
            520,
            820,
            "Page {PAGE_NUM} sur {PAGE_COUNT}",
            $font,
            10,
            [0, 0, 0]
        );

        return $dompdf->output();
    }
    
    private function optimizeHtmlForPdf($html)
    {
        // Supprimer les éléments problématiques
        $html = preg_replace('/<div class="background-image">.*?<\/div>/s', '', $html);
        
        // Optimiser les images base64 trop lourdes
        $html = preg_replace_callback('/src="data:image\/jpeg;base64,([^"]+)"/', function($matches) {
            $base64 = $matches[1];
            // Limiter la taille des images pour éviter les timeouts
            if (strlen($base64) > 100000) { // Si l'image est trop lourde
                return 'src="data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="150"><rect width="200" height="150" fill="#f5f5f5"/><text x="100" y="75" text-anchor="middle" fill="#666">Image trop lourde</text></svg>') . '"';
            }
            return $matches[0];
        }, $html);
        
        // CSS optimisé pour PDF
        $optimizedCss = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page {
                    margin: 10mm;
                    padding: 0;
                }
                * {
                    box-sizing: border-box;
                }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 11px;
                    line-height: 1.4;
                    margin: 0;
                    padding: 20px;
                }
                .page-break {
                    page-break-after: always;
                }
                .equipement {
                    margin-bottom: 15px;
                    border: 1px solid #ddd;
                    padding: 8px;
                    break-inside: avoid;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 4px;
                    text-align: left;
                    vertical-align: top;
                }
                th {
                    background-color: #f9f9f9;
                    font-weight: bold;
                    width: 25%;
                }
                .main-photo {
                    max-width: 150px;
                    max-height: 100px;
                    object-fit: contain;
                }
                .stats-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                    font-size: 10px;
                }
                .stats-table th {
                    background-color: #ffff00;
                    color: black;
                    font-weight: bold;
                    text-align: center;
                    padding: 6px;
                }
                .stats-table td {
                    text-align: center;
                    padding: 4px;
                }
                .stats-section {
                    background-color: #f8f9fa;
                    border: 1px solid #dee2e6;
                    padding: 10px;
                    margin: 15px 0;
                }
                .header h1, .header h2 {
                    margin: 5px 0;
                }
                .summary {
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>';
        
        return $optimizedCss . $html . '</body></html>';
    }
}