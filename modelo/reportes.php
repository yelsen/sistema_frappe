<?php
require('fpdf.php');
require 'vendor/autoload.php'; 
 
class PDF extends FPDF
{
// Cabecera de página
function Header()
{
    // Logo
    $this->Image('logo.png',10,8,33);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Movernos a la derecha
    $this->Cell(80);
    // Título
    $this->Cell(30,10,'Title',1,0,'C');
    // Salto de línea
    $this->Ln(20);
}
 
// Pie de página
function Footer()
{
    // Posición: a 1,5 cm del final
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Número de página
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}
 
require ('conexion.php');
$consulta = "select c.fecha_ingresoC,c.num_comprobanteC,c.monto_ingresoC from compras c";
$resultado = $mysqli->query($consulta);
 
// Creación del objeto de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
 
while($row=$resultado->fetch_assoc()){
    $pdf->Cell(0,10,'Fecha de ingreso: '.$row['fecha_ingresoC'],0,1);
    $pdf->Cell(0,10,'Numero de comprobante: '.$row['num_comprobanteC'],0,1);
    $pdf->Cell(0,10,'Monto de ingreso: '.$row['monto_ingresoC'],0,1);
}
 
   
    $pdf->Output();
?>