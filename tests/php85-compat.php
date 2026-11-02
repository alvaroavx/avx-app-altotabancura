<?php

if (PHP_VERSION_ID < 80500 || PHP_VERSION_ID >= 80600) {
    fwrite(STDERR, 'Se requiere PHP 8.5.x; detectado: ' . PHP_VERSION . PHP_EOL);
    exit(1);
}

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$root = getcwd();
require $root . '/valk/tools/tEncode.php';
require $root . '/vendor/FPDF/doorlock.php';
require $root . '/valk/mu/cXLSXWriter.php';

$sample = 'Árbol, pingüino y ñandú';
if (Latin1ToUtf8(Utf8ToLatin1($sample)) !== $sample) {
    throw new RuntimeException('La conversión ISO-8859-1/UTF-8 cambió el contenido.');
}

$pdfPath = tempnam(sys_get_temp_dir(), 'alto-php85-pdf-');
$xlsxPath = tempnam(sys_get_temp_dir(), 'alto-php85-xlsx-');

try {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 5, null);
    $pdf->Ln();
    $pdf->Cell(0, 10, Utf8ToLatin1('Certificado de vacunación - Álvaro Núñez'));
    $pdf->Output('F', $pdfPath);

    if (substr((string)file_get_contents($pdfPath), 0, 5) !== '%PDF-') {
        throw new RuntimeException('FPDF no generó un documento PDF válido.');
    }

    $xlsx = new XLSXWriter();
    $xlsx->setTempDir(sys_get_temp_dir());
    $xlsx->writeSheet(
        array(
            array('Nombre', 'Resultado'),
            array('Álvaro Núñez', '=1+1'),
        ),
        'Compatibilidad'
    );
    $xlsx->writeToFile($xlsxPath);

    if (substr((string)file_get_contents($xlsxPath), 0, 2) !== 'PK') {
        throw new RuntimeException('XLSXWriter no generó un archivo XLSX válido.');
    }
} finally {
    @unlink($pdfPath);
    @unlink($xlsxPath);
}

restore_error_handler();
echo 'Compatibilidad PHP ' . PHP_VERSION . ': OK' . PHP_EOL;
