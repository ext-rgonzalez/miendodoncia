<?php
require('fpdf17/fpdf.php');
class PDF extends FPDF{
    public function Header(){
        //Logo de la institucion
        $this->Cell(15, 10,'');
        $this->Cell(25, 22, $this->Image('modules/sined/adjuntos/logo.png',33,16,10), 1, 0, 'C');
        // Encabezado de la calificacion
        $this->SetFont('Arial','',11);
        $this->Cell(120,5,'INSTITUCION EDUCATIVA ATANASIO GIRARDOT','T',0,'C',false);
        $this->Cell(25, 22,$this->Image('modules/sined/adjuntos/certificacion.png',172,16,21), 1, 0, 'C');
        $this->Ln(6);
        $this->SetFont('Arial','',6);
        $this->Cell(30, 18,'');
        $this->MultiCell(130,3, utf8_decode("Aprobación Estudios Secretaría de Educación Resolución 569/2006 y Resolución 1611/2009"), '', "C", false);
        $this->Cell(30, 18,'');
        $this->MultiCell(130,3, utf8_decode("Preescolar- Educación Básica Primaria-Secundaria y Media Vocacional"), '', "C", false);
        $this->Cell(30, 18,'');
        $this->MultiCell(130,3, utf8_decode("Ciclos Lectivos Especiales Integrados (Educación para adultos y jóvenes en extraedad Decreto MEN 3011 de 1997)"), '', "C", false);
        $this->Cell(30, 18,'');
        $this->MultiCell(130,3, utf8_decode("DANE 117001000653- NIT 810001464-7"), '', "C", false);
        $this->Cell(40, 18);
        $this->SetFont('Arial','',8);
        $this->MultiCell(120,4, utf8_decode("GESTION ACADEMICA"), 1, "C", false);
        $this->Ln(10);
    }

    public function Footer(){
        $this->SetY(-15);
        $this->SetFont('Arial','I',7);
        $this->Cell(15,5);
        $this->Cell(130,10,utf8_decode('Calle 67 No. 30C-33 Barrio Fátima - Teléfax 8875772 - colatanasio@hotmail.com - Manizales'),'T',0,'C');
        $this->Cell(40, 10, $this->Image('modules/sined/adjuntos/eua.png',160,$this->GetY(),30),'T', 0, 'C');
    }
}
?>