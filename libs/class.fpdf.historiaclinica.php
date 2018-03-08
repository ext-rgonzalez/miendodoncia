<?php
require('fpdf17/fpdf.php');
class PDF extends FPDF{
    public $logo_header;
    public $logo_header_derecha;
    public $titulo;
    public $linea_1;
    public $linea_2;
    public $linea_3;
    public $linea_4;
    public $linea_area_gestion;
    public $linea_1_footer;
    public $log_footer;
    
    public function Header(){
        //Logo de la institucion
        $this->Cell(60, 23, $this->Image($this->logo_header,15,12,70), 0, 0, 'C');    
        //Encabezado de la pagina
        $this->SetFont('Arial','',11);
        $this->Cell(130,5, utf8_decode($this->titulo), 0, 1,'R');  
        $this->Ln(2);
        $this->SetFont('Arial','',6);
        $this->Cell(60, 18,'');
        $this->MultiCell(130,3, utf8_decode($this->linea_1), '', "R", false);  
        $this->Cell(60, 18,'');
        $this->MultiCell(130,3, utf8_decode($this->linea_2), '', "R", false);  
        $this->Cell(60, 18,'');
        $this->MultiCell(130,3, utf8_decode($this->linea_3), '', "R", false);  
        $this->Cell(60, 18,'');
        $this->MultiCell(130,3, utf8_decode($this->linea_4), '', "R", false); 
        $this->Cell(60, 18);
        $this->SetFont('Arial','',8);
        $this->MultiCell(120,4,'', 0, "R", false); 
        $this->Ln(10);
    }

    public function Footer(){
        $this->SetY(-15);
        $this->SetFont('Arial','I',7);
        $this->Cell(15,5);
        $this->Cell(165,10,utf8_decode($this->linea_1_footer),'T',0,'C');
       //$this->Cell(40, 10, $this->Image('modules/sined/adjuntos/eua.png',160,$this->GetY(),30),'T', 0, 'C');
    }
}
?>